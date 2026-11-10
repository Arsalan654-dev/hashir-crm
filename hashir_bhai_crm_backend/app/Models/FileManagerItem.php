<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\FileManagerItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'type', 'size', 'date_label', 'icon',
    'icon_class', 'parent_id', 'user_id',
])]
class FileManagerItem extends Model
{
    /** @use HasFactory<FileManagerItemFactory> */
    use BelongsToOrganization, HasFactory;

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
