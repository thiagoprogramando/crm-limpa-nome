<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    protected $table = 'posts';
    
    protected $fillable = [
        'image',
        'title',
        'tags',
        'content',
        'access_type',
    ];

    public function labelTags() {
        
        $tags = array_filter(explode(' ', $this->tags));
        return collect($tags)->map(function ($tag) {
            return '<span class="badge bg-dark">' . e($tag) . '</span>';
        })->implode(' ');
    }

    public function labelResume() {
        
        $plainText = trim(strip_tags($this->content));
        $resume = mb_substr($plainText, 0, 69);

        return $resume . '...';
    }
}
