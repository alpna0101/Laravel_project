@extends('layouts.user')

@section('styles')

<style type="text/css">
    
.list-inline {
  text-align: center;
}
.list-inline > li {
  margin: 10px 5px;
  padding: 0;
}
.list-inline > li:hover {
  cursor: pointer;
}
.list-inline .selected img {
  opacity: 1;
  border-radius: 15px;
}
.list-inline img {
  opacity: 0.5;
  -webkit-transition: all .5s ease;
  transition: all .5s ease;
}
.list-inline img:hover {
  opacity: 1;
}

.item > img {
  max-width: 100%;
  height: auto;
  display: block;
}

.carousel-inner .active {

    background-color: none;
}

.carousel-inner .item {

    padding: 0px;
}

</style>
@endsection

@section('content')

    <div class="y-content">

   
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-xs-12 col-sm-9 col-md-10">

@if(count(@$sponsored->items) > 0)

<div class="slide-area token_banner multiple-items">

                    

                            @foreach($sponsored->items as $kk => $sponsor)

                            <div class="slide-box">
                                <div id="main-video-player-{{$kk}}"></div>
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                    </div><!--end of slide-area-->
       
@else

  <section class="token_banner" style="padding-top:50px;">
  <div class="container-fluid text-center">
      <h2>Limited Time Offer 

</h2>
    
      <a href="{{route('user.subscriptions')}}"><h3 class="free_coin">Subscribe And Get Free Token</h3></a>
      <h3 class="truau_game">TruAu Token</h3>
      <div class="row">
        <div class="countdown_box pull-left">
            <p> Tokens Given Away</p>
            <div class="countdown">
               
            </div>
        </div>
        <div class="countdown_box pull-right">
            <p>Tokens Left</p>
            <div class="countdown">
               
            </div>
        </div>
      </div>
  </div>
</section>
  @endif
                   
            

