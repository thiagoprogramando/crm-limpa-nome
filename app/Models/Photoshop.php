<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photoshop extends Model {

    use HasFactory;

    protected $table = 'photoshop';

    protected $fillable = [
        'name',
        'file',
        'level'
    ];
}
