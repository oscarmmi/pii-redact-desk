<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedactResult extends Model
{
    protected $fillable = [
        'redacted_text',
        'entities',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'entities' => 'array',
        ];
    }

    /** @return BelongsTo<RedactJob, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(RedactJob::class, 'redact_job_id');
    }
}
