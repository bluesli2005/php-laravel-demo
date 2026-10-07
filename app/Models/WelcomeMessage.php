<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WelcomeMessage extends Model
{
    public const array PAGES = ['home', 'about', 'services', 'contact'];

    protected $fillable = [
        'page',
        'content',
    ];
}
