<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PricingCms;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        $currentType = $request->get('section', 'hero');
        $typeList = $this->getSectionList();

        $status = PricingCms::where('section', $currentType)
            ->where('item_id', 0)
            ->where('cms_key', 'status')
            ->value('value') ?? 1;

        $currentSection = (object)['is_active' => $status];

        return view('admin-views.content-management.pricing.index', compact('currentType', 'typeList', 'currentSection'));
    }

    private function getSectionList()
    {
        return [
            'hero' => 'Hero Section',
            'all_plans' => 'What’s Included in All Plans',
            'faq' => 'Pricing FAQ',
        ];
    }

    public static function getSectionDataStatic($section)
    {
        return PricingCms::where('section', $section)
            ->where('status', true)
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0)
            ->toArray();
    }

    public function editData($section, $item_id = 0)
    {
        $data = PricingCms::where('section', $section)
            ->where('item_id', $item_id)
            ->pluck('value', 'cms_key')
            ->toArray();

        return view('admin-views.content-management.pricing.partials.edit-modal-form', compact('section', 'item_id', 'data'));
    }

public function update(Request $request, $section, $item_id = 0)
{
    $inputs = $request->except(['_token', '_method']);

    // ✅ PRICING FAQ CREATE CASE
    if ($section === 'faq' && $item_id == 0) {

        // last item_id nikaalo
        $lastItemId = PricingCms::where('section', 'faq')->max('item_id');
        $item_id = $lastItemId ? $lastItemId + 1 : 1; // 👈 always +1
    }

    foreach ($inputs as $key => $value) {

        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $path = $file->store('pricing_cms', 'public');
            $value = 'storage/' . $path;
        }

        PricingCms::updateOrCreate(
            [
                'section' => $section,
                'item_id' => $item_id,
                'cms_key' => $key
            ],
            [
                'value' => $value,
                'sort_order' => 0,
                'status' => 1
            ]
        );
    }

    return back()->with('success', 'Saved!');
}


    public function destroy($section, $item_id)
    {
        PricingCms::where('section', $section)->where('item_id', $item_id)->delete();
        return back()->with('success', 'Deleted!');
    }

    public function toggleStatus(Request $request)
    {
        $type = $request->type;
        $status = $request->status;

        PricingCms::updateOrCreate(
            ['section' => $type, 'item_id' => 0, 'cms_key' => 'status'],
            ['value' => $status]
        );

        return response()->json(['success' => true]);
    }
}