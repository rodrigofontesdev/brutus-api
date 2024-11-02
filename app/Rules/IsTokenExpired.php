<?php

namespace App\Rules;

use App\Models\MagicLink;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class IsTokenExpired implements ValidationRule
{
    /**
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isTokenInvalid = !Str::isUuid($value);

        if ($isTokenInvalid) {
            $fail('validation.uuid')->translate();
            return;
        }

        $currentDateTime = now()->toDateTimeString();
        $isTokenExpired = MagicLink::where('token', $value)
            ->where('expires_at', '<', $currentDateTime)
            ->exists();

        if ($isTokenExpired) {
            $fail("The selected {$attribute} is expired.");
        }
    }
}
