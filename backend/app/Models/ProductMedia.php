<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_media')]
#[Fillable(['product_id', 'type', 'path', 'title', 'mime_type', 'size', 'youtube_url', 'sort_order'])]
class ProductMedia extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['image', 'video', 'document'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return str_starts_with((string) $this->path, 'http')
            ? $this->path
            : asset($this->path);
    }

    public function isExternal(): bool
    {
        return $this->type === 'video' && filled($this->youtube_url);
    }
}
