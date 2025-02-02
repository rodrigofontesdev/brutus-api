<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateReportRequest extends FormRequest
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
            'trade_with_invoice' => ['present', 'integer', 'nullable'],
            'trade_without_invoice' => ['present', 'integer', 'nullable'],
            'industry_with_invoice' => ['present', 'integer', 'nullable'],
            'industry_without_invoice' => ['present', 'integer', 'nullable'],
            'services_with_invoice' => ['present', 'integer', 'nullable'],
            'services_without_invoice' => ['present', 'integer', 'nullable'],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to update report due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Report update request data has passed all validation checks.');
    }
}
