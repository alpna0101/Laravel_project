<div class="y-menu col-sm-3 col-md-2 scroll responsive_headers">
    <ul class="y-home menu1">
        <li id="home">
            <a href="{{route('user.dashboard')}}">
                <img src="{{asset('images/home-grey.png')}}" class="grey-img">
                <img src="{{asset('images/home-red.png')}}" class="red-img">
                <span>{{tr('home')}}</span>
            </a>
        </li>
        <li id="trending">
            <a href="{{route('user.trending')}}">
                <img src="{{asset('images/trending-grey.png')}}" class="grey-img">
                <img src="{{asset('images/trending-red.png')}}" class="red-img">
                <span>{{tr('trending')}}</span>
            </a>
        </li>
              <li id="training">
            <a href="{{route('user.training')}}">
                <img src="{{asset('images/training.png')}}" class="grey-img">
                <img src="{{asset('images/training.png')}}" class="red-img">
                <span>Training</span>
            </a>
        </li>

         </li>
              <li id="making_money">
            <a href="{{route('user.making_money')}}">
                <img src="{{asset('images/making_money.png')}}" style="opacity: 0.6" class="grey-img">
                <img src="{{asset('images/making_money.png')}}" style="opacity: 0.6" class="red-img">
                <span>Making Money</span>
            </a>
        </li>

  <li id="toper">
            <a href="{{route('user.topplayer')}}">
                <img src="{{asset('images/user.png')}}" class="grey-img">
                <img src="{{asset('images/user.png')}}" class="red-img">
                <span>Leader Board</span>
            </a>
        </li>
        <li id="marketplace">
            <a href="{{route('user.marketplace')}}">
             <img src="{{asset('images/marketplace.png')}}" class="grey-img">
                <img src="{{asset('images/marketplace.png')}}" class="red-img">
                <span>Marketplace</span>
            </a>
        </li>
       @if(Auth::check())

             <li id="chat">
              <a href="{{route('chat')}}">
               <img src="{{asset('images/c2.png')}}" class="grey-img">
                <img src="{{asset('images/c1.png')}}" class="red-img">
                    <span>Chat</span>
                </a>
            </li>
        <li id="add_product">
            <a href="{{route('user.add_product')}}">
             <img src="{{asset('images/pro.png')}}"  style="opacity: 0.6" class="grey-img">
                <img src="{{asset('images/pro.png')}}"  style="opacity: 0.6" class="red-img">
                <span>Add Product</span>
            </a>
        </li>
     
        @if(count(@$seller)>0)
         <li id="add_product">
            <a href="{{route('user.myproduct')}}">
             <img src="{{asset('images/mypro.png')}}"  style="opacity: 0.6" class="grey-img">
                <img src="{{asset('images/mypro.png')}}"  style="opacity: 0.6" class="red-img">
                <span>My Products</span>
            </a>
        </li>
          @endif
           @if(count(@$seller)>0)
          <li id="customer_order">
            <a href="{{route('user.customerorders')}}">
             <img src="{{asset('images/or.png')}}"  style="opacity: 0.6" class="grey-img">
                <img src="{{asset('images/or.png')}}"  style="opacity: 0.6" class="red-img">
                <span>Customer Order</span>
            </a>
        </li>
            @endif
        @if(count(@$buyer)>1)
            <li id="my_order">
            <a href="{{route('user.myorders')}}">
             <img src="{{asset('images/myor.png')}}"  style="opacity: 0.6" class="grey-img">
                <img src="{{asset('images/myor.png')}}"  style="opacity: 0.6" class="red-img">
                <span>My Order</span>
            </a>
        </li>
        @endif
        @endif
        <li id="ownerpage">
            <a href="{{route('user.ownerpage')}}">
             <img src="{{asset('images/owner.png')}}" style="opacity: 0.5" class="grey-img">
                <img src="{{asset('images/owner.png')}}" style="opacity: 0.5" class="red-img">
                <span>Owners</span>
            </a>
        </li>
      
        <li id="custom_live_videos">
            <a href="{{route('user.custom.live.index')}}">
                <img src="{{asset('images/video-camera1.png')}}" class="grey-img">
                <img src="{{asset('images/video-camera-red.png')}}" class="red-img">
                <span>{{tr('custom_live_videos')}}</span>
            </a>
        </li>
          @if(Auth::check())
        @if(Setting::get('broadcast_by_user') == 1 || Auth::user()->is_master_user == 1)
         <li id="go_live_videos">
        <a data-target="#start_broadcast1" data-toggle="modal" style="cursor:pointer;">
          <img src="{{asset('images/go-live_gray.png')}}" class="grey-img">
                <img src="{{asset('images/go-live_yellow.png')}}" class="red-img">
    <!-- <button class="st_video_upload_btn text-uppercase"  > -->
     <!--    <i class="fa fa-video-camera" style="font-size: 35px;"></i>  -->
        {{tr('go_live')}}
    <!-- </button> -->
    </a>
