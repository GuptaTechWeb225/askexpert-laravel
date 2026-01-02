<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpertCms;

class ExpertCmsController extends Controller
{
    /** --------------------------------------------------------------
     *  INDEX – list of sections (hero, why_join …)
     *  -------------------------------------------------------------- */
    public function index(Request $request)
    {
        $currentType = $request->get('section', 'hero');
        $typeList    = $this->getSectionList();

        $status = ExpertCms::where('section', $currentType)
            ->where('item_id', 0)
            ->where('cms_key', 'status')
            ->value('value') ?? 1;

        $currentSection = (object)['is_active' => $status];

        return view('admin-views.content-management.expert.index', compact(
            'currentType', 'typeList', 'currentSection'
        ));
    }

    /** --------------------------------------------------------------
     *  SECTION LIST (used in admin menu & titles)
     *  -------------------------------------------------------------- */
    private function getSectionList()
    {
        return [
            'hero'          => 'Hero Section',
            'why_join'      => 'Why Join Ask Expert Online',
            'how_it_works'  => 'How It Works',
            'testimonials'  => 'What Experts Say',
            'cta'           => 'Bottom CTA Card',
        ];
    }

    /** --------------------------------------------------------------
     *  STATIC helper – used on front-end & admin partials
     *  -------------------------------------------------------------- */
    public static function getSectionDataStatic($section)
    {
        return ExpertCms::where('section', $section)
            ->active()
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0)
            ->toArray();
    }

    /** --------------------------------------------------------------
     *  EDIT modal form (AJAX)
     *  -------------------------------------------------------------- */
    public function editData($section, $item_id = 0)
    {
        $data = ExpertCms::where('section', $section)
            ->where('item_id', $item_id)
            ->pluck('value', 'cms_key')
            ->toArray();

        return view('admin-views.content-management.expert.partials.edit-modal-form', compact(
            'section', 'item_id', 'data'
        ));
    }

    /** --------------------------------------------------------------
     *  STORE / UPDATE
     *  -------------------------------------------------------------- */
    public function update(Request $request, $section, $item_id = 0)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('expert_cms', 'public');
                $value = 'storage/' . $path;
            }

            ExpertCms::updateOrCreate(
                ['section' => $section, 'item_id' => $item_id, 'cms_key' => $key],
                ['value' => $value, 'sort_order' => 0, 'status' => 1]
            );
        }

        return back()->with('success', 'Updated!');
    }

    /** --------------------------------------------------------------
     *  DELETE item (when item_id > 0)
     *  -------------------------------------------------------------- */
    public function destroy($section, $item_id)
    {
        ExpertCms::where('section', $section)
            ->where('item_id', $item_id)
            ->delete();

        return back()->with('success', 'Deleted!');
    }

    /** --------------------------------------------------------------
     *  TOGGLE whole section status
     *  -------------------------------------------------------------- */
    public function toggleStatus(Request $request)
    {
        $type   = $request->input('type');
        $status = $request->input('status');

        ExpertCms::updateOrCreate(
            ['section' => $type, 'item_id' => 0, 'cms_key' => 'status'],
            ['value' => $status]
        );

        return response()->json(['success' => true]);
    }
}