<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\EmailMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'from_name', 'from_email', 'to_email', 'subject', 'body', 'teaser',
    'avatar', 'folder', 'threads', 'time_label', 'date_label',
    'is_read', 'is_starred', 'is_important', 'received_at',
])]
class EmailMessage extends Model
{
    /** @use HasFactory<EmailMessageFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'is_important' => 'boolean',
            'received_at' => 'datetime',
        ];
    }
}
