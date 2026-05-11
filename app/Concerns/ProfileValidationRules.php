<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Account-level rules (auth concerns).
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function accountRules(?int $userId = null): array
    {
        return [
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function usernameRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'alpha_dash',
            'max:255',
            $userId === null
                ? Rule::unique(User::class, 'username')
                : Rule::unique(User::class, 'username')->ignore($userId),
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
