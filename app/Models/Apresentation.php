<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apresentation extends Model {
    use HasFactory;

    protected $table = 'archive_start';

    protected $fillable = [
        'level',
        'title',
        'file',
    ];
}
