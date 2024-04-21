<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model {

    use HasFactory;

    use HasFactory;

    protected $table = 'product_item';

    protected $fillable = [
        'id_product',

        'name',
        'description',

        'type',
        'item'
    ];
}
