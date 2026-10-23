<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:180'],
            'trade_name' => ['nullable', 'string', 'max:180'],
            'segment' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'place_id' => ['nullable', 'string', 'max:180'],
            'legal_basis' => ['nullable', 'string', 'in:' . implode(',', config('prospector.legal_bases'))],
        ];
    }
}
