<?php

namespace App\Rules;

use App\Models\MagicLink;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class IsTokenUsed implements ValidationRule
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

        $isTokenUsed = MagicLink::where('token', $value)->whereNotNull('used_at')->exists();

        if ($isTokenUsed) {
            $fail("The selected {$attribute} has already been used.");
        }
    }
}
