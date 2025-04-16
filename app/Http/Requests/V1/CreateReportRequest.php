<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreateReportRequest extends FormRequest
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
            'trade_with_invoice' => ['present', 'integer'],
            'trade_without_invoice' => ['present', 'integer'],
            'industry_with_invoice' => ['present', 'integer'],
            'industry_without_invoice' => ['present', 'integer'],
            'services_with_invoice' => ['present', 'integer'],
            'services_without_invoice' => ['present', 'integer'],
            'period' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('reports')->where('user', $this->user()->id)
            ],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to create report due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Report\'s request data has passed all validation checks.');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'period.unique' => 'A report for the specified period already exists.',
        ];
    }
}
