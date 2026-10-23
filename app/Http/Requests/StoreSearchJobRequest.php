<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSearchJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedCities = config('prospector.region.cities');

        return [
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => [Rule::in($allowedCities)],
            'segment' => ['nullable', Rule::in(config('prospector.segments'))],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_active', true)],
            'target_quantity' => [
                'required', 'integer',
                'min:1', 'max:' . config('prospector.search.max_target', 200),
            ],
        ];
    }
}
