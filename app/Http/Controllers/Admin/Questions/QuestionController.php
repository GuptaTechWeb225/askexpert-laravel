<?php

namespace App\Http\Controllers\Admin\Questions;


use App\Http\Controllers\Controller;
use App\Contracts\Repositories\ExpertRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\WebConfigKey;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use App\Models\ExpertCategory;
use App\Models\ChatSession;
use App\Enums\ViewPaths\Admin\Expert;

class QuestionController extends Controller
{

    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly ExpertRepositoryInterface        $expertRepo,
        private readonly CustomerRepositoryInterface        $customerRepo,

    ) {}
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->questions($request);
    }

    public function questions(Request $request): View|RedirectResponse
    {
        $query = ChatSession::query()
            ->with(['customer', 'expert', 'category', 'firstMessage', 'expert.category']);

        // Search filter
        if ($request->filled('searchValue')) {
            $search = $request->get('searchValue');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
                ->orWhereHas('expert', function ($q) use ($search) {
                    $q->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('firstMessage', function ($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%");
                });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Status filter: Active (ongoing), Ended (ended_at filled), Waiting (no expert assigned yet)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('ended_at')->whereNotNull('expert_id');
            } elseif ($request->status === 'ended') {
                $query->whereNotNull('ended_at');
            } elseif ($request->status === 'waiting') {
                $query->whereNull('expert_id')->whereNull('ended_at');
            }
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                $endDate   = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('started_at', [$startDate, $endDate]);
            }
        }

        $questions = $query->latest('started_at')->paginate(25)->appends($request->query());

        $categories = ExpertCategory::where('is_active', 1)->get();

        return view('admin-views.questions.index', compact('questions', 'categories'));
    }

    public function detail($id)
    {
        $session = ChatSession::with(['customer', 'expert', 'category', 'firstMessage'])
            ->findOrFail($id);
        $totalMessages = $session->messages()->count();

        return view('admin-views.questions.partials.detail-view', compact('session', 'totalMessages'));
    }
    public function assignExpert(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:chat_sessions,id',
            'expert_id' => 'required|exists:experts,id'
        ]);

        $session = ChatSession::find($request->session_id);

        if ($session->expert_id) {
            return response()->json(['success' => false, 'message' => 'Already assigned to an expert']);
        }

        $session->expert_id = $request->expert_id;
        $session->status = 'active';
        $session->save();

        return response()->json(['success' => true, 'message' => 'Expert assigned successfully']);
    }

    public function assignCategory(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:chat_sessions,id',
            'category_id' => 'required|exists:expert_categories,id'
        ]);

        $session = ChatSession::find($request->session_id);
        $session->category_id = $request->category_id;
        $session->save();

        return response()->json(['success' => true, 'message' => 'Category updated successfully']);
    }
    public function missCategorieQuo(Request $request): View|RedirectResponse
    {
        $query = ChatSession::query()
            ->with(['customer', 'expert', 'category', 'firstMessage', 'expert.category']);

        $query->where('expert_id', null);

        if ($request->filled('searchValue')) {
            $search = $request->get('searchValue');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
                ->orWhereHas('expert', function ($q) use ($search) {
                    $q->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('firstMessage', function ($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%");
                });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Status filter: Active (ongoing), Ended (ended_at filled), Waiting (no expert assigned yet)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('ended_at')->whereNotNull('expert_id');
            } elseif ($request->status === 'ended') {
                $query->whereNotNull('ended_at');
            } elseif ($request->status === 'waiting') {
                $query->whereNull('expert_id')->whereNull('ended_at');
            }
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                $endDate   = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('started_at', [$startDate, $endDate]);
            }
        }

        $questions = $query->latest('started_at')->paginate(10)->appends($request->query());

        $categories = ExpertCategory::where('is_active', 1)->get();

        return view('admin-views.questions.miscategories', compact('questions', 'categories'));
    }
}
