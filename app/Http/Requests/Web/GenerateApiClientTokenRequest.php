<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class GenerateApiClientTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token_name' => ['required', 'string', 'max:191'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', 'max:100', 'distinct'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
