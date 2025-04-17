<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use App\Models\MeiCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreateMeiCategoryRequest extends FormRequest
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
            'type' => [
                'required',
                Rule::in([MeiCategory::GERAL, MeiCategory::TAC])
            ],
            'creation_date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('mei_categories')->where('user', $this->user()->id),
            ],
            'table_a_excluded_after_032022' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to create MEI category due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: MEI category\'s request data has passed all validation checks.');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'table_a_excluded_after_032022' => 'table A excluded',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'creation_date.unique' => 'A MEI category for the specified date already exists.',
        ];
    }
}
