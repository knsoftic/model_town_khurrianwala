<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'is_active', 'ip_address'])]
class NewsletterSubscriber extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
