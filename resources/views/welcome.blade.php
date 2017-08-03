@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <h1 class="page-header">sakeEvent - {{config('sake.area_jp')[$area ?? '']}}</h1>
        <div>
          <ul>
            <li><a href="http://nihonshucalendar.com/">日本酒カレンダー</a>のUIがいろいろキツイので突貫で作った。1時間に1回データ同期してます。</li>
            <li>特に許可とかとってないんで怒られたらやめます。</li>
            <li>そのうち<a href="http://craftbeer-tokyo.info/category/event/">クラフトビール東京</a>あたりの情報もfetchしたいところ。</li>
            <li>苦情・要望は<a href="https://twitter.com/achel_b8">@achel_b8</a>まで。</li>
          </ul>
        </div>
      </div>
    </div>
    <!-- event -->
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading">
            <!-- search -->
            <form class="form-inline">
              <label class="sr-only" for="date">date</label>
              <div class="input-group @if($errors->first('date')) has-error @endif">
                <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                <input type="date" id="date" name="date" class="form-control"
                       value="{{old('date', \Carbon\Carbon::today()->format('Y-m-d'))}}">
              </div>
              <button type="submit" class="btn btn-primary">日付で検索</button>
            </form>
          </div>
          <!-- list -->
          <div class="panel-body">
            <div class="infinite-scroll">
              @foreach($events as $event)
                <h3 class="text-danger">{{$event->date}}</h3>
                <span class="label label-danger">{{$event->prefecture->name}}</span>
                @if($event->is_recommended)
                  <span class="label label-primary">オススメ</span>
                @endif
                <h4><a href="http://nihonshucalendar.com/show_event.php?id={{$event->code}}">{{$event->summary}}</a>

                </h4>
                <h4>
                  <i class="fa fa-fw fa-map-marker"></i>
                  {{$event->location}}
                  <a class="btn btn-sm btn-info"
                     href="https://maps.google.co.jp/maps/search/{{$event->location}}">Map</a>
                </h4>
                <p>
                  {{$event->shortDescription}}
                </p>
                <hr>
              @endforeach
              {{$events->appends(['date' => $date ?? null])->links()}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-infinitescroll/2.1.0/jquery.infinitescroll.min.js"></script>
<script>
  $('.infinite-scroll').infinitescroll({
    loading: {
      finished: function () {
        $('ul.pagination').hide();
      },
      finishedMsg: '<div class="end-msg">Congratulations!</div>',
      msgText: '<div class="center">Loading...</div>'
    },
    navSelector: '.pagination',
    nextSelector: '.pagination li.active + li a',
    itemSelector: 'div.infinite-scroll'
  });
</script>
@endpush