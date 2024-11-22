<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SignUpRequest extends FormRequest
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
            'cnpj' => ['required', 'string', 'unique:users,cnpj', 'size:14'],
            'full_name' => ['required', 'string', 'max:100'],
            'mobile_phone' => ['required', 'string', 'size:11'],
            'email' => ['required', 'email:filter', 'unique:users,email', 'max:100'],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to validate the subscriber\'s request data due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Subscriber\'s request data has passed all validation checks.');
    }
}
