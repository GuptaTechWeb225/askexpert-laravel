<?php

namespace App\Http\Controllers\Web;

use App\Traits\CacheManagerTrait;
use App\Traits\EmailTemplateTrait;
use App\Traits\InHouseTrait;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ExpertCategory;
use App\Utils\ProductManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\HomeCmsTrait;

class HomeController extends Controller
{
    use HomeCmsTrait;


    public function __construct(
        private readonly Review       $review,

    ) {}


    public function index(): View
    {
        $themeName = theme_root_path();
        return match ($themeName) {
            'default' => self::default_theme(),
        };
    }

    public function default_theme(): View
    {
        

        $data = [
            'hero' => $this->getSection('hero'),
            'quick_buttons'    => $this->getSectionItems('quick_buttons'),     // ← Fixed
            'categories'       => $this->getSectionItems('expert_categories'), // ← Fixed
            'popular_questions' => $this->getSectionItems('popular_questions'),
            'how_it_works' => $this->getSectionItems('how_it_works'),
            'why_love' => $this->getSectionItems('why_love'),
            'testimonials' => $this->getSectionItems('testimonials'),
            'experts' => $this->getSectionItems('experts'),
        ];
                $categories = ExpertCategory::active()->get();


        return view(
            VIEW_FILE_NAMES['home'],
            compact(
                'data','categories'
            )
        );
    }
}
