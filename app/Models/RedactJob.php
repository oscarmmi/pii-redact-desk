<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RedactJob extends Model
{
    protected $fillable = [
        'source_text',
        'status',
    ];

    /** @return HasOne<RedactResult, $this> */
    public function result(): HasOne
    {
        return $this->hasOne(RedactResult::class);
    }
}
