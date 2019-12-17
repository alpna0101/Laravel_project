@if (!Auth::check()) 

<style type="text/css">
    
.mobile-header li {

    width: 19% !important;

}
</style>


@endif

<div class="header-search" id="search-section">
    <form method="post" action="{{route('search-all')}}" id="userSearch_min">
        <div class="form-group no-margin pull-left width-95">
            <input type="text" id="auto_complete_search_min" name="key" class="auto_complete_search search-query form-control" required placeholder="Search">
        </div>
    </form>


    <a href="#" id="close-btn"><i class="fa fa-close"></i></a>    
    <div class="clear-both"></div>
</div>

<div class="streamtube-nav" id="header-section">
    <div class="row">
        <div class="col-lg-2 col-md-3 col-sm-3 col-xs-6">

            <!-- <a href="#" class="hidden-xs"><i class="fa fa-bars toggle-icon" aria-hidden="true"></i></a> -->
            <a href="#" class="hidden-xs"><img src="{{asset('images/menu.png')}}" class="toggle-icon"></a>
            <a href="{{route('user.dashboard')}}"> 
                @if(Setting::get('site_logo'))
                    <img src="{{Setting::get('site_logo')}}" class="logo-img">
                @else
                    <img src="{{asset('logo.png')}}" class="logo-img">
                @endif
            </a>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6 visible-xs hidden-sm hidden-md hidden-lg">
            
            @if(Auth::check())
                
                <div class="y-button profile-button" style="position: unset;">
                   
                   <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent;">

                            @if(Auth::user()->picture != "")
                                <img class="profile-image" src="{{Auth::user()->picture}}">
                            @else
                                <img class="profile-image" src="{{asset('placeholder.png')}}">
                            @endif

                        </button>

                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                            <a href="{{route('user.profile')}}">
                                <div class="display-inline">
                                    <div class="menu-profile-left">
                                        <img src="{{Auth::user()->picture}}">
                                    </div>
                                    <div class="menu-profile-right">
                                        <h4>user</h4>
                                        <p>user@gmail.com</p>
                                    </div>
                                </div>
                            </a>
                            <li role="separator" class="divider"></li>
                            <div class="row">
                                <div class="col-xs-6">
                                    <a href="/settings" class="menu-link"><i class="fa fa-cog"></i>settings</a>
                                </div>
                                <div class="col-xs-6">
                                    <a href="{{route('user.logout')}}" class="menu-link"><i class="fa fa-sign-out"></i>logout</a>
                                </div>
                            </div>
                            <!-- <li><a href="{{route('user.profile')}}">{{tr('profile')}}</a></li>

                            <li><a href="{{route('user.wishlist')}}">{{tr('wishlist')}}</a></li>

                            <li><a href="{{route('user.history')}}">{{tr('history')}}</a></li>

                            <li><a href="{{route('user.channels.subscribed')}}">{{tr('subscribed_channels')}}</a></li>

                            @if(Setting::get('is_spam')) 
                            <li><a href="{{route('user.spam-videos')}}">{{tr('spam_videos')}}</a></li>
                            @endif

                            <li role="separator" class="divider"></li>

                            @if(Setting::get('payment_type') == 'stripe')

                            <li><a href="{{route('user.card.card_details')}}">{{tr('cards')}}</a></li>

                            @endif


                            <li><a href="{{route('user.redeems')}}">{{tr('redeems')}}</a></li>

                            <li role="separator" class="divider"></li>

                            <li><a href="{{route('user.subscription.history')}}">{{tr('subscription_history')}}</a></li>

                            <li><a href="{{route('user.ppv.history')}}">{{tr('ppv_history')}}</a></li>

                             <li><a href="{{route('user.live.history')}}">{{tr('live_history')}}</a></li>


                            
                            <li role="separator" class="divider"></li>

                            @if (Auth::user()->login_by == 'manual') 
                                <li role="separator" class="divider"></li>
                                
                                <li><a href="{{route('user.change.password')}}">{{tr('change_password')}}</a></li>
                            @endif

                            <li><a href="{{route('user.delete.account')}}" @if(Auth::user()->login_by != 'manual') onclick="return confirm('Are you sure? . Once you deleted account, you will lose your history and wishlist details.')" @endif>{{tr('delete_account')}}</a></li>

                            <li role="separator" class="divider"></li>
                            
                            <li><a href="{{route('user.logout')}}">{{tr('logout')}}</a></li> -->
                        </ul>
                    
                    </div>

                </div><!--y-button end-->

                <!-- Version 3.1 feature -->

                @if(Setting::get('is_direct_upload_button') == YES)

                    <a href="{{userChannelId()}}" class="btn pull-right" style="margin-right: 10px;color: red;background:white;box-shadow: none;" title="{{tr('upload_video')}}">
                        <i class="fa fa-upload fa-1x"></i>
                    </a>

                @endif

            @endif

            <section id="language-section">

            <ul class="nav navbar-nav pull-right" style="margin: 3.5px 0px">

                @if(Setting::get('admin_language_control'))

                    @if(count($languages = getActiveLanguages()) > 1) 
                       
                        <li  class="dropdown">
                    
                            <a href="#" class="dropdown-toggle language-icon" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-globe"></i> <b class="caret"></b></a>

                            <ul class="dropdown-menu languages1">

                                @foreach($languages as $h => $language)

                                    <li class="{{(\Session::get('locale') == $language->folder_name) ? 'active' : ''}}" ><a href="{{route('user_session_language', $language->folder_name)}}" style="{{(\Session::get('locale') == $language->folder_name) ? 'background-color: #cc181e' : ''}}">{{$language->folder_name}}</a></li>
                                @endforeach
                                
                            </ul>
                         
                        </li>
                
                    @endif

                @endif

            </ul>

            </section>

            @if(!Auth::check())

            <div class="y-button pull-right" style="position: unset;">
                <a href="{{route('user.login.form')}}" class="y-signin" style="margin-left: 0px;" title="{tr('login')}}"><i class="fa fa-sign-in"></i></a>
            </div><!--y-button end-->

                @if(Setting::get('is_direct_upload_button') == YES)

                    <a href="{{route('user.login.form')}}" class="btn pull-right" style="margin-right: 10px;color: red;background:white;box-shadow: none;" title="{{tr('upload_video')}}">
                        <i class="fa fa-upload fa-1x"></i>
                    </a>

                @endif

            @endif
            
            <span class="search-cls pull-right" id="search-btn"><i class="fa fa-search top5"></i></span>
            
            <div class="clearfix"></div>

        </div>


        <div class="col-lg-7 col-md-7 col-sm-6 col-xs-12 hidden-xs">

            <div id="custom-search-input" class="">
                <form method="post" action="{{route('search-all')}}" id="userSearch">
                <div class="input-group search-input">
                    
                        <input type="text" id="auto_complete_search" name="key" class="search-query form-control" required placeholder="{{tr('search')}}" />
                        <div class="input-group-btn">
                            <button class="btn btn-danger" type="submit">
                            <i class=" glyphicon glyphicon-search"></i>
                            </button>
                        </div>
                    
                </div>
                </form>
            </div><!--custom-search-input end-->

        </div>

        <!-- ========RESPONSIVE  HEADER VISIBLE IN MOBAILE VIEW====== -->
        <div class="col-xs-12 visible-xs">
            <ul class="mobile-header">
                <li><a href="{{route('user.dashboard')}}" class="mobile-menu">
                    <i class="material-icons">home</i> 
                    <span class="hidden-xxxs">{{tr('home')}}</span>
                </a></li>
                <li><a href="{{route('user.trending')}}" class="mobile-menu">
                    <i class="material-icons">whatshot</i>
                    <span class="hidden-xxxs">{{tr('trending')}}</span>
                </a></li>
                <li><a href="{{route('user.channel.list')}}" class="mobile-menu">
                    <i class="material-icons">live_tv</i>
                    <span class="hidden-xxxs">{{tr('channels')}}</span>
                </a></li>
                <li><a href="{{route('user.channel.list')}}" class="mobile-menu">
                    <i class="material-icons">movie</i>
                    <span class="hidden-xxxs">{{tr('custom_live_videos')}}</span>
                </a></li>
                <li><a href="{{route('user.live_videos')}}" class="mobile-menu">
                    <i class="material-icons">videocam</i>
                    <span class="hidden-xxxs">Live videos</span>
                </a></li>

                @if(Auth::check())

                    @if(Setting::get('create_channel_by_user') == CREATE_CHANNEL_BY_USER_ENABLED || Auth::user()->is_master_user == 1)

                        <li><a href="{{route('user.channel.mychannel')}}" class="mobile-menu">
                            <i class="material-icons">subscriptions</i>
                            <span class="hidden-xxxs">{{tr('my_channels')}}</span>
                        </a></li>
                    @else

                        <li>
                            <a href="{{route('user.history')}}" class="mobile-menu">
                                <i class="material-icons">history</i>
                                <span class="hidden-xxxs">{{tr('history')}}</span>
                            </a>
                        </li>
                    @endif
                @endif

            </ul>
        </div>


        <div class="col-lg-3 col-md-2 col-sm-3 col-xs-12 hidden-xs visible-sm visible-md visible-lg">
            @if(Auth::check())


                <div class="y-button profile-button">
                   <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            @if(Auth::user()->picture != "")
                                <img class="profile-image" src="{{Auth::user()->picture}}">
                            @else
                                <img class="profile-image" src="{{asset('placeholder.png')}}">
                            @endif
                            
                        </button>
                        
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                            <a href="{{route('user.profile')}}">
                                <div class="display-inline">
                                    <div class="menu-profile-left">
                                        <img src="{{Auth::user()->picture}}">
                                    </div>
                                    <div class="menu-profile-right">
                                        <h4>{{Auth::user()->name}}</h4>
                                        <p>{{Auth::user()->email}}</p>
                                    </div>
                                </div>
                            </a>
                            <li role="separator" class="divider"></li>
                            <div class="row">
                                <div class="col-xs-6">
                                    <a href="/settings" class="menu-link"><i class="fa fa-cog"></i>{{tr('settings')}}</a>
                                </div>
                                <div class="col-xs-6">
                                    <a href="{{route('user.logout')}}" onclick="return confirm('{{tr("logout_confirmation")}}')" class="menu-link"><i class="fa fa-sign-out"></i>{{tr('logout')}}</a>
                                </div>
                            </div>
                            <!-- <li><a href="{{route('user.profile')}}">{{tr('profile')}}</a></li>

                            <li><a href="{{route('user.wishlist')}}">{{tr('wishlist')}}</a></li>

                            <li><a href="{{route('user.history')}}">{{tr('history')}}</a></li>

                        
                            <li><a href="{{route('user.channels.subscribed')}}">{{tr('subscribed_channels')}}</a></li>
                            

                            @if(Setting::get('is_spam')) 
                                <li><a href="{{route('user.spam-videos')}}">{{tr('spam_videos')}}</a></li>
                            @endif

                            <li role="separator" class="divider"></li>
                            
                            @if(Setting::get('payment_type') == 'stripe')
                            
                            <li><a href="{{route('user.card.card_details')}}">{{tr('cards')}}</a></li>

                            @endif

                            <li><a href="{{route('user.redeems')}}">{{tr('redeems')}}</a></li>

                            <li role="separator" class="divider"></li>
                            
                            <li>
                                <a href="{{route('user.subscription.history')}}">{{tr('subscription_history')}}</a>
                            </li>

                            @if(Setting::get('is_payper_view')) 

                                <li><a href="{{route('user.ppv.history')}}">{{tr('ppv_history')}}</a></li>

                            @endif

                            <li><a href="{{route('user.live.history')}}">{{tr('live_history')}}</a></li>

                            <li role="separator" class="divider"></li>

                            @if (Auth::user()->login_by == 'manual') 
                                <li><a href="{{route('user.change.password')}}">{{tr('change_password')}}</a></li>
                            @endif

                            <li><a href="{{route('user.delete.account')}}" @if(Auth::user()->login_by != 'manual') onclick="return confirm('Are you sure? . Once you deleted account, you will lose your history and wishlist details.')" @endif>{{tr('delete_account')}}</a></li>

                            <li role="separator" class="divider"></li>
                           
                            <li><a href="{{route('user.logout')}}">{{tr('logout')}}</a></li> -->

                        </ul>
                    </div>
                </div><!--y-button end-->

                <a href="javascript:void(0);" class="btn btn-outline-success pull-right points-btn"> 
                    {{Auth::user()->total_points}} {{tr('points')}}
                </a>

                @if(Setting::get('is_direct_upload_button') == YES)

                <a href="{{userChannelId()}}" class="btn pull-right user-upload-btn" title="{{tr('upload_video')}}">
                     {{tr('upload')}} 
                    <i class="fa fa-upload fa-1x"></i>
                </a>

                @endif

            @else
                <div class="y-button">
                    <a href="{{route('user.login.form')}}" class="y-signin">{{tr('login')}}</a>
                </div><!--y-button end-->

                @if(Setting::get('is_direct_upload_button') == YES)

                    <a href="{{route('user.login.form')}}" class="btn pull-right user-upload-btn" title="{{tr('upload_video')}}"> 
                        {{tr('upload')}} 
                        <i class="fa fa-upload fa-1x"></i>
                    </a>

                @endif

            @endif


            <ul class="nav navbar-nav pull-right" style="margin: 3.5px 0px">

                @if(Setting::get('admin_language_control'))
                    
                    @if(count($languages = getActiveLanguages()) > 1) 
                       
                        <li  class="dropdown">
                    
                            <a href="#" class="dropdown-toggle language-icon" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-globe"></i> <b class="caret"></b></a>

                            <ul class="dropdown-menu languages">

                                @foreach($languages as $h => $language)

                                    <li class="{{(\Session::get('locale') == $language->folder_name) ? 'active' : ''}}" ><a href="{{route('user_session_language', $language->folder_name)}}" style="{{(\Session::get('locale') == $language->folder_name) ? 'background-color: #cc181e' : ''}}">{{$language->folder_name}}</a></li>
                                @endforeach
                                
                            </ul>
                         
                        </li>
                
                    @endif

                @endif

            </ul>




        </div>

    </div><!--end of row-->
</div><!--end of streamtube-nav-->
