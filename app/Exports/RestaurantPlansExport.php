<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RestaurantPlansExport implements FromView
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.restaurant-plans', [
            'plans' => $this->data['plans'],
            'filters' => $this->data,
        ]);
    }
}
