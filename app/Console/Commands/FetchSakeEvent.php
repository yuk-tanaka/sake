<?php

namespace App\Console\Commands;

use App\Eloquents\Prefecture;
use App\Eloquents\SakeEvent;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FetchSakeEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sake';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch 日本酒カレンダー';

    private $client;

    private $prefecture;

    private $sakeEvent;

    /**
     * FetchSakeEvent constructor.
     * @param Client $client
     * @param Prefecture $prefecture
     * @param SakeEvent $sakeEvent
     */
    public function __construct(Client $client, Prefecture $prefecture, SakeEvent $sakeEvent)
    {
        parent::__construct();

        $this->client = $client;

        $this->prefecture = $prefecture;

        $this->sakeEvent = $sakeEvent;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = collect($this->fetch())->sortBy('startDateTime');

        $this->save($events);
    }

    /**
     * 日本酒カレンダーのAPI URLを生成
     * @param Carbon $carbon
     * @return string URL
     */
    private function buildApiAddress(Carbon $carbon)
    {
        $url = 'http://nihonshucalendar.com/gcal.php';
        $start = $carbon->format('Y-m-d') . 'T00%3A00%3A00.000%2B09%3A00';
        $end = $carbon->today()->addYear()->format('Y-m-d') . 'T00%3A00%3A00.000%2B09%3A00';

        return $url . '?' . 'start-min=' . $start . '&start-max=' . $end;
    }

    /**
     * @return string JSON
     */
    private function fetch()
    {
        // 取得失敗時ClientException
        $response = $this->client->request('GET', $this->buildApiAddress(Carbon::today()));

        return json_decode($response->getBody(), true);
    }

    /**
     * 住所から都道府県を判定して都道府県番号を返す
     * @param string|null $location
     * @param Collection $prefectures ['県名' => id]の配列
     * @return int
     */
    private function parsePrefecture(?String $location, Collection $prefectures)
    {
        if (is_null($location)) {
            return Prefecture::UNKNOWN;
        }

        $pattern = '/東京都|北海道|(?:大阪|京都)府|(?:三重|兵庫|千葉|埼玉|大分|奈良|岐阜|岩手|島根|新潟|栃木|沖縄|熊本|福井|秋田
        |群馬|長野|青森|高知|鳥取|(?:宮|長)崎|(?:宮|茨)城|(?:佐|滋)賀|(?:静|福)岡|山(?:口|形|梨)|愛(?:媛|知)|(?:石|香|神奈)川|
        (?:富|岡|和歌)山|(?:福|広|徳|鹿児)島)県/';

        $result = preg_match($pattern, $location, $matches) ? $matches[0] : Prefecture::UNKNOWN_NAME;

        return $prefectures->get($result);
    }

    /**
     * @param Collection $events
     */
    private function save(Collection $events)
    {
        $prefectures = $this->prefecture->pluck('id', 'name');

        foreach ($events as $event) {
            $this->sakeEvent->updateOrCreate(
                ['code' => $event['id']],
                [
                    'code' => $event['id'],
                    'summary' => $event['summary'],
                    'prefecture_id' => $this->parsePrefecture($event['location'], $prefectures),
                    'location' => $event['location'],
                    'description' => $event['description'],
                    'started_at' => Carbon::parse($event['startDateTime']),
                    'ended_at' => Carbon::parse($event['endDateTime']),
                    'is_all_day' => $event['allDay'],
                    'is_recommended' => $event['recommend'],
                ]
            );
        }
    }
}
