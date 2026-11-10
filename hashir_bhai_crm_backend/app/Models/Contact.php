<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'position', 'email', 'phone', 'avatar', 'badge_class',
    'badge_text_class', 'projects', 'bio', 'address', 'city',
    'country', 'zip', 'company',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use BelongsToOrganization, HasFactory;
}
