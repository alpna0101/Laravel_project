@extends('layouts.user.focused')

@section('styles')

<link rel="stylesheet" href="{{asset('admin-css/plugins/datepicker/datepicker3.css')}}">
<style type="text/css">
    .error {
    font-size: 14px !important;
    color: red;
    margin-bottom: 0px !important;
    float: none;
}
</style>
@endsection

@section('content')

<div class="login-space">
        <div class="common-form login-common">

            @include('notification.notify')
        
            <div class="signup-head text-center">
                <h3>{{tr('signup')}}</h3>
            </div><!--end  of signup-head-->

            @if((config('services.facebook.client_id') && config('services.facebook.client_secret'))
            || (config('services.twitter.client_id') && config('services.twitter.client_secret'))
            || (config('services.google.client_id') && config('services.google.client_secret')))
            
            <div class="social-form">
                
                <div class="social-btn">

                    @if(config('services.facebook.client_id') && config('services.facebook.client_secret'))
                        <div class="social-fb">
                            <form class="social-form form-horizontal" role="form" method="POST" action="{{ route('SocialLogin') }}">
                                <input type="hidden" value="facebook" name="provider" id="provider">
                                <input type="hidden" value="{{ app('request')->input('referral') }}" name="referral" id="referral">

                                <a href="#">
                                    <button type="submit">
                                        <i class="fa fa-facebook"></i>{{tr('login_via_fb')}}
                                    </button>
                                </a>
                            </form>
                        </div>
                    
                    @endif

                    @if(config('services.twitter.client_id') && config('services.twitter.client_secret'))

                        <div class="social-twitter">
                            <form class="social-form form-horizontal" role="form" method="POST" action="{{ route('SocialLogin') }}">
                                <input type="hidden" value="twitter" name="provider" id="provider">
                                <input type="hidden" value="{{ app('request')->input('referral') }}" name="referral" id="referral">

                                <a href="#">
                                    <button type="submit">
                                        <i class="fa fa-twitter"></i>{{tr('login_via_twitter')}}
                                    </button>
                                </a>
                            </form>
                        </div>

                    @endif

                    @if(config('services.google.client_id') && config('services.google.client_secret'))

                        <div class="social-google">
                            <form class="social-form form-horizontal" role="form" method="POST" action="{{ route('SocialLogin') }}">
                                <input type="hidden" value="google" name="provider" id="provider">
                                <a href="#">
                                    <button type="submit">
                                        <i class="fa fa-google-plus"></i>{{tr('login_via_google')}}
                                    </button>
                                </a>
                            </form>
                        </div>
                        
                    @endif

                </div><!--end of social-btn-->          
            </div><!--end of socila-form-->

            <p class="col-xs-12 divider1">OR</p>

            @endif

            <div class="sign-up">

                <form class="signup-form"  id="signup" role="form" method="POST" action="{{ url('/register') }}">

                    {!! csrf_field() !!}

                    @if($errors->has('email') || $errors->has('name') || $errors->has('password_confirmation') ||$errors->has('password') || $errors->has('age_limit'))
                        <div data-abide-error="" class="alert callout">
                            <p>
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong> 
                                    @if($errors->has('email')) 
                                        {{ $errors->first('email') }}
                                    @endif

                                    @if($errors->has('name')) 
                                        {{ $errors->first('name') }}
                                    @endif

                                    @if($errors->has('age_limit')) 
                                        {{$errors->first('age_limit')}}
                                    @endif

                                    @if($errors->has('password')) 
                                        {{$errors->first('password')}}
                                    @endif

                                    @if($errors->has('password_confirmation'))
                                        {{ $errors->has('password_confirmation') }}
                                    @endif

                                </strong>
                            </p>
                        </div>
                    @endif

                    <input type="hidden" value="{{ app('request')->input('referral') }}" name="referral" id="referral">

                    <div class="form-group">
                        <label for="name">{{tr('name')}}</label>
                        <input type="text"  name="name" class="form-control" id="name" aria-describedby="emailHelp" placeholder="{{tr('name')}}" pattern="[a-zA-Z0-9\s]+" title="{{tr('username_notes')}}">
                    </div>
                    <div class="form-group">
                        <label for="email">{{tr('email')}}</label>
                        <input type="email" required name="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="{{tr('email')}}">
                    </div>

                    <div class="form-group">
                        <label for="dob">{{tr('dob')}}</label>
                        <input type="text" name="dob" class="form-control" placeholder="{{tr('enter_dob')}}" id="dob" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">{{tr('password')}}</label>
                        <input type="password" required name="password"  class="form-control" id="password" placeholder="{{tr('password')}}">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">{{tr('confirm_password')}}</label>
                        <input type="password" required name="password_confirmation"  class="form-control" id="confirm_password" placeholder="{{tr('confirm_password')}}">
                    </div>

                    <input type="hidden" name="timezone" value="" id="userTimezone">

                    <div class="change-pwd">
                        <button type="submit" class="btn btn-primary signup-submit">{{tr('submit')}}</button>
                    </div>  
                    <p class="text-right">{{tr('already_account')}} <a href="{{route('user.login.form')}}">{{tr('login')}}</a></p>         
                </form>
            </div><!--end of sign-up-->
        </div><!--end of common-form-->     
    </div><!--form-background end-->

@endsection


@section('scripts')

<script src="{{asset('assets/js/jstz.min.js')}}"></script>

<script src="{{asset('admin-css/plugins/datepicker/bootstrap-datepicker.js')}}"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<script type="text/javascript">
// $("#signup").validate({
// debug: true,
// rules: {
// "name": {required: true,minlength: 3,maxlength: 25},

// "email": {required: true, email: true,remote:"{{url('/check_mail')}}"},
// "dob": {required: true},

// "password": {required: true, minlength: 6},

// "password_confirmation": {required: true, equalTo: "#password"}

// },
// messages:{
//   "email": {
//                     remote: "Email already exist"
//                 },
// "password_confirmation":{
//     equalTo: "Passwords do not match"
// },
                
  
// }
// });
    $(document).ready(function() {

        var max_age_limit = "{{Setting::get('max_register_age_limit' , 18)}}";

        max_age_limit = max_age_limit ? "-"+max_age_limit+"y" : "-15y";

        $('#dob').datepicker({
            autoclose:true,
            format : 'dd-mm-yyyy',
            endDate: max_age_limit,
        });

        var dMin = new Date().getTimezoneOffset();

        var dtz = -(dMin/60);
        $("#userTimezone").val(jstz.determine().name());
    });

</script>

@endsection