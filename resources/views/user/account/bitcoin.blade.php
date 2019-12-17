@extends('layouts.user')

@section('content')
<div class="y-content">

    <div class="row y-content-row">

        @include('layouts.user.nav')

        <div class="page-inner col-sm-9 col-md-10 profile-edit">

            <div class="form-background p-50">
                <div class="common-form login-common">

                    @include('notification.notify')

                    <div class="social-form">
                        <div class="signup-head">
                            <h3>Bitcoin Detail</h3>
                        </div><!--end  of signup-head-->        
                    </div><!--end of socila-form-->

                    <div class="sign-up login-page"> 
                        @if(Setting::get('admin_delete_control') == 1)

                        <form class="signup-form login-form" method="post" action="#">

                        @else

                        <form class="signup-form login-form" method="post" action="{{ route('user.profile.btc_save') }}" enctype="multipart/form-data">

                        @endif

                            <div class="form-group">
                                <label for="old_password">BTC Address</label>
                                <input type="text" required name="btc_address" class="form-control" id="btc_address" placeholder="Enter BTC Address" value="{{@$btc->btc_address}}">
                                <input type="hidden"  name="id" class="form-control" id="id"  value="{{@$btc->id}}">
                              </div>

                            <div class="form-group">
                                <label for="new_password">QR CODE</label>
                               
                                @if(@$btc)<input type="file"  name="qr_code" class="form-control" id="qr_code" placeholder="QR Code"> 
                                <img src="{{asset('uploads/qr_code/')}}/{{$btc->qr_code}}" height="100" width="100" >
                                @else
                                 <input type="file" required name="qr_code" class="form-control" id="qr_code" placeholder="QR Code">
                                @endif
                            </div>

                          

                            <div class="change-pwd">

                                

                                    <button type="submit" class="btn btn-info">{{tr('submit')}}</button>
                                  

                            </div>  
                                        
                        </form>
                    </div><!--end of sign-up-->

                </div><!--end of common-form-->     
            </div><!--end of form-background-->

            <div class="sidebar-back"></div> 
        </div>
    </div>
</div>

@endsection
