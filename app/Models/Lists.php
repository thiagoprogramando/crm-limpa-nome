<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lists extends Model {

    use HasFactory;

    protected $table = 'list';

    protected $fillable = [
        'name',
        'description',
        'start',
        'end',
        'status',
    ];

    public function statusLabel() {
        switch ($this->status) {
            case '1':
                return 'Ativa';
            case 2:
                return 'Inativa';
            default:
                return 'carregando...';
        }
    }
}