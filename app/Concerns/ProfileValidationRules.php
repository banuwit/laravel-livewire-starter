<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function accountRules(?int $userId = null): array
    {
        return [
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
