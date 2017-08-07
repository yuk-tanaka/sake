<?php

namespace App\Console\Commands;

use App\Eloquents\BeerEvent;
use App\Eloquents\Prefecture;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * CRONはPHP5.6環境
 */
class FetchBeerEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:beer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch ビールイベント';

    private $prefecture;

    private $beerEvent;

    /** @var array */
    private $data;

    /**
     * FetchSakeEvent constructor.
     * @param Prefecture $prefecture
     * @param BeerEvent $beerEvent
     */
    public function __construct(Prefecture $prefecture, BeerEvent $beerEvent)
    {
        parent::__construct();

        $this->prefecture = $prefecture;

        $this->beerEvent = $beerEvent;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->prefecture = $this->prefecture->pluck('id', 'name');

        $events = $this->scrape()->format();

        $this->save($events);
    }


    /**
     * https://craftbeer.brnavi.com/beer-festival-event.htmlからスクレイピング
     * データは$this->data
     *
     * @return $this
     */
    private function scrape()
    {
        $crawler = \Goutte::request('GET', 'https://craftbeer.brnavi.com/beer-festival-event.html');

        $crawler->filter('table.tbl_std tr')->each(function ($node, $i) {
            $node->filter('td p')->each(function ($node_b) use ($i) {
                //title
                if (count($node_b->filter('strong'))) {
                    $this->data[$i]['summary'] = $node_b->text();
                }

                //date&address
                if ($node_b->attr('style') === 'line-height: 1.7;') {
                    //@で日付と開催地を分割 日付は要フォーマット
                    $explode = explode('@', $node_b->text());
                    //titleの次のループ
                    $this->data[$i]['date'] = $explode[0];
                    $this->data[$i]['location'] = isset($explode[1]) ? $explode[1] : null;
                };
            });

            //pref td styleの場合とtd p styleの場合がある
            $node->filter('td')->each(function ($node_b) use ($i) {
                if (strpos($node_b->attr('style'), 'padding: 10px; font-size: 12px;') !== false) {
                    $this->data[$i]['pref'] = $node_b->text();
                }
            });
            $node->filter('td p')->each(function ($node_b) use ($i) {
                if (strpos($node_b->attr('style'), 'padding: 10px; font-size: 12px;') !== false) {
                    $this->data[$i]['pref'] = $node_b->text();
                }
            });

            //url
            $node->filter('td p.lkbx.cl-hm a')->each(function ($node_b) use ($i) {
                $this->data[$i]['url'] = $node_b->attr('href');
            });
        });

        return $this;
    }

    /**
     * @return Collection
     */
    private function format()
    {
        foreach ($this->data as $i => $row) {
            $date = isset($row['date']) ? trim($row['date']) : null;

            $events[$i]['code'] = trim($row['summary']);
            $events[$i]['url'] = trim(isset($row['url']) ? $row['url'] : null);
            $events[$i]['summary'] = trim($row['summary']);
            $events[$i]['prefecture_id'] = $this->parsePrefecture(isset($row['pref']) ? $row['pref'] : null);
            $events[$i]['location'] = trim(isset($row['location']) ? $row['location'] : null);
            $events[$i]['started_at'] = $this->parseStartedAt($date);
            $events[$i]['ended_at'] = $this->parseEndedAt($date);
        }
        return collect($events);
    }

    /**
     * 住所から都道府県を判定して都道府県番号を返す
     * 元データが[宮城・仙台]のような形式のためループして対応
     * @param string|null $location
     * @return int
     */
    private function parsePrefecture($location = null)
    {
        if (is_null($location)) {
            return Prefecture::UNKNOWN;
        }

        foreach ($this->prefecture as $pref => $id) {
            if (mb_strpos($location, mb_substr($pref, 0, mb_strlen($pref) - 1)) !== false) {
                return $id;
            }
        }

        return Prefecture::UNKNOWN;
    }

    /**
     * @param string $date
     * @return null|Carbon
     */
    private function parseStartedAt($date)
    {
        if (is_null($date)) {
            return null;
        }

        //YYYY年mm月dd日からparse
        try {
            return Carbon::parse(str_replace(['年', '月'], '-', mb_substr($date, 0, mb_strpos($date, '日'))));
        } catch (\Exception $e) {
            Log::error($e);
            return null;
        }

    }

    /**
     * @param string $date
     * @return null|Carbon
     */
    private function parseEndedAt($date)
    {
        if (is_null($date)) {
            return null;
        }

        //区切り文字ハイフンがなければ単日
        if (mb_strpos('-', $date) === false) {
            $start = $this->parseStartedAt($date);
            return $start ? $this->parseStartedAt($date)->addDay() : null;
        }

        //YYYY年mm月dd日(d) - mm月dd日(d)からparse
        try {
            return Carbon::parse(mb_substr($date, 0, 4) . '-' . str_replace(['月', '日'], ['-', ''],
                    mb_substr($date, mb_strpos($date, '-') + 2, -3)));
        } catch (\Exception $e) {
            Log::error($e);
            return null;
        }
    }

    /**
     * @param Collection $events
     */
    private function save(Collection $events)
    {
        foreach ($events as $event) {
            $message[] = $this->beerEvent->updateOrCreate(
                ['code' => $event['code']],
                [
                    'code' => $event['code'],
                    'url' => $event['url'],
                    'summary' => $event['summary'],
                    'prefecture_id' => $event['prefecture_id'],
                    'location' => $event['location'],
                    'description' => '',
                    'started_at' => $event['started_at'],
                    'ended_at' => $event['ended_at'],
                    'is_all_day' => true,
                    'is_recommended' => false,
                ]
            );
        }

        Log::info(count($message));
    }
}
