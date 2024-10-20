<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasFactory;

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [
        'user',
        'used_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Subscriber, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, 'user', 'id');
    }

    public function fullUrl(): string
    {
        $hostUrl = config('app.client.url');
        $redirectUrl = config('app.client.redirect');
        $token = $this->token;

        return "{$hostUrl}/authenticate/{$token}&redirect={$redirectUrl}";
    }
}
