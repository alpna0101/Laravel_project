@extends('layouts.user')

@section('content')

<div class="y-content">
    <div class="row content-row">

        @include('layouts.user.nav')

        <div class="history-content page-inner col-sm-9 col-md-10">
            
            @include('notification.notify')

            <div class="new-history">
                <div class="content-head">
                    <div><h4 class="no-margin-top">{{tr('pay_per_videos')}} ( ${{user_total_amount()}} )</h4></div>              
                </div><!--end of content-head-->

                @if(count($model->items) > 0)

                    <ul class="history-list">

                        @foreach($model->items as $i => $video)

                            <li class="sub-list row">
                                <div class="main-history">
                                     <div class="history-image">
                                        <a href="{{$video->url}}"><img src="{{$video->video_image}}"></a>
                                        @if($video->ppv_amount > 0)
                                            @if(!$video->ppv_status)
                                                <div class="video_amount">

                                                {{tr('pay')}} - {{Setting::get('currency')}}{{$video->ppv_amount}}

                                                </div>
                                            @endif
                                        @endif
                                        <div class="video_duration">
                                            {{$video->duration}}
                                        </div>
                                    </div><!--history-image-->

                                    <div class="history-title">
                                        <div class="history-head row">
                                            <div class="cross-title1">
                                                <h5><a href="{{$video->url}}">{{$video->title}} ({{tr('paid')}} - ${{$video->paid_amount}})</a></h5>
                                                <span class="video_views">
                                                    <div><a href="{{route('user.channel',$video->channel_id)}}">{{$video->channel_name}}</a></div>
                                                    <i class="fa fa-eye"></i> {{$video->watch_count}} {{tr('views')}} 
                                                    <b>.</b> 
                                                    {{$video->created_at}}
                                                </span>
                                            </div> 
                                                                 
                                        </div> <!--end of history-head--> 

                                        <div class="description">
                                            <p>{{$video->description}}</p>
                                        </div><!--end of description--> 

                                        <span class="stars">
                                           <a><i @if($video->ratings >= 1) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                           <a><i @if($video->ratings >= 2) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                           <a><i @if($video->ratings >= 3) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                           <a><i @if($video->ratings >= 4) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                           <a><i @if($video->ratings >= 5) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                        </span>      

                                    </div><!--end of history-title--> 
                                    
                                </div><!--end of main-history-->
                            </li> 
                        @endforeach
                       
                    </ul>

                @else 

                    <p>{{tr('no_payper_found')}}</p>

                @endif

                @if(count($model->items) > 0)

                    @if($model->pagination)
                    <div class="row">
                        <div class="col-md-12">
                            <div align="center" id="paglink"><?php echo $model->pagination; ?></div>
                        </div>
                    </div>
                    @endif
                @endif
                
            </div>
        
        </div>

    </div>
</div>

@endsection
