<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MeiCategory extends Model
{
    use HasFactory;
    use HasVersion4Uuids;

    public const GERAL = 'MEI-GERAL';
    public const TAC = 'MEI-TAC';
    public const GERAL_LIMIT = 8_100_000;
    public const TAC_LIMIT = 25_160_000;

    public $timestamps = false;

    protected $guarded = [
        'user',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    protected function casts(): array
    {
        return [
            'table_a_excluded_after_032022' => 'boolean',
        ];
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::upper($value)
        );
    }
}
