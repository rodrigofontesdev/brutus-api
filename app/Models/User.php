<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasVersion4Uuids;
    use SoftDeletes;
    use Notifiable;

    protected $hidden = [
        'email',
        'mobile_phone',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<MeiCategory, $this>
     */
    public function meiCategories(): HasMany
    {
        return $this->hasMany(MeiCategory::class, 'user', 'id')->latest('creation_date');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<MeiCategory, $this>
     */
    public function firstMeiCategory(): HasOne
    {
        return $this->hasOne(MeiCategory::class, 'user', 'id')->oldest('creation_date');
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

    protected function secretWord(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Str::upper($value) : null
        );
    }

    public function scopeSubscriber(Builder $query): void
    {
        $query->where('role', 'subscriber');
    }

    public function isSubscriber(): bool
    {
        return $this->role === 'subscriber';
    }
}
