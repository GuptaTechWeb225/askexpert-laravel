<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeCms;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $currentType = $request->get('section', 'hero');
        $typeList = $this->getSectionList();

        $status = HomeCms::where('section', $currentType)
            ->where('item_id', 0)
            ->where('cms_key', 'status')
            ->value('value') ?? 1;

        $currentSection = (object)['is_active' => $status];

        return view('admin-views.content-management.home.index', compact('currentType', 'typeList', 'currentSection'));
    }

    private function getSectionList()
    {
        return [
            'hero' => 'Hero Section',
            'quick_buttons' => 'Quick Questions',
            'popular_questions' => 'Popular Questions',
            'how_it_works' => 'How It Works',
            'why_love' => 'Why Love AskExpert',
            'testimonials' => 'Happy Members',
            'experts' => 'Our Experts',
        ];
    }

    public function getSectionData($section)
    {
        return HomeCms::where('section', $section)
            ->where('status', true)
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0);
    }

   public static function getSectionDataStatic($section)
{
    $itemIds = HomeCms::where('section', $section)
        ->where('status', true)
        ->orderByDesc('item_id') // ya created_at
        ->pluck('item_id')
        ->unique()
        ->take(4);

    return HomeCms::where('section', $section)
        ->where('status', true)
        ->whereIn('item_id', $itemIds)
        ->get()
        ->groupBy('item_id')
        ->map(fn ($group) => $group->pluck('value', 'cms_key')->toArray())
        ->toArray();
}


    public function addItem($section)
    {
        $max = HomeCms::where('section', $section)->max('item_id') ?? 0;
        return redirect()->back()->with('open_modal', ['section' => $section, 'item_id' => $max + 1]);
    }

    public function edit($section, $item_id = 0)
    {
        $data = HomeCms::where('section', $section)
            ->where('item_id', $item_id)
            ->pluck('value', 'cms_key')
            ->toArray();

        return view('admin-views.content-management.home.partials.edit', compact('section', 'item_id', 'data'));
    }

    public function update(Request $request, $section, $item_id = 0)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('home_cms', 'public');
                $value = 'storage/' . $path;
            }

            $sort = $request->input("sort_order_{$key}", 0);

            HomeCms::updateOrCreate(
                ['section' => $section, 'item_id' => $item_id, 'cms_key' => $key],
                ['value' => $value, 'sort_order' => $sort, 'status' => 1]
            );
        }

        return redirect()->route('admin.content-management.home', ['section' => $section])
            ->with('success', 'Updated successfully');
    }

    public function destroy($section, $item_id)
    {
        HomeCms::where('section', $section)->where('item_id', $item_id)->delete();
        return back()->with('success', 'Deleted!');
    }
}
