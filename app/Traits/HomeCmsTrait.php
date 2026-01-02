<?php
namespace App\Traits;

use App\Models\HomeCms;

trait HomeCmsTrait
{
    public function getSection($section, $item_id = 0)
    {
        return HomeCms::where('section', $section)
            ->where('item_id', $item_id)
            ->where('status', true)
            ->pluck('value', 'cms_key')
            ->toArray();
    }

    public function getSectionItems($section)
    {
        $items = HomeCms::where('section', $section)
            ->where('status', true)
            ->select('item_id')
            ->distinct()
            ->orderBy('sort_order')
            ->pluck('item_id');

        return $items->map(function ($id) use ($section) {
            return $this->getSection($section, $id);
        });
    }
}