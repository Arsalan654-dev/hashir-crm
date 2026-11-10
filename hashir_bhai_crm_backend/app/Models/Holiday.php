<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('holidays')]
#[Fillable(['name', 'date', 'is_paid', 'description'])]
class Holiday extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_paid' => 'boolean',
        ];
    }
}
