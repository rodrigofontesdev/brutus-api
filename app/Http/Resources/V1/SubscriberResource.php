<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'cnpj' => $this->cnpj,
            'mobile_phone' => $this->mobile_phone,
            'city' => $this->city,
            'sate' => $this->state,
            'secret_word' => $this->secret_word,
            'mei' => $this->mei,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
