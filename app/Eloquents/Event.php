<?php

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    /** @var string $type イベントの属性 オーバーライド先で設定 */
    protected $typeName;

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
        'url',
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

    /**
     * オーバーライド
     * グローバルスコープ設定
     * マニュアルの方法だとstatic bootメソッドを操作しなければならない
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $query = parent::newQuery();

        if ($this->typeName) {
            $query = $query->where('type', $this->typeName);
        }

        return $query;
    }

    /**
     * オーバーライド
     * typeカラムに値を代入
     * @param  array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->type = $this->typeName;

        return parent::save();
    }

    /**
     * @return Builder
     */
    public function scopeCurrent()
    {
        return $this->where('ended_at', '>', Carbon::today());
    }

    /**
     * @return Builder
     */
    public function scopeSake()
    {
        return SakeEvent::query();
    }

    /**
     * @return Builder
     */
    public function scopeBeer()
    {
        return BeerEvent::query();
    }

    /**
     * ラベル用bootstrap classを返す
     * @return string
     */
    public function getColorAttribute()
    {
        switch ($this->type) {
            case '日本酒カレンダー' :
                return 'label-primary';
            case 'クラフトビール' :
                return 'label-success';
            default:
                return 'label-default';
        }
    }

    /**
     * 開始時間と終了時間から開催日時を取得
     * @return string
     */
    public function getDateAttribute()
    {
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        if (is_null($this->started_at) || is_null($this->ended_at)) {
            return '日時不明';
        }

        if ($this->isOneAllDay()) {
            return $this->started_at->formatLocalized('%Y年%m月%d日(%a)');
        }

        if ($this->is_all_day) {
            $start = $this->started_at->formatLocalized('%Y年%m月%d日(%a)');
        } else {
            $start = $this->started_at->formatLocalized('%Y年%m月%d日(%a) %H:%M');
        }

        if ($this->isSameDay()) {
            //終了日が同日なら日付表記を省略
            $end = $this->ended_at->format('H:i');
        } elseif ($this->is_all_day) {
            $end = $this->ended_at->formatLocalized('%m月%d日(%a)');
        } else {
            $end = $this->ended_at->formatLocalized('%m月%d日(%a) %H:%M');
        }

        return $start . '～' . $end;
    }

    /**
     * @return bool
     */
    private function isOneAllDay()
    {
        return $this->is_all_day && $this->started_at->addDay()->startOfDay()->eq($this->ended_at);
    }

    /**
     * @return bool
     */
    private function isSameDay()
    {
        return $this->started_at->startOfDay()->eq($this->ended_at->startOfDay());
    }

    /**
     * @return string
     */
    public function getShortDescriptionAttribute()
    {
        $limit = 200;

        return str_limit($this->description, $limit);
    }
}
