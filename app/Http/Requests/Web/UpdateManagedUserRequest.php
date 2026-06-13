<?php

namespace App\Http\Requests\Web;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'email_verified' => ['nullable', 'boolean'],
        ];
    }
}