{{--<!-- <section class="Subscription_plan">
    <div class="container-fluid">
        <div class="row">

        @if(count(@$subscription) > 0)
                   @foreach(@$subscription as $temp)

            <div class="col-sm-4">
              <div class="subs_cardd">
                  <div class="card_bg_box">
                    <img src="https://www.cjclive.com/images/subscriptions1.png" alt="BASIC EARN REWARD POINTS ">
                      <div class="title">
                        <h3>
                            <span class="sign">$</span>
                            <span class="cash">{{$temp->amount}}</span>
                        </h3> 
                        @if($temp->amount=="0.99")
                        <h4>Get 1 TruAu Game Token</h4>
                        <h5>BASIC EARN REWARD POINTS </h5>
                        @elseif($temp->amount=="99")
                         <h4>Get 3 TruAu Game Tokens</h4>
                          <h5>Mobile App Mobile App + Basic Membership</h5>
                         @else
                          <h4>Get 5 TruAu Game Tokens</h4>
                            <h5>STREAMING PAY PER VIEW SERVICE Mobile App + Basic Membership</h5>
                          @endif
                      </div>
                  </div>

                  <div class="subs-card-detailss">      
                        <h5>Earn  Bitcoin Earn Free Rewards     Earn Monthly  Earn Yearly</h5>
                         @if($temp->amount=="0.99")
                           <p>Only&nbsp;${{$temp->amount}}&nbsp;per Month</p>
                         @elseif($temp->amount=="99")
                           <p>Only&nbsp;${{$temp->amount}}&nbsp;per Year</p>
                        @else
                          <p>Only&nbsp;${{$temp->amount}}&nbsp;per Year</p>
                        @endif
                      
                  </div>
                  <a href="#" class="subscribe-btnn">Choose Plan</a>
              </div>
            </div>
            @endforeach
            @endif
           
           
        </div>
    </div>  
</section>
 -->--}}
                @if(Setting::get('is_banner_video'))


                @if(count($banner_videos) > 0)

                <div class="row" id="slider">
                    <div class="col-md-12 banner-slider">
                        <div id="myCarousel" class="carousel slide">
                            <div class="carousel-inner">
                                @foreach($banner_videos as $key => $banner_video)
                                <div class="{{$key == 0 ? 'active item' : 'item'}}" data-slide-number="{{$key}}">
                                    <a href="{{route('user.single' , $banner_video->video_tape_id)}}"><img src="{{$banner_video->image}}" style="height:250px;width: 100%;">
                                    
                                    </a>
                                </div>
                                @endforeach
                            </div>

                            <!-- Controls-->
                           <!--  <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                                <span class="sr-only">{{tr('previous')}}</span>
                            </a>
                            <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                                <span class="sr-only">{{tr('next')}}</span>
                            </a> -->

                        </div>
                    </div>
                </div>

                @endif

                @endif

                @if(Setting::get('is_banner_ad'))

                @if(count($banner_ads) > 0)

                <div class="row" id="slider">
                    <div class="col-md-12 banner-slider">
                        <div id="myCarousel" class="carousel slide">
                            <div class="carousel-inner">
                                @foreach($banner_ads as $key => $banner_ad)

                                <div class="{{$key == 0 ? 'active item' : 'item'}}" data-slide-number="{{$key}}" style="background-image: url({{$banner_ad->image}});">
                                    <a href="{{$banner_ad->link}}" target="_blank">

                                        <div class="carousel-caption">

                                            <h3>{{$banner_ad->video_title}}</h3>

                                            <div class="clearfix"></div>

                                            <p class="hidden-xs">@if($banner_ad->content) <?= strlen($banner_ad->content) > 200 ? substr($banner_ad->content , 0 , 200).'...' :  substr($banner_ad->content , 0 , 200)?> @endif</p>
                                        </div>
                                    </a>
                                </div>

                                @endforeach
                            </div>

                            <!-- Controls-->
                            <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                                <span class="sr-only">{{tr('previous')}}</span>
                            </a>
                            <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                                <span class="sr-only">{{tr('next')}}</span>
                            </a>
                        </div>
                    </div>
                </div>

                @endif

                @endif

                @include('notification.notify')

                @if($wishlists)

                @if(count($wishlists->items) > 0)

                    <div class="slide-area">
                        <div class="box-head">
                            <h3>{{tr('wishlist')}}</h3>
                        </div>

                        <div class="box">

                            @foreach($wishlists->items as $wishlist)

                            <div class="slide-box">
                                <div class="slide-image">
                                    <a href="{{$wishlist->url}}">
                                        <!-- <img src="{{$wishlist->video_image}}" /> -->
                                        <!-- <div style="background-image: url({{$wishlist->video_image}});" class="slide-img1"></div> -->

                                        <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$wishlist->video_image}}"class="slide-img1 placeholder" />
                                    </a>

                                    @if($wishlist->ppv_amount > 0)
                                        @if(!$wishlist->ppv_status)
                                            <div class="video_amount">

                                            {{tr('pay')}} - {{Setting::get('currency')}}{{$wishlist->ppv_amount}}

                                            </div>
                                        @endif
                                    @endif
                                    <div class="video_mobile_views">
                                        {{$wishlist->watch_count}} {{tr('views')}}
                                    </div>
                                    <div class="video_duration">
                                        {{$wishlist->duration}}
                                    </div>
                                </div><!--end of slide-image-->

                                <div class="video-details">
                                    <div class="video-head">
                                        <a href="{{$wishlist->url}}">{{$wishlist->title}}</a>
                                    </div>
                                    <span class="video_views">
                                        <div><a href="{{route('user.channel',$wishlist->channel_id)}}">{{$wishlist->channel_name}}</a></div>
                                        <div class="hidden-mobile"><i class="fa fa-eye"></i> {{$wishlist->watch_count}} {{tr('views')}} <b>.</b> 
                                        {{$wishlist->created_at}}</div>
                                    </span>
                                </div><!--end of video-details-->
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                        </div><!--end of box--> 
                    </div><!--end of slide-area-->

                @endif

                @endif


                @if(count($recent_videos->items) > 0)

                <hr>
                
                    <div class="slide-area">
                        <div class="box-head">
                            <h3>{{tr('recent_videos')}}</h3>
                        </div>

                        <div class="box">

                            @foreach($recent_videos->items as $recent_video)
                            <div class="slide-box">
                                <div class="slide-image">
                                    <a href="{{$recent_video->url}}">
                                        <!-- <img src="{{$recent_video->video_image}}" /> -->
                                        <!-- <div style="background-image: url({{$recent_video->video_image}});" class="slide-img1"></div> -->
                                        <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$recent_video->video_image}}"class="slide-img1 placeholder" />
                                    </a>
                                    @if($recent_video->ppv_amount > 0)
                                        @if(!$recent_video->ppv_status)
                                            <div class="video_amount">

                                            {{tr('pay')}} - {{Setting::get('currency')}}{{$recent_video->ppv_amount}}

                                            </div>
                                        @endif
                                    @endif
                                    <div class="video_mobile_views">
                                        {{$recent_video->watch_count}} {{tr('views')}}
                                    </div>
                                    <div class="video_duration">
                                        {{$recent_video->duration}}
                                    </div>
                                </div><!--end of slide-image-->

                                <div class="video-details">
                                    <div class="video-head">
                                        <a href="{{$recent_video->url}}">{{$recent_video->title}}</a>
                                    </div>

                                    <span class="video_views">
                                        <div><a href="{{route('user.channel',$recent_video->channel_id)}}">{{$recent_video->channel_name}}</a></div>
                                        <div class="hidden-mobile"><i class="fa fa-eye"></i> {{$recent_video->watch_count}} {{tr('views')}} <b>.</b> 
                                        {{$recent_video->created_at}}</div>
                                    </span>
                                </div><!--end of video-details-->
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                        </div><!--end of box--> 
                    </div><!--end of slide-area-->

                @endif



                @if(count($trendings->items) > 0)

                <hr>

                    <div class="slide-area">
                        <div class="box-head">
                            <h3>{{tr('trending')}}</h3>
                        </div>

                        <div class="box">

                            @foreach($trendings->items as $trending)

                            <div class="slide-box">
                                <div class="slide-image">
                                    <a href="{{$trending->url}}">
                                        <!-- <img src="{{$trending->video_image}}" /> -->
                                        <!-- <div style="background-image: url({{$trending->video_image}});" class="slide-img1"></div> -->
                                        <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$trending->video_image}}" class="slide-img1 placeholder" />
                                    </a>
                                    @if($trending->ppv_amount > 0)
                                        @if(!$trending->ppv_status)
                                            <div class="video_amount">

                                            {{tr('pay')}} - {{Setting::get('currency')}}{{$trending->ppv_amount}}

                                            </div>
                                        @endif
                                    @endif
                                    <div class="video_mobile_views">
                                        {{$trending->watch_count}} {{tr('views')}}
                                    </div>
                                    <div class="video_duration">
                                        {{$trending->duration}}
                                    </div>
                                </div><!--end of slide-image-->

                                <div class="video-details">
                                    <div class="video-head">
                                        <a href="{{$trending->url}}">{{$trending->title}}</a>
                                    </div>
                                    
                                    <span class="video_views">
                                        <div><a href="{{route('user.channel',$trending->channel_id)}}">{{$trending->channel_name}}</a></div>
                                        <div class="hidden-mobile"><i class="fa fa-eye"></i> {{$trending->watch_count}} {{tr('views')}} <b>.</b> 
                                        {{$trending->created_at}}</div>
                                    </span>
                                </div><!--end of video-details-->
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                        </div><!--end of box--> 
                    </div><!--end of slide-area-->

                @endif

                @if(count($suggestions->items) > 0)

                <hr>

                    <div class="slide-area">
                        <div class="box-head">
                            <h3>{{tr('suggestions')}}</h3>
                        </div>

                        <div class="box">

                            @foreach($suggestions->items as $suggestion)
                            <div class="slide-box">
                                <div class="slide-image">
                                    <a href="{{$suggestion->url}}">
                                        <!-- <img src="{{$suggestion->video_image}}" /> -->
                                       <!--  <div style="background-image: url({{$suggestion->video_image}});" class="slide-img1"></div> -->
                                       <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$suggestion->video_image}}" class="slide-img1 placeholder" />
                                    </a>

                                    @if($suggestion->ppv_amount > 0)
                                        @if(!$suggestion->ppv_status)
                                            <div class="video_amount">

                                            {{tr('pay')}} - {{Setting::get('currency')}}{{$suggestion->ppv_amount}}

                                            </div>
                                        @endif
                                    @endif
                                    <div class="video_mobile_views">
                                        {{$suggestion->watch_count}} {{tr('views')}}
                                    </div>
                                    <div class="video_duration">
                                        {{$suggestion->duration}}
                                    </div>
                                </div><!--end of slide-image-->

                                <div class="video-details">
                                    <div class="video-head">
                                        <a href="{{$suggestion->url}}">{{$suggestion->title}}</a>
                                    </div>
                                   
                                    <span class="video_views">
                                        <div><a href="{{route('user.channel',$suggestion->channel_id)}}">{{$suggestion->channel_name}}</a></div>
                                        <div class="hidden-mobile"><i class="fa fa-eye"></i> {{$suggestion->watch_count}} {{tr('views')}} <b>.</b> 
                                        {{$suggestion->created_at}}</div>
                                    </span>
                                </div><!--end of video-details-->
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                        </div><!--end of box--> 
                    </div><!--end of slide-area-->

                @endif

                @if($watch_lists)

                @if(count($watch_lists->items) > 0)

                  <hr>


                  <div class="main">
  <div class="slider slider-for">
    <div><h3>1</h3></div>
    <div><h3>2</h3></div>
    <div><h3>3</h3></div>
    <div><h3>4</h3></div>
    <div><h3>5</h3></div>
  </div>
  <div class="slider slider-nav">
    <div><h3>1</h3></div>
    <div><h3>2</h3></div>
    <div><h3>3</h3></div>
    <div><h3>4</h3></div>
    <div><h3>5</h3></div>
  </div>
  
