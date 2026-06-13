<?php

namespace App\Http\Requests\Web;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'email_verified' => ['nullable', 'boolean'],
        ];
    }
}