</li>
@endif
@endif
        <li id="live_videos">
            <a href="{{route('user.live_videos')}}">
                <img src="{{asset('images/video-grey.png')}}" class="grey-img">
                <img src="{{asset('images/video-red.png')}}" class="red-img">
                <span>{{tr('live_videos')}} @if(@$livevideo>0)<span class="circle_blink red_blink"></span> @endif</span>
            </a>
        </li>

        </li>

        <li id="channels">
            <a href="{{route('user.channel.list')}}">
                <img src="{{asset('images/search-grey.png')}}" class="grey-img">
                <img src="{{asset('images/search-red.png')}}" class="red-img">
                <span>{{tr('browse_channels')}}</span>
            </a>
        </li>

        @if(Auth::check())
          
   
            <li id="history">
                <a href="{{route('user.history')}}">
                    <img src="{{asset('images/history-grey.png')}}" class="grey-img">
                    <img src="{{asset('images/history-red.png')}}" class="red-img">
                    <span>{{tr('history')}}</span>
                </a>
            </li>
            <li id="settings">
                <a href="{{url('/settings')}}">
                    <img src="{{asset('images/settings-grey.png')}}" class="grey-img">
                    <img src="{{asset('images/settings-red.png')}}" class="red-img">
                    <span>{{tr('settings')}}</span>
                </a>
            </li>
            <li id="wishlist">
                <a href="{{route('user.wishlist')}}">
                    <img src="{{asset('images/heart-grey.png')}}" class="grey-img">
                    <img src="{{asset('images/heart-red.png')}}" class="red-img">
                    <span>{{tr('wishlist')}}</span>
                </a>
            </li>
            @if(Setting::get('create_channel_by_user') == CREATE_CHANNEL_BY_USER_ENABLED || Auth::user()->is_master_user == 1)
                <li id="my_channel">
                    <a href="{{route('user.channel.mychannel')}}">
                        <img src="{{asset('images/channel-grey.png')}}" class="grey-img">
                        <img src="{{asset('images/channel-red.png')}}" class="red-img">
                        <span>{{tr('my_channels')}}</span>
                    </a>
                </li>

            @endif
        @endif
    </ul>
                
    @if(count($channels = loadChannels()) > 0)
        
        <ul class="y-home menu1" style="margin-top: 10px;">

            <h3>{{tr('channels')}}</h3>

            @foreach($channels as $channel)
                <li id="channels_{{$channel->id}}">
                    <a href="{{route('user.channel',$channel->id)}}"><img src="{{$channel->picture}}">{{$channel->name}}</a>
                </li>
            @endforeach              
        </ul>

    @endif

    <!-- ============PLAY STORE, APP STORE AND SHARE LINKS======= -->

    @if(Setting::get('appstore') || Setting::get('playstore'))

        <ul class="menu-foot" style="margin-top: 10px;">

            <h3>{{tr('download_our_app')}}</h3>

            @if(Setting::get('playstore'))

            <li>
                <a href="{{Setting::get('playstore')}}" target="_blank">
                    <img src="{{asset('images/google-play.png')}}">
                </a>
            </li>

            @endif

            @if(Setting::get('appstore'))

            <li>
                <a href="{{Setting::get('appstore')}}" target="_blank">
                    <img src="{{asset('images/app_store.png')}}" >
                </a>
            </li>

            @endif

        </ul>

    @endif

    @if(Setting::get('facebook_link') || Setting::get('twitter_link') || Setting::get('linkedin_link') || Setting::get('pinterest_link') || Setting::get('google_link'))

    <h3 class="menu-foot-head">{{tr('contact')}}</h3>

    <div class="nav-space">

        @if(Setting::get('facebook_link'))

        <a href="{{Setting::get('facebook_link')}}" target="_blank">
            <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x social-fb"></i>
                <i class="fa fa-facebook fa-stack-1x fa-inverse foot-share2"></i>
            </span>
        </a>

        @endif

        @if(Setting::get('twitter_link'))

        <a href="{{Setting::get('twitter_link')}}" target="_blank">
            <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x social-twitter"></i>
                <i class="fa fa-twitter fa-stack-1x fa-inverse foot-share2"></i>
            </span>
        </a>

        @endif

        @if(Setting::get('linkedin_link'))

        <a href="{{Setting::get('linkedin_link')}}" target="_blank">
            <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x social-linkedin"></i>
                <i class="fa fa fa-linkedin fa-stack-1x fa-inverse foot-share2"></i>
            </span>
        </a>

        @endif

        @if(Setting::get('pinterest_link'))

        <a href="{{Setting::get('pinterest_link')}}" target="_blank">
            <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x social-pinterest"></i>
                <i class="fa fa fa-pinterest fa-stack-1x fa-inverse foot-share2"></i>
            </span>
        </a>
        @endif

        @if(Setting::get('google_link'))
        <a href="{{Setting::get('google_link')}}" target="_blank">
            <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x social-google"></i>
                <i class="fa fa fa-google fa-stack-1x fa-inverse foot-share2"></i>
            </span>
        </a>
        @endif

    </div>

    @endif
    
    @if(Auth::check())
   


        <!-- Check the create channel options are enabled by admin -->

        <?php /*@if(Setting::get('create_channel_by_user') == CREATE_CHANNEL_BY_USER_ENABLED || Auth::user()->is_master_user == 1)

            <?php $channels = getChannels(Auth::user()->id);?>

            @if(count($channels) > 0 || Auth::user()->user_type)

                <ul class="y-home" style="margin-top: 10px;">
                   

                    <h3>{{tr('my_channels')}}</h3>


                    @foreach($channels as $channel)
                        <li>
                            <a href="{{route('user.channel',$channel->id)}}"><img src="{{$channel->picture}}">{{$channel->name}}</a>
                        </li>
                    @endforeach  


                    @if(Auth::user()->user_type || Auth::user()->is_master_user == 1)  

                        @if(count($channels) == 0 || Setting::get('multi_channel_status') || Auth::user()->is_master_user == 1)  

                        <li>
                            <a href="{{route('user.create_channel')}}"><i class="fa fa-tv fa-2x" style="vertical-align: middle;"></i> {{tr('create_channel')}}</a>
                        </li>    

                        @endif

                    @endif     
                
                </ul>

            @endif
            
        @endif */?>


        @if(!Auth::user()->user_type)

            <div class="menu4 top nav-space">
                <p>{{tr('subscribe_note')}}</p>
                <a href="{{route('user.subscriptions')}}" class="btn btn-sm btn-primary">{{tr('subscribe')}}</a>
            </div> 


        @endif

    @else
        <div class="menu4 top nav-space">
            <p>{{tr('signin_nav_content')}}</p>
            <form method="get" action="{{route('user.login.form')}}">
                <button type="submit">{{tr('login')}}</button>
            </form>
        </div>   
    @endif             
