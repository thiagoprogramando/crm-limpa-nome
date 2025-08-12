<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    protected $table = 'posts';
    
    protected $fillable = [
        'image',
        'title',
        'content',
        'access_type',
    ];

    public function labelResume() {
        
        $plainText = trim(strip_tags($this->content));
        $resume = mb_substr($plainText, 0, 69);

        return $resume . '...';
    }
}
