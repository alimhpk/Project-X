<?php

namespace App\Http\Requests;

use App\Models\Destination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDestinationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'costLevel' => ['nullable', Rule::in(Destination::COST_LEVELS)],
            'sort' => ['nullable', Rule::in(array_keys(Destination::SORTS))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'perPage' => ['nullable', 'integer', 'between:1,50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
