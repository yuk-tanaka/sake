<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Prefecture extends Model
{
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    const UNKNOWN = 48;
    const UNKNOWN_NAME = '不明';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sakeEvents()
    {
        return $this->hasMany(SakeEvent::class);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeHokkaido($query)
    {
        return $query->where('id', 1);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTohoku($query)
    {
        return $query->whereIn('id', [2, 3, 4, 5, 6, 7]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeKanto($query)
    {
        return $query->whereIn('id', [8, 9, 10, 11, 12, 13, 14]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeKoshinetsu($query)
    {
        return $query->whereIn('id', [15, 19, 20]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeHokuriku($query)
    {
        return $query->whereIn('id', [16, 17, 18]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTokai($query)
    {
        return $query->whereIn('id', [21, 22, 23, 24]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeKansai($query)
    {
        return $query->whereIn('id', [25, 26, 27, 28, 29, 30]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeChugoku($query)
    {
        return $query->whereIn('id', [31, 32, 33, 34, 35]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeShikoku($query)
    {
        return $query->whereIn('id', [36, 37, 38, 39]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeKyusyu($query)
    {
        return $query->whereIn('id', [40, 41, 42, 43, 44, 45, 46, 47]);
    }


}
