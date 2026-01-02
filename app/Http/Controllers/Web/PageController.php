<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\ExpertCategory;
use App\Models\Expert;
use App\Models\CareerCard;
use App\Models\CareerJob;
use App\Models\CareerSection;
use App\Models\CareerBenefits;
use App\Traits\HomeCmsTrait;
use App\Http\Controllers\Admin\Cms\HelpController as AdminHelpController;
use App\Models\ChatSession;
use Illuminate\Http\Request;

class PageController extends Controller
{

    use HomeCmsTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface   $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface         $helpTopicRepo,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
    ) {}

    public function getAboutUsView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'about-us']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $data = [
            'experts' => $this->getSectionItems('experts'),
        ];
        return view(VIEW_FILE_NAMES['about_us'],   compact(
            'data',
        ));
    }
    public function getUserHomeView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'user-home']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $experts = Expert::where('status', 'approved')
            ->inRandomOrder()
            ->take(10)
            ->get();
        return view(VIEW_FILE_NAMES['user_home'], compact('experts'));
    }
    public function getUserQuestionsView(Request $request): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo
            ->getFirstWhere(params: ['page_name' => 'user-home'])
            ?? $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);

        $query = ChatSession::with([
            'category:id,name',
            'expert:id,f_name,l_name',
            'firstMessage:id,chat_session_id,message'
        ])
            ->where('user_id', auth('customer')->id());

        /* ======================
       CATEGORY FILTER
    ====================== */
        if ($request->filled('q_category_id')) {
            $query->where('category_id', $request->q_category_id);
        }

        /* ======================
       STATUS FILTER
    ====================== */
        if ($request->filled('q_status')) {
            $query->where('status', $request->q_status);
        }

        /* ======================
       SEARCH FILTER (in first message)
    ====================== */
        if ($request->filled('q_search')) {
            $searchTerm = $request->q_search;
            $query->whereHas('firstMessage', function ($q) use ($searchTerm) {
                $q->where('message', 'like', "%{$searchTerm}%");
            });
        }

        $questions = $query
            ->latest('started_at')
            ->paginate(10)
            ->withQueryString();

        $categories = ExpertCategory::active()
            ->orderBy('name')
            ->get();

        return view(VIEW_FILE_NAMES['user_questions'], compact(
            'questions',
            'categories',
            'robotsMetaContentData'
        ));
    }
    public function getUserExpertsView(Request $request): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo
            ->getFirstWhere(params: ['page_name' => 'user-home'])
            ?? $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);

        $chatExpertsQuery = ChatSession::with(['expert', 'category'])
            ->where('user_id', auth('customer')->id())
            ->whereNotNull('expert_id');

        // My Experts Filters
        if ($request->filled('my_category_id')) {
            $chatExpertsQuery->where('category_id', $request->my_category_id);
        }

        if ($request->filled('my_status')) {
            $chatExpertsQuery->where('status', $request->my_status);
        }

        if ($request->filled('my_search')) {
            $search = $request->my_search;
            $chatExpertsQuery->whereHas('expert', function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $chatExperts = $chatExpertsQuery
            ->latest('started_at')
            ->paginate(10, ['*'], 'chat_experts_page')
            ->withQueryString();

        $allExpertsQuery = Expert::with('category')
            ->where('is_active', true);

        // All Experts Filters
        if ($request->filled('all_category_id')) {
            $allExpertsQuery->where('category_id', $request->all_category_id);
        }

        if ($request->filled('all_search')) {
            $search = $request->all_search;
            $allExpertsQuery->where(function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allExperts = $allExpertsQuery
            ->paginate(10, ['*'], 'all_experts_page')
            ->withQueryString();

        $categories = ExpertCategory::active()->get();

        return view(VIEW_FILE_NAMES['user_experts'], compact(
            'chatExperts',
            'allExperts',
            'categories',
            'robotsMetaContentData'
        ));
    }
    public function getExpertView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'expert']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        return view(VIEW_FILE_NAMES['become_expert']);
    }
    public function getPricesView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'price']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $categories = ExpertCategory::active()->inRandomOrder()
            ->take(9)
            ->get();;

        return view(VIEW_FILE_NAMES['cms_price'], compact('categories'));
    }
    public function categorieView($id): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo
            ->getFirstWhere(params: ['page_name' => 'price'])
            ?? $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);

        $categories = ExpertCategory::active()
            ->withCount('experts')
            ->get();
        $popular_questions = $this->getSectionItems('popular_questions');

        $categorie = ExpertCategory::active()->findOrFail($id);

        $expert = Expert::where('category_id', $id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->first();
        if (!$expert) {
            $generalCategory = ExpertCategory::where('name', 'General')->first();

            if ($generalCategory) {
                $expert = Expert::where('category_id', $generalCategory->id)
                    ->where('status', 'approved')
                    ->where('is_active', true)
                    ->first();
            }
        }
        if (!$expert) {
            $expert = Expert::where('status', 'approved')
                ->where('is_active', true)
                ->first();
        }
        return view(
            VIEW_FILE_NAMES['categorie_view'],
            compact('categorie', 'categories', 'popular_questions', 'expert')
        );
    }

    public function getContactView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'contacts']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $recaptcha = getWebConfig(name: 'recaptcha');
        return view(VIEW_FILE_NAMES['contacts'], compact('recaptcha', 'robotsMetaContentData'));
    }
    public function getHelpView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'help']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        return view(VIEW_FILE_NAMES['help']);
    }
    public function knowledgeAll(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'help']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $kb = AdminHelpController::getSectionDataStatic('knowledge_base');
        return view(VIEW_FILE_NAMES['knowledge_all'], compact('kb'));
    }
    public function knowledgeRead($id): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'help']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $kb = AdminHelpController::getSectionDataStatic('knowledge_base');

        if (is_numeric($id)) {
            $item = $kb[$id] ?? null;
        } else {
            $item = collect($kb)->firstWhere('slug', $id);
        }

        if (!$item) {
            abort(404, 'Knowledge item not found');
        }
        return view(VIEW_FILE_NAMES['knowledge_read'], compact('item', 'id'));
    }


    public function getHelpTopicView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'helpTopic']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $helps = $this->helpTopicRepo->getListWhere(orderBy: ['id' => 'desc'], filters: ['status' => 1, 'type' => 'default'], dataLimit: 'all');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_faq_page'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['faq'], compact('helps', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getRefundPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'refund-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $refundPolicy = getWebConfig(name: 'refund-policy');
        if (!$refundPolicy['status']) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_refund_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['refund_policy_page'], compact('refundPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }
    public function getPrivacyPolicyView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'privacy-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $privacyPolicy = getWebConfig(name: 'privacy_policy');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_privacy_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['privacy_policy_page'], compact('privacyPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getTermsAndConditionView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'terms']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $termsCondition = getWebConfig(name: 'terms_condition');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_terms_conditions'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['terms_conditions_page'], compact('termsCondition', 'pageTitleBanner', 'robotsMetaContentData'));
    }

   
}
