<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('knowledge_articles')]
#[Fillable([
    'title', 'category', 'type', 'content', 'tags', 'priority', 'is_active',
    'share_with_bot', 'product_id', 'created_by',
])]
class KnowledgeArticle extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['faq', 'instruction', 'policy', 'product_info', 'script', 'pricing', 'objection'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
            'share_with_bot' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
