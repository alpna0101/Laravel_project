@extends('layouts.user')

@section('content')

    <div class="y-content">
        <div class="row content-row">

            @include('layouts.user.nav')

            @include('user.user_subscriptions._form')

        </div>
    </div>

@endsection