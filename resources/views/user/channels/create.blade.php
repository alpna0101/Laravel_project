@extends('layouts.user')

@section('styles')

<!-- Add css file and inline css here -->
<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/custom-style.css')}}"> 

<link href="{{asset('assets/iCheck/skins/minimal/pink.css')}}" rel="stylesheet">

<style type="text/css">

.form-control {

	border-radius: 0px;
}

</style>

@endsection 


@section('content')

@include('user.channels._form')

@endsection

