<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class SakeEvent extends Model
{
    protected $casts = [
        'is_all_day' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'code',
        'summary',
        'prefecture_id',
        'location',
        'description',
        'started_at',
        'ended_at',
        'is_all_day',
        'is_recommended',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }
}
