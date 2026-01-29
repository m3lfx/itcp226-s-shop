<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'item';
    public $timestamps = false;
    protected $primaryKey = 'item_id';
    protected $fillable = ['description', 'cost_price', 'sell_price', 'img_path'];
}