</div>

                    <div class="slide-area">
                        <div class="box-head">
                            <h3>{{tr('watch_lists')}}</h3>
                        </div>

                        <div class="box">

                            @foreach($watch_lists->items as $watch_list)

                            <div class="slide-box">
                                <div class="slide-image">
                                    <a href="{{$watch_list->url}}">
                                        <!-- <img src="{{$watch_list->video_image}}" /> -->
                                        <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$watch_list->video_image}}" class="slide-img1 placeholder" />
                                    </a>
                                    @if($watch_list->ppv_amount > 0)
                                        @if(!$watch_list->ppv_status)
                                            <div class="video_amount">

                                            {{tr('pay')}} - {{Setting::get('currency')}}{{$watch_list->ppv_amount}}

                                            </div>
                                        @endif
                                    @endif
                                    <div class="video_mobile_views">
                                        {{$watch_list->watch_count}} {{tr('views')}}
                                    </div>
                                    <div class="video_duration">
                                        {{$watch_list->duration}}
                                    </div>
                                </div><!--end of slide-image-->

                                <div class="video-details">
                                    <div class="video-head">
                                        <a href="{{$watch_list->url}}">{{$watch_list->title}}</a>
                                    </div>
                                    <span class="video_views">
                                        <div><a href="{{route('user.channel',$watch_list->channel_id)}}">{{$watch_list->channel_name}}</a></div>
                                        <div class="hidden-mobile"><i class="fa fa-eye"></i> {{$watch_list->watch_count}} {{tr('views')}} <b>.</b> 
                                        {{$watch_list->created_at}}</div>
                                    </span> 
                                </div><!--end of video-details-->
                            </div><!--end of slide-box-->
                            @endforeach
                   
                              
                        </div><!--end of box--> 
                    </div><!--end of slide-area-->

                @endif

                @endif
             
                <div class="sidebar-back"></div>  
            </div>

        </div>
    </div>

