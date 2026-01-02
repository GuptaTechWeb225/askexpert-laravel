<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_id', 'table_size', 'table_count', 'avg_turnover_time','status'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