</div>


 @if(Setting::get('broadcast_by_user') == 1 || (Auth::check() ? Auth::user()->is_master_user == 1 : 0))

        <div id="start_broadcast1" class="modal fade in" role="dialog">
            <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header start_brocadcast_form">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-uppercase text-center">{{tr('start_broadcast')}}</h4>
                </div>

                <div class="modal-body body-modal">

                <form method="post" action="{{route('user.live_video.broadcast')}}">

                    <input type="hidden" name="channel_id" value="">

                    <input type="hidden" name="user_id" value="{{$channel->user_id}}">

                    <!-- Text input-->

                    <div class="form-group form-group1">
                        <input type="text" class="form-control signup-form1" placeholder="{{tr('enter_title')}}" id="title" name="title" required="">
                    </div>


                    <div class="form-group radio-btn text-left">

                        <label class="control-label col-xs-4 col-sm-3 zero-padding" for="optradio">{{tr('payment')}}</label>

                        <div class="col-xs-8 col-sm-8">

                            <label class="radio-inline width-100" for="reqType-1">
                                <input type="radio" id="reqType-1" checked="checked" class="option-input radio" name="payment_status" onchange="return $('#price').hide();" value="0">{{tr('free')}}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="reqType-0" class="option-input radio" name="payment_status" onchange="return $('#price').show()" value="1">{{tr('paid')}}
                            </label>
                        </div>
                    
                    </div>

                    <div class="clearfix"></div>

                    <!-- ======amount===== -->
                    <div class="form-group form-group1" style="display: none" id="price">
                        <input id="Amount" name="amount" type="number" placeholder="Amount" pattern="[0-9]{0,}" class="form-control signup-form1">
                    </div>
                 

                    <div class="form-group form-group1">
                        <textarea id="description1" name="description" class="form-control signup-form1" rows="5" id="comment" placeholder="{{tr('description')}}"></textarea>
                    </div>

                @if(Setting::get('broadcast_by_user') == 1 || Auth::user()->is_master_user == 1) 
                    <button class="btn btn-danger" type="submit" id="submitButton" name="submitButton">{{tr('broadcast')}}</button>
                @else

                    <button class="btn btn-danger" type="button" onclick="return alert('Broadcast option is disabled by admin.');">{{tr('broadcast')}}</button>

                @endif

              </form>

              </div>

              <div class="clearfix"></div>
            </div>

          </div>
        
        </div>

@endif