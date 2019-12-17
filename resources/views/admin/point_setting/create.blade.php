@extends('layouts.admin')

@section('title', tr('point_setting'))

@section('content-header', tr('point_setting'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li> <a href="{{route('admin.point_index')}}" ><i class="fa fa-key"></i> {{tr('view_point_setting')}}</a></li>
    <li class="active">{{tr('add_point_setting')}}</li>
@endsection

@section('content')

@include('admin.point_setting._form')

@endsection

@section('scripts')
    <script src="http://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'ckeditor' );
        function loadFile(event, id){

           $("#"+id).show();

            // alert(event.files[0]);
            var reader = new FileReader();
            reader.onload = function(){
              var output = document.getElementById(id);
              // alert(output);
              output.src = reader.result;
               //$("#imagePreview").css("background-image", "url("+this.result+")");
            };
            reader.readAsDataURL(event.files[0]);
        }
    </script>
@endsection