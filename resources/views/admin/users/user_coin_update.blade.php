@extends('layouts.admin')

@section('title', tr('edit_user'))

@section('content-header', tr('edit_user'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users.coin_price')}}"><i class="fa fa-user"></i> {{tr('coin_price')}}</a></li>
    <li class="active">{{tr('edit_user')}}</li>
@endsection

@section('styles')

<link rel="stylesheet" href="{{asset('admin-css/plugins/datepicker/datepicker3.css')}}">

@endsection

@section('content')

@include('notification.notify')

 <form class="form-horizontal" action="{{route('admin.users.edit_coin_price')}}" method="POST" enctype="multipart/form-data" role="form"> 

                <div class="box-body">
                <div class="row">
                 <div class="form-group">
 
                                <div class="col-lg-2">

                                   {{tr('price')}}

                                </div>

                                 <div class="col-lg-4">
                                   <input type="hidden" name="id" value="{{$_GET['id']}}" id="userTimezone">
                                   <input type="text" required name="price" value="{{$data->price}}" class="form-control" id="username" placeholder="{{tr('price')}} *" title="{{tr('token_price')}}">

                                </div>
                                
                            <div class="col-lg-4">
                             <button type="submit" class="btn btn-success text-center">{{tr('save')}}</button>
                             </div>
                            </div>
                            </div>


                             

                </div>
                <!--  <div class="box-footer"> -->
                   <!--  <a href="" class="btn btn-danger">{{tr('reset')}}</a> -->
                   
               <!--  </div> -->

</form>

@endsection

