<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MagicLink extends Model
{
    use HasFactory;
    use HasVersion4Uuids;
    use Prunable;

    protected $primaryKey = 'token';

    public $timestamps = false;

    protected $guarded = [
        'user',
        'used_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now()->toDateString());
    }

    public function fullUrl(): string
    {
        $hostUrl = config('app.client.url');
        $redirectUrl = config('app.client.redirect');
        $token = $this->token;

        return "{$hostUrl}/authenticate/{$token}&redirect={$redirectUrl}";
    }

    public function isUsed(): bool
    {
        return !empty($this->used_at);
    }

    public function isNotUsed(): bool
    {
        return empty($this->used_at);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->expires_at);
    }

    public function isNotExpired(): bool
    {
        return Carbon::now()->lessThan($this->expires_at);
    }
}
