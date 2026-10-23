<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
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
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'recommended_service_reason' => ['nullable', 'string', 'max:1000'],
            'opportunity_evidence_summary' => ['nullable', 'string', 'max:2000'],
            'contact_channel' => ['nullable', Rule::in(['email', 'whatsapp', 'instagram', 'telefone', 'site'])],
            'contact_value' => ['nullable', 'string', 'max:180'],
            'contact_permission' => ['nullable', Rule::in(['desconhecida', 'permitida', 'negada'])],
        ];
    }
}
