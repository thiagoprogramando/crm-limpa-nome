<?php

namespace App\Models;

use App\Http\Controllers\Gateway\AssasController;
use Illuminate\Database\Eloquent\Model;

class WebHook extends Model {
    
    protected $table = 'webhooks';

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'url',
        'email',
        'enabled',
        'interrupted',
        'apiVersion',
        'sendType'
    ];

    public function webhookAssas () {

        $assasController = new AssasController();
        return $assasController->webhookStatus($this->uuid);
    }
}