@endsection

@section('scripts')

<script type="text/javascript" src="{{asset('assets/js/star-rating.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/toast.script.js')}}"></script>
<script src="{{asset('jwplayer/jwplayer.js')}}"></script>

<?php 

if(count(@$sponsored->items) > 0) {
foreach($sponsored->items as $keyy => $sItem) {
  ?>

<script type="text/javascript">
   jwplayer.key="{{Setting::get('JWPLAYER_KEY')}}";
 var playerInstance = jwplayer("main-video-player-<?php echo $keyy;?>");  
   var path = [];
    @if(@$videoStreamUrl) 
   
           path.push({file : "{{$sItem->videoStreamUrl}}", label : "Original"});
   
           path.push({file : "{{$sItem->video_link}}", label : "Original"});
   
       @else
   
           if(jQuery.browser.mobile) {
   
               $('#mainVideo').show();
   
               // console.log('You are using a mobile device!');
   
               path.push({file : "{{$sItem->hls_video}}", label : "Original"});
   
           } else {
   
            @if(count($sItem->videoPath) > 0 && $sItem->videoPath != '')
   
               @foreach($sItem->videoPath as $path)
   
                   path.push({file : "{{$path->file}}", label : "{{$path->label}}"});
   
               @endforeach
   
               @endif
   
           }
   
       @endif
 playerInstance.setup({
   
           sources: path,
           image: "{{$sItem->video_image}}",
           title: "{{$sItem->title}}",
           description:"",
           width: "100%",
           aspectratio: "16:9",
           primary: "flash",
           controls : true,
           "controlbar.idlehide" : false,
           controlBarMode:'floating',
           "controls": {
           "enableFullscreen": false,
           "enablePlay": false,
           "enablePause": false,
           "enableMute": true,
           "enableVolume": true
           },
           autostart : false,
           "sharing": {
            "sites": ["facebook","twitter"]
           },
           events : {
   
               onReady : function(event) {
   
                   console.log("onready");
   
               },

   
               onTime:function(event) {
   
                   // Between Ad Play
   
                   var video_time = Math.round(playerInstance.getPosition());
   
              
               },
   
               onBeforePlay : function(event) {
   
               },
               onPlay : function(event) {
                  setTimeout(function() {
                     $('.jw-title').addClass('hide');
                  }, 1000);
                  console.info('onPlay');
                   // between_ad_status = 0;
   
               },
               onPause : function(event) {
                  console.info('onPause');
                  $('.jw-title').removeClass('hide');
                   // between_ad_status = 0;
   
               },
   
               onComplete : function(event) {
   
                   console.log("onComplete Fn");

                  
   
   
                   // For post ad, once video completed the ad will execute
   
   
               }
   
           },
   
          
   
       });
        jQuery("#main-video-player-<?php echo $keyy;?>").show();
</script>
<?php } } ?> ;?>

