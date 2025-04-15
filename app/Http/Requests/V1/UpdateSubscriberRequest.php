<?php

namespace App\Http\Requests\V1;

use App\Exceptions\V1\InvalidRequestException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'email:filter',
                Rule::unique('users', 'email')->ignore($this->id),
                'max:100',
            ],
            'full_name' => ['string', 'max:100'],
            'mobile_phone' => ['string', 'size:11'],
            'city' => ['string', 'max:100'],
            'state' => ['in:AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SE,SP,TO'],
            'secret_word' => ['string', 'max:50'],
        ];
    }

    /**
     * @throws \App\Exceptions\V1\InvalidRequestException;
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new InvalidRequestException(message: self::class.':: Unable to update subscriber due to missing or invalid parameters.', validator: $validator);
    }

    protected function passedValidation(): void
    {
        Log::info(self::class.':: Subscriber\'s update request data has passed all validation checks.');
    }
}
