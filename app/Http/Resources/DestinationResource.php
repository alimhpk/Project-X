<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'region' => $this->region,
            'costLevel' => $this->cost_level,
            'activities' => $this->activities,
            'averageDailyBudget' => $this->average_daily_budget,
            'annualVisitors' => $this->annual_visitors,
        ];
    }
}
