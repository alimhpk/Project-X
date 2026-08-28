<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'country', 'region', 'cost_level', 'activities', 'average_daily_budget', 'annual_visitors'])]
class Destination extends Model
{
    use HasFactory;

    public const COST_LEVELS = ['Budget', 'Moderate', 'Premium', 'Luxury'];

    public const SORTS = [
        'name' => 'name',
        'country' => 'country',
        'region' => 'region',
        'costLevel' => 'cost_level',
        'averageDailyBudget' => 'average_daily_budget',
        'annualVisitors' => 'annual_visitors',
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
            'average_daily_budget' => 'integer',
            'annual_visitors' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $query, string $term) => $query->where(
            fn (Builder $query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%")
                ->orWhere('region', 'like', "%{$term}%")
        ));
    }
}
