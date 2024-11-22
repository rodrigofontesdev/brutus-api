<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SignInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cnpj' => [
                'required',
                'string',
                'size:14',
                Rule::exists('users')->where(function (Builder $query) {
                    $query->where('role', 'subscriber');
                }),
            ],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to validate the sign in request due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Subscriber\'s sign in credentials have been validated successfully.');
    }
}
