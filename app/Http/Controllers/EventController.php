<?php

namespace App\Http\Controllers;

use App\Eloquents\Event;
use App\Http\Requests\SearchRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EventController extends Controller
{
    private $event;

    private $paginate = 20;

    /**
     * EventController constructor.
     * @param Event $Event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @param SearchRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(SearchRequest $request)
    {
        return view('welcome', [
            'date' => $request->date ?? Carbon::today()->format('Y-m-d'),
            'type' => $request->type,
            'events' => $this->search($request->type, $request->date)
                ->with(['prefecture'])->paginate($this->paginate),
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
            'date' => $request->date ?? Carbon::today()->format('Y-m-d'),
            'type' => $request->type,
            'events' => $this->search($request->type, $request->date)->with(['prefecture'])
                ->whereHas('prefecture', function ($pref) use ($area) {
                    $pref->$area();
                })->paginate($this->paginate),
        ]);
    }

    /**
     * @param null|string $date
     * @return Builder
     */
    private function search(?string $type, ?string $date)
    {
        if ($type === 'sake') {
            $this->event = $this->event->sake();
        } elseif ($type === 'beer') {
            $this->event = $this->event->beer();
        }

        if (is_null($date)) {
            return $this->event->current();
        }

        //終日の場合、ended_atは翌日00:00:00
        return $this->event->where('ended_at', '>', Carbon::parse($date));
    }
}
