<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpCms extends Model
{
    use HasFactory;

    protected $fillable = ['section', 'item_id', 'cms_key', 'value', 'sort_order', 'status'];
}
