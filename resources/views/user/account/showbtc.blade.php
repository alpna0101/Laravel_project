@extends('layouts.user')

@section('content')
<div class="y-content">

    <div class="row y-content-row">

        @include('layouts.user.nav')

        <div class="page-inner col-sm-9 col-md-10 profile-edit">

            <div class="form-background p-50">
                <div class="common-form login-common">

                    @include('notification.notify')
                      <a href="{{route('user.profile.editbtc')}}"> <i class="fa fa-pencil" style="float: right;font-size: 20px;cursor:pointer;" title="Edit"></i> </a>
                    <div class="social-form">
                        <div class="signup-head">
                            <h3>Bitcoin Detail</h3>
                        </div>  
                    </div><!--end of socila-form-->
                   
                    <div class="sign-up login-page"> 
                     

                          <ul style="list-style-type: none">
                      <li><b>BTC Address:</b> <i>{{$btc->btc_address}}</i></li>   
                      <li><img src="{{asset('uploads/qr_code/')}}/{{$btc->qr_code}}" height="400" width="400"></li>

                     </ul>
                     
                    </div><!--end of sign-up-->

                </div><!--end of common-form-->     
            </div><!--end of form-background-->

            <div class="sidebar-back"></div> 
        </div>
    </div>
</div>

@endsection
