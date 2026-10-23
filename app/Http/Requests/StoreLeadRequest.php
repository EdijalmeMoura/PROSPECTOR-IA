<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:180'],
            'trade_name' => ['nullable', 'string', 'max:180'],
            'segment' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'place_id' => ['nullable', 'string', 'max:180'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_active', true)],
            'contact_channel' => ['nullable', Rule::in(['email', 'whatsapp', 'instagram', 'telefone', 'site'])],
            'contact_value' => ['nullable', 'string', 'max:180', 'required_with:contact_channel'],
            'contact_permission' => ['nullable', Rule::in(['desconhecida', 'permitida', 'negada'])],
            'evidence_type' => ['nullable', 'string', 'max:60'],
            'evidence_description' => ['nullable', 'string', 'max:1000', 'required_with:evidence_type'],
            'evidence_reliability' => ['nullable', Rule::in(['baixa', 'media', 'alta'])],
        ];
    }
}
