<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Subscriber extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'users';

    protected $attributes = [
        'role' => 'subscriber',
    ];

    protected $hidden = [
        'email',
        'secret_word',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<MagicLink, $this>
     */
    public function magicLinks(): HasMany
    {
        return $this->hasMany(MagicLink::class, 'user', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<MagicLink, $this>
     */
    public function latestMagicLink(): HasOne
    {
        return $this->magicLinks()->one()->latest('expires_at');
    }

    protected function cnpj(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::replaceMatches(
                pattern: '/[^A-Za-z0-9]/',
                replace: '',
                subject: $value
            )
        );
    }

    protected function mobilePhone(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::replaceMatches(
                pattern: '/[^0-9]/',
                replace: '',
                subject: $value
            )
        );
    }

    protected function state(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::upper($value) : null
        );
    }

    protected function mei(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::upper($value) : null
        );
    }
}
