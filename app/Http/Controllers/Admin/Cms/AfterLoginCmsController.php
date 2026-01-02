<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AfterLoginCms;
use Illuminate\Support\Facades\Log;

class AfterLoginCmsController extends Controller
{
    public function index(Request $request)
    {
        $currentType = $request->get('section', 'hero');
        $typeList = $this->getSectionList();

        $status = AfterLoginCms::where('section', $currentType)
            ->where('item_id', 0)
            ->where('cms_key', 'status')
            ->value('value') ?? 1;

        $currentSection = (object)['is_active' => $status];

        return view('admin-views.content-management.after-login.index', compact('currentType', 'typeList', 'currentSection'));
    }

    private function getSectionList()
    {
        return [
            'hero' => 'Hero Section',
            'my_questions' => 'My Questions',
            'my_experts' => 'My Experts',
        ];
    }

    public function getSectionData($section)
    {
        return AfterLoginCms::where('section', $section)
            ->where('status', true)
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0);
    }

    public static function getSectionDataStatic($section)
    {
        return AfterLoginCms::where('section', $section)
            ->where('status', true)
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->pluck('value', 'cms_key')->toArray())
            ->sortBy(fn($item) => $item['sort_order'] ?? 0)
            ->toArray();
    }

    public function addItem($section)
    {
        $max = AfterLoginCms::where('section', $section)->max('item_id') ?? 0;
        return redirect()->back()->with('open_modal', ['section' => $section, 'item_id' => $max + 1]);
    }

    public function edit($section, $item_id = 0)
    {
        $data = AfterLoginCms::where('section', $section)
            ->where('item_id', $item_id)
            ->pluck('value', 'cms_key')
            ->toArray();

        return view('admin-views.content-management.after-login.partials.edit', compact('section', 'item_id', 'data'));
    }

    public function update(Request $request, $section, $item_id = 0)
    {
        Log::info('request is ', ['request' => $request->all()]);
                    Log::info('sort is ', ['sort' => $section]);
                                Log::info('sort is ', ['sort' => $item_id]);

                                

        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('home_cms', 'public');
                $value = 'storage/' . $path;
            }

            $sort = $request->input("sort_order_{$key}", 0);
            Log::info('sort is ', ['sort' => $sort]);

            AfterLoginCms::updateOrCreate(
                ['section' => $section, 'item_id' => $item_id, 'cms_key' => $key],
                ['value' => $value, 'sort_order' => $sort, 'status' => 1]
            );
        }

        return redirect()->route('admin.content-management.after-login', ['section' => $section])
            ->with('success', 'Updated successfully');
    }

    public function destroy($section, $item_id)
    {
        AfterLoginCms::where('section', $section)->where('item_id', $item_id)->delete();
        return back()->with('success', 'Deleted!');
    }
}
