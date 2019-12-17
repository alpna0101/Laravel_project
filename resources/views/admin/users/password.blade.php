@extends('layouts.admin')

@section('title', tr('add_user'))

@section('content-header', tr('add_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
    <li class="active"><i class="fa fa-user-plus"></i> {{tr('reset_password')}}</li>
@endsection

@section('styles')

<link rel="stylesheet" href="{{asset('admin-css/plugins/datepicker/datepicker3.css')}}">

@endsection

@section('content')
@include('notification.notify')

<div class="row">

    <div class="col-md-10">

        <div class="box box-primary">

            <div class="box-header label-primary">
                <b style="font-size:18px;">@yield('title')</b>
                <a href="{{route('admin.users')}}" class="btn btn-default pull-right">{{tr('view_users')}}</a>
            </div>

            <form class="form-horizontal" action="{{route('admin.users.password')}}" method="POST" enctype="multipart/form-data" role="form">

                <div class="box-body">
                 <input type="hidden" name="id" value="{{$user->id}}">
                    <input type="hidden" name="reset_password" value="true">

                    <div class="row">
                        <div class="col-sm-3"></div>
                           <div class="col-sm-6">
                             <input type="password" required name="password" class="form-control" id="password" placeholder="{{tr('password')}} *" minlength="6" title="Enter Minimum 6 Characters">
                          </div>
                         <div class="col-sm-3"></div>
                    </div>
                        <div class="clearfix"></div>
                       <div class="row">
                       <div class="col-sm-3"></div>
                       <div class="col-sm-6">
                            <input type="password"  style ="margin-top: 10px;" required name="password_confirmation" class="form-control" id="confirm-password" placeholder="{{tr('confirm_password')}} *" minlength="6" title="Enter Minimum 6 Characters">
                        </div>
                             <div class="col-sm-3"></div>
                        </div>

                <div class="box-footer text-center">
                  
                    <button type="submit" class="btn btn-success ">{{tr('submit')}}</button>
                </div>
                
            </form>
        
        </div>

    </div>

</div>
@endsection

@section('scripts')

<script src="{{asset('admin-css/plugins/datepicker/bootstrap-datepicker.js')}}"></script> 


<script src="{{asset('assets/js/jstz.min.js')}}"></script>

<script>
    
 var max_age_limit = "{{Setting::get('max_register_age_limit' , 18)}}";

max_age_limit = max_age_limit ? "-"+max_age_limit+"y" : "-15y";

$('#dob').datepicker({
    autoclose:true,
    format : 'dd-mm-yyyy',
    endDate: max_age_limit,
});


$(document).ready(function() {

    var dMin = new Date().getTimezoneOffset();
    var dtz = -(dMin/60);
    // alert(dtz);
    $("#userTimezone").val(jstz.determine().name());
});


function loadFile(event, id){
    // alert(event.files[0]);
    var reader = new FileReader();
    reader.onload = function(){
      var output = document.getElementById(id);
      // alert(output);
      output.src = reader.result;
      //$("#c4-header-bg-container .hd-banner-image").css("background-image", "url("+this.result+")");
    };
    reader.readAsDataURL(event.files[0]);
}

</script>

@endsection
