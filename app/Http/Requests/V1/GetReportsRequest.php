<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && $this->user()->isSubscriber();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'year' => $this->query('year'),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['date_format:Y', 'nullable'],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to fetch subscriber reports due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Subscriber\'s reports request has passed all validation checks.');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.date_format' => 'The year param must match the format YYYY.',
        ];
    }
}
