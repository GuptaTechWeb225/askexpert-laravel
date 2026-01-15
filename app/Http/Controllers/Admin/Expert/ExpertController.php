<?php

namespace App\Http\Controllers\Admin\Expert;


use App\Http\Controllers\Controller;
use App\Contracts\Repositories\ExpertRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\WebConfigKey;
use Illuminate\Http\Request;
use App\Enums\ViewPaths\Admin\Restaurant;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\RestaurantService;
use Illuminate\Support\Facades\DB; // <-- yeh line add karo
use App\Models\RestaurantBillPayment;
use App\Models\ExpertCategory;
use App\Exports\RestaurantListExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Events\CustomerStatusUpdateEvent;
use App\Enums\ViewPaths\Admin\Expert;
use App\Models\ExpertEarning;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpertApprovedMail;
use App\Mail\ExpertRejectedMail;


class ExpertController extends Controller
{

    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly ExpertRepositoryInterface        $expertRepo,
        private readonly CustomerRepositoryInterface        $customerRepo,

    ) {}
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->experts($request);
    }

    public function experts(Request $request): View|RedirectResponse
    {
        $filters = [
            'is_active' => $request['is_active'] ?? null,
            'sort_by' => $request['sort_by'] ?? null,
            'status' => 'approved', // only pending
            'category' => $request['category'] ?? null,
        ];

        $joiningStartDate = '';
        $joiningEndDate = '';
        if ($request['expert_joining_date'] ?? false) {
            $dates = explode(' - ', $request['expert_joining_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $joiningStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $joiningEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $experts = $this->expertRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            relations: ['category'],
            filters: $filters,
            whereBetween: 'created_at',
            whereBetweenFilters: $joiningStartDate && $joiningEndDate ? [$joiningStartDate, $joiningEndDate] : [],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all()
        );

        $totalExperts = $this->expertRepo->getListWhereBetween(
            filters: ['status' => 'approved', 'avoid_walking_customer' => 1],
            dataLimit: 'all'
        )->count();
        $onlineExperts = $this->expertRepo->getListWhereBetween(
            filters: ['is_online' => true, 'avoid_walking_customer' => 1],
            dataLimit: 'all'
        )->count();
        $pendingExperts = $this->expertRepo->getListWhereBetween(
            filters: ['status' => 'pending', 'avoid_walking_customer' => 1],
            dataLimit: 'all'
        )->count();
        $blockExperts = $this->expertRepo->getListWhereBetween(
            filters: ['is_active' => false, 'avoid_walking_customer' => 1],
            dataLimit: 'all'
        )->count();

        $categories = ExpertCategory::all();

        return view(Expert::EXPERTS[VIEW], compact('experts', 'totalExperts', 'categories', 'onlineExperts', 'pendingExperts', 'blockExperts'));
    }


    public function expertStatus(Request $request): JsonResponse
    {
        $this->expertRepo->update(id: $request['id'], data: ['is_active' => $request->get('is_active', 0)]);
        $this->expertRepo->deleteAuthAccessTokens(id: $request['id']);
        $expert = $this->expertRepo->getFirstWhere(params: ['id' => $request['id']]);
        $data = [
            'userName' => $expert['f_name'],
            'userType' => 'customer',
            'templateName' => $expert['is_active'] ? 'account-unblock' : 'account-block',
            'subject' => $expert['is_active'] ? translate('Account_Unblocked') . ' !' : translate('Account_Blocked') . ' !',
            'title' => $expert['is_active'] ? translate('Account_Unblocked') . ' !' : translate('Account_Blocked') . ' !',
        ];
        event(new CustomerStatusUpdateEvent(email: $expert['email'], data: $data));
        return response()->json(['message' => translate('update_successfully')]);
    }


    public function expertView(Request $request, $id): View|RedirectResponse
    {
        $expert = $this->expertRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['reviews.user', 'category'] // Relations load karein
        );
        return view(Expert::EXPERT_VIEW[VIEW], compact('expert'));
        Toastr::error(translate('Expert_Not_Found'));

        return back();
    }


    public function expertRequest(Request $request): View|RedirectResponse
    {
        $filters = [
            'sort_by' => $request['sort_by'] ?? null,
            'status' => 'pending', // only pending
            'category' => $request['category'] ?? null,
        ];

        $joiningStartDate = '';
        $joiningEndDate = '';
        if ($request['expert_joining_date'] ?? false) {
            $dates = explode(' - ', $request['expert_joining_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                Toastr::error(translate('Invalid_date_range_format'));
                return back();
            }
            $joiningStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $joiningEndDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $experts = $this->expertRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            relations: ['category'],
            filters: $filters,
            whereBetween: 'created_at',
            whereBetweenFilters: $joiningStartDate && $joiningEndDate ? [$joiningStartDate, $joiningEndDate] : [],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all()
        );

        $totalExperts = $this->expertRepo->getListWhereBetween(
            filters: ['status' => 'pending', 'avoid_walking_customer' => 1],
            dataLimit: 'all'
        )->count();

        $categories = ExpertCategory::all();

        return view(Expert::EXPERT_REQUEST[VIEW], compact('experts', 'totalExperts', 'categories'));
    }

    public function requestApprove(request $request)
    {

        $id = $request->id;
        $expert = $this->expertRepo->getFirstWhere(params: ['id' => $id]);

        if (!$expert) {
            Toastr::error(translate('Expert_Not_Found'));
            return redirect()->back();
        }

        $expert->status = 'approved';
        $expert->is_active = true;
        $expert->save();

        $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);

        // -----------------------------
        // 1️⃣ Admin Notification
        // -----------------------------
        $notificationRepo->notifyRecipients(
            $expert->id,
            \App\Models\Expert::class,
            "Expert Approved",
            "Expert {$expert->f_name} {$expert->l_name} has been approved successfully.",
            [
                ['type' => 'admin', 'id' => 1]
            ]
        );

        // -----------------------------
        // 2️⃣ Expert Notification
        // -----------------------------
        $notificationRepo->notifyRecipients(
            $expert->id,
            \App\Models\Expert::class,
            "Application Approved",
            "Congratulations! Your expert application has been approved. You can now start accepting questions.",
            [
                ['type' => 'expert', 'id' => $expert->id]
            ]
        );

        try {
            Mail::to($expert->email)->send(new ExpertApprovedMail($expert));
        } catch (\Exception $e) {
        }
        Toastr::success(translate('Expert_application_approved_successfully'));

        return redirect()->back();
    }

    public function requestReject(Request $request)
    {
        $id = $request->id;
        $expert = $this->expertRepo->getFirstWhere(params: ['id' => $id]);
        if (!$expert) {
            Toastr::error(translate('Expert_Not_Found'));
            return redirect()->back();
        }

        $request->validate([
            'reject_reason' => 'required|string|max:1000',
        ]);

        $expert->status = 'rejected';
        $expert->reject_reason = $request->reject_reason; // <-- fixed
        $expert->save();

        try {
            Mail::to($expert->email)->send(
                new ExpertRejectedMail($expert, $request->reject_reason)
            );
        } catch (\Exception $e) {
        }
        Toastr::success(translate('Expert_application_rejected_successfully'));
        return redirect()->back();
    }
}
