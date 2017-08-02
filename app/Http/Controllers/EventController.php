<?php

namespace App\Http\Controllers;

use App\Eloquents\SakeEvent;
use App\Http\Requests\SearchRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private $sakeEvent;

    private $paginate = 20;

    /**
     * EventController constructor.
     * @param SakeEvent $sakeEvent
     */
    public function __construct(SakeEvent $sakeEvent)
    {
        $this->sakeEvent = $sakeEvent;
    }

    /**
     * @param SearchRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(SearchRequest $request)
    {
        return view('welcome', [
            'date' => $request->date,
            'events' => $this->search($request->date)->with(['prefecture'])->paginate($this->paginate),
        ]);
    }

    /**
     * @param string $area
     * @param SearchRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function area(string $area, SearchRequest $request)
    {
        return view('welcome', [
            'area' => $area,
            'date' => $request->date,
            'events' => $this->search($request->date)->with(['prefecture'])
                ->whereHas('prefecture', function ($pref) use ($area) {
                    $pref->$area();
                })->paginate($this->paginate),
        ]);
    }

    /**
     * @param null|string $date
     * @return Builder
     */
    private function search(?string $date)
    {
        if (is_null($date)) {
            return $this->sakeEvent->current();
        }

        //終日の場合、ended_atは翌日00:00:00
        return $this->sakeEvent->where('ended_at', '>=', Carbon::parse($date));
    }
}
