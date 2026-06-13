<?php

namespace App\Http\Requests\Web;

use App\Models\ApiClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApiClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ApiClient|null $apiClient */
        $apiClient = $this->route('apiClient');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/i', Rule::unique('api_clients', 'code')->ignore($apiClient)],
            'description' => ['nullable', 'string'],
            'owner_name' => ['nullable', 'string', 'max:191'],
            'owner_email' => ['nullable', 'email:rfc', 'max:191'],
            'allowed_ips' => ['nullable', 'array'],
            'allowed_ips.*' => ['string', 'max:100', 'distinct'],
            'allowed_origins' => ['nullable', 'array'],
            'allowed_origins.*' => ['string', 'max:191', 'distinct'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'rate_limit_per_day' => ['nullable', 'integer', 'min:1', 'max:100000000'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
