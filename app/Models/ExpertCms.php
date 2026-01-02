<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertCms extends Model
{
    use HasFactory;

    protected $table = 'expert_cms';

    protected $fillable = [
        'section',
        'item_id',
        'cms_key',
        'value',
        'sort_order',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
