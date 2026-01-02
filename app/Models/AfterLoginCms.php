<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfterLoginCms extends Model
{
    use HasFactory;
    protected $fillable = ['section', 'cms_key', 'value', 'item_id', 'sort_order', 'status'];

}
