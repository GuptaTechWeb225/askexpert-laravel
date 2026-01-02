<?php

namespace App\Repositories;

use App\Contracts\Repositories\BoostPlanRepositoryInterface;
use App\Models\BoostPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BoostPlanRepository implements BoostPlanRepositoryInterface
{
    public function __construct(
        private readonly BoostPlan $plan,
    ) {}

    public function add(array $data): string|object
    {
        return $this->plan->create($data);
    }
    public function find($id)
    {
        return $this->plan->findOrFail($id);
    }
    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->plan->with($relations)->where($params)->first();
    }

    public function getByIdentity(array $filters = []): ?Model
    {
        return $this->plan
            ->when(isset($filters['phone']) && $filters['phone'], function ($query) use ($filters) {
                return $query->where(['phone' => $filters['phone']]);
            })
            ->when(isset($filters['email']) && $filters['email'], function ($query) use ($filters) {
                return $query->where(['email' => $filters['email']]);
            })
            ->when(isset($filters['identity']) && $filters['identity'], function ($query) use ($filters) {
                return $query->orWhere(function ($query) use ($filters) {
                    return $query->whereNotNull('email')->where(['email' => $filters['identity']]);
                });
            })
            ->when(isset($filters['identity']) && $filters['identity'], function ($query) use ($filters) {
                return $query->orWhere(function ($query) use ($filters) {
                    return $query->whereNotNull('phone')->where(['phone' => $filters['identity']]);
                });
            })->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->plan->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(
        array $orderBy = [],
        string $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = DEFAULT_DATA_LIMIT,
        int $offset = null,
        array $select = ['*'], // ✅ new
        array $groupBy = []    // ✅ new
    ): Collection|LengthAwarePaginator {
        $query = $this->plan->select($select)->with($relations)
            ->when(empty($filters['withCount']), function ($query) use ($filters) {
                return $query->where($filters);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->orWhere('f_name', 'like', "%$searchValue%")
                    ->orWhere('l_name', 'like', "%$searchValue%")
                    ->orWhere('phone', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%");
            })
            ->when(isset($filters['withCount']), function ($query) use ($filters) {
                return $query->withCount($filters['withCount']);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            })
            ->when(!empty($groupBy), function ($query) use ($groupBy) { // ✅ new
                $query->groupBy($groupBy);
            });

        return $dataLimit == 'all'
            ? $query->get()
            : $query->paginate($dataLimit)->appends(['searchValue' => $searchValue]);
    }


    public function getListWhereBetween(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], string $whereBetween = null, array $whereBetweenFilters = [], int|string $takeItem = null, int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null, array|object $appends = []): Collection|LengthAwarePaginator
    {
        $query = $this->plan
            ->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->orWhere('email', 'like', "%$searchValue%")
                        ->orWhere('restaurant_name', 'like', "%$searchValue%")
                        ->orWhere('city', 'like', "%$searchValue%");
                })->where('email', '!=', 'abc@gm.com');
            })

            ->when(isset($filters['is_active']) && in_array($filters['is_active'], ['0', '1']), function ($query) use ($filters) {
                return $query->where('is_active', $filters['is_active']);
            })
            ->when(isset($filters['restaurant_plan']) && in_array($filters['restaurant_plan'], ['free', 'paid']), function ($query) use ($filters) {
                return $query->where('plan_name', $filters['restaurant_plan']);
            })
            ->when(isset($filters['status']) && !empty($filters['status']), function ($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(!empty($whereBetween) && !empty($whereBetweenFilters), function ($query) use ($whereBetween, $whereBetweenFilters) {
                $query->whereBetween($whereBetween, $whereBetweenFilters);
            })
            ->when(isset($filters['sort_by']) && in_array($filters['sort_by'], ['asc', 'desc']), function ($query) use ($filters) {
                return $query->orderBy('created_at', $filters['sort_by']);
            })

            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        if (!empty($takeItem) && $dataLimit == 'all') {
            return $query->get()->slice(0, $takeItem)->values();
        } else if (!empty($takeItem) && $dataLimit != 'all') {
            $allResults = $query->get();
            $allResults = $allResults->slice(0, $takeItem);
            $page = request('page') ?? 1;
            $perPage = $dataLimit;
            $paginator = new LengthAwarePaginator(
                items: $allResults->forPage($page, $perPage)->values(),
                total: $allResults->count(),
                perPage: $perPage,
                currentPage: $page,
                options: ['path' => request()->url(), 'query' => request()->query()]
            );
            return $paginator->appends($appends);
        }
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($appends);
    }

    public function getListWhereNotIn(array $ids = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->plan->whereNotIn('id', $ids)->get();
    }

    public function update(string $id, array $data): bool
    {
        return $this->plan->find($id)->update($data);
    }

    public function updateWhere(array $params, array $data): bool
    {
        $this->plan->where($params)->update($data);
        return true;
    }

    public function updateOrCreate(array $params, array $data): mixed
    {
        return $this->plan->updateOrCreate($params, $data);
    }

    public function delete(array $params): bool
    {
        $this->plan->where($params)->delete();
        return true;
    }


    public function deleteAuthAccessTokens(string|int $id): bool
    {
        DB::table('oauth_access_tokens')->where('user_id', $id)->delete();
        return true;
    }
}
