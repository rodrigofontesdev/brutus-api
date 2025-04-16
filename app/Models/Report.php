<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;
    use HasVersion4Uuids;

    protected $guarded = [
        'user',
        'period',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    protected function tradeWithInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }

    protected function tradeWithoutInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }

    protected function industryWithInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }

    protected function industryWithoutInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }

    protected function servicesWithInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }

    protected function servicesWithoutInvoice(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => 0 === $value ? null : $value,
        );
    }
}
