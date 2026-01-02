<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HelpCms;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        $currentType = $request->get('section', 'hero');
        $typeList = $this->getSectionList();

        $status = HelpCms::where('section', $currentType)
            ->where('item_id', 0)
            ->where('cms_key', 'status')
            ->value('value') ?? 1;

        $currentSection = (object)['is_active' => $status];

        return view('admin-views.content-management.help.index', compact('currentType', 'typeList', 'currentSection'));
    }

    private function getSectionList()
    {
        return [
            'hero' => 'Hero Section',
            'faq_general' => 'General FAQ',
            'faq_billing' => 'Billing FAQ',
            'faq_account' => 'Account FAQ',
            'faq_experts' => 'Experts FAQ',
            'knowledge_base' => 'Knowledge Base',
        ];
    }

    public static function getSectionDataStatic($section)
    {
        return HelpCms::where('section', $section)
            ->where('status', true)
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0)
            ->toArray();
    }

    public function editData($section, $item_id = 0)
    {
        $data = HelpCms::where('section', $section)
            ->where('item_id', $item_id)
            ->pluck('value', 'cms_key')
            ->toArray();

        return view('admin-views.content-management.help.partials.edit-modal-form', compact('section', 'item_id', 'data'));
    }

    public function update(Request $request, $section, $item_id = 0)
    {
        $inputs = $request->except(['_token', '_method']);
        foreach ($inputs as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('help_cms', 'public');
                $value = 'storage/' . $path;
            }
            HelpCms::updateOrCreate(
                ['section' => $section, 'item_id' => $item_id, 'cms_key' => $key],
                ['value' => $value, 'sort_order' => 0, 'status' => 1]
            );
        }
        return back()->with('success', 'Updated!');
    }

    public function destroy($section, $item_id)
    {
        HelpCms::where('section', $section)->where('item_id', $item_id)->delete();
        return back()->with('success', 'Deleted!');
    }

    public function toggleStatus(Request $request)
    {
        $type = $request->type;
        $status = $request->status;

        HelpCms::updateOrCreate(
            ['section' => $type, 'item_id' => 0, 'cms_key' => 'status'],
            ['value' => $status]
        );

        return response()->json(['success' => true]);
    }
}