<script type="text/javascript">
$(document).ready(function(){  
    console.log("#cookieConsent"); 
    setTimeout(function () {
        $("#cookieConsent").fadeIn(200);
     }, 4000);
    $("#closeCookieConsent, .cookieConsentOK").click(function() {
        $("#cookieConsent").fadeOut(200);
    }); 
}); 

var countt = $(".item").length;
$(".carousel-control").click(function() {

  for (var i = 0; i < countt; i++) {
    jwplayer(i).stop();
  }

})
$('#myCarousel').carousel({
    interval: false
});

// This event fires immediately when the slide instance method is invoked.
$('#myCarousel').on('slide.bs.carousel', function (e) {
    var id = $('.item.active').data('slide-number');
        
    // Added a statement to make sure the carousel loops correct
        if(e.direction == 'right'){
        id = parseInt(id) - 1;  
      if(id == -1) id = 7;
    } else{
        id = parseInt(id) + 1;
        if(id == $('[id^=carousel-thumb-]').length) id = 0;
    }
  
    $('[id^=carousel-thumb-]').removeClass('selected');
    $('[id=carousel-thumb-' + id + ']').addClass('selected');
});

// Thumb control
$('[id^=carousel-thumb-]').click( function(){
  var id_selector = $(this).attr("id");
  var id = id_selector.substr(id_selector.length -1);
  id = parseInt(id);
  $('#myCarousel').carousel(id);
  $('[id^=carousel-thumb-]').removeClass('selected');
  $(this).addClass('selected');
});


</script>

<script>
$('.multiple-items').slick({
  dots: false,
  slidesToShow: 3,
  slidesToScroll: 3,
  infinite: false,
    responsive: [
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 3,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 2
      }
    }
    ]
});
    
</script>
@endsection