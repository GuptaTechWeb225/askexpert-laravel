<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\Pages;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\AboutUsRequest;
use App\Http\Requests\Admin\PageUpdateRequest;
use App\Http\Requests\Admin\PrivacyPolicyRequest;
use App\Http\Requests\Admin\TermsConditionRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Traits\SettingsTrait;

class PagesController extends BaseController
{
    use SettingsTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ){}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getTermsConditionView();
    }

    public function getTermsConditionView(): View
    {
        $terms_condition = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'terms_condition']);
        return view(Pages::TERMS_CONDITION[VIEW], compact('terms_condition'));
    }

    public function updateTermsCondition(TermsConditionRequest $request): RedirectResponse
    {
        $this->businessSettingRepo->updateWhere(params: ['type'=>'terms_condition'], data: ['value' => $request['value']]);
        clearWebConfigCacheKeys();
        Toastr::success(translate('Terms_and_Condition_Updated_successfully'));
        return back();
    }

    public function getPrivacyPolicyView(): View
    {
        $privacy_policy = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'privacy_policy']);
        return view(Pages::PRIVACY_POLICY[VIEW], compact('privacy_policy'));
    }

    public function updatePrivacyPolicy(PrivacyPolicyRequest $request): RedirectResponse
    {
        $this->businessSettingRepo->updateWhere(params: ['type'=>'privacy_policy'], data: ['value' => $request['value']]);
        Toastr::success(translate('Privacy_policy_Updated_successfully'));
        return back();
    }


    public function getPageView($page): View|RedirectResponse
    {
        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];
        if (in_array($page, $pages)) {
            $data = $this->businessSettingRepo->getFirstWhere(params: ['type' => $page]);
            return view(Pages::VIEW[VIEW], compact('page', 'data'));
        }
        Toastr::error(translate('invalid_page'));
        return back();
    }

    public function updatePage(PageUpdateRequest $request, $page): RedirectResponse
    {
        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];
        if (in_array($page, $pages)) {
            $value = json_encode(['status' => $request->get('status', 0), 'content' => $request['value']]);
            $this->businessSettingRepo->updateOrInsert(type: $page, value: $value);
            Toastr::success(translate('updated_successfully'));
        } else {
            Toastr::error(translate('invalid_page'));
        }
        return back();
    }

    public function getAboutUsView(): View
    {
        $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'about_us']);
        return view(Pages::ABOUT_US[VIEW], compact('pageData'));
    }
   public function getDispatchView(): View
{
    $settings = $this->businessSettingRepo->getListWhere(dataLimit: 'all');

    $dispatchSettings = [
        'dispatch_mode'           => $this->getSettings($settings, 'dispatch_mode')->value ?? 'auto',
        'ai_assist'               => (bool)($this->getSettings($settings, 'ai_assist')->value ?? 1),
        'fallback_manual'         => (bool)($this->getSettings($settings, 'fallback_manual')->value ?? 1),
        'match_category'          => (bool)($this->getSettings($settings, 'match_category')->value ?? 1),
        'match_language'          => (bool)($this->getSettings($settings, 'match_language')->value ?? 1),
        'prioritize_ratings'      => (bool)($this->getSettings($settings, 'prioritize_ratings')->value ?? 1),
        'avoid_pending_payouts'   => (bool)($this->getSettings($settings, 'avoid_pending_payouts')->value ?? 1),
        'admin_notification'      => (bool)($this->getSettings($settings, 'admin_notification')->value ?? 1),
        'max_pending_assignments' => $this->getSettings($settings, 'max_pending_assignments')->value ?? 5,
        'fallback_manual_time' => $this->getSettings($settings, 'fallback_manual_time')->value ?? 5,
    ];

    return view(Pages::DISPATCH[VIEW], [
        'dispatchSettings' => $dispatchSettings,
    ]);
}

public function updateDispatch(Request $request): RedirectResponse
{
    $request->validate([
        'dispatch_mode' => 'required|in:auto,manual',
        'ai_assist' => 'nullable|in:1',
        'fallback_manual' => 'nullable|in:1',
        'match_category' => 'nullable|in:1',
        'match_language' => 'nullable|in:1',
        'prioritize_ratings' => 'nullable|in:1',
        'avoid_pending_payouts' => 'nullable|in:1',
        'admin_notification' => 'nullable|in:1',
        'max_pending_assignments' => 'required|integer|min:1|max:999',
        'fallback_manual_time' => 'nullable|integer|min:1|max:999',
    ]);

    $this->businessSettingRepo->updateOrInsert('dispatch_mode', $request->dispatch_mode);
    $this->businessSettingRepo->updateOrInsert('ai_assist', $request->has('ai_assist') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('fallback_manual', $request->has('fallback_manual') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('match_category', $request->has('match_category') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('match_language', $request->has('match_language') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('prioritize_ratings', $request->has('prioritize_ratings') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('avoid_pending_payouts', $request->has('avoid_pending_payouts') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('admin_notification', $request->has('admin_notification') ? 1 : 0);
    $this->businessSettingRepo->updateOrInsert('max_pending_assignments', $request->max_pending_assignments);
    $this->businessSettingRepo->updateOrInsert('fallback_manual_time', $request->fallback_manual_time);

    Toastr::success(translate('Dispatch settings updated successfully!'));
    return back();
}

    public function updateAboutUs(AboutUsRequest $request): RedirectResponse
    {
        $this->businessSettingRepo->updateWhere(params: ['type'=>'about_us'], data: ['value' => $request['about_us']]);
        Toastr::success(translate('about_us_updated_successfully'));
        return back();
    }


}
