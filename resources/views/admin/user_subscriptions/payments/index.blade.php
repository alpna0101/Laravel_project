@extends('layouts.admin')

@section('title', tr('user_subscription_payments'))

@section('content-header')

{{tr('user_subscription_payments')}}

@endsection

@section('breadcrumb')

    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>

    <li class="active"><i class="fa fa-key"></i> {{ tr('user_subscription_payments') }}</li>

@endsection

@section('content')

	@include('notification.notify')

	<div class="row">

        <div class="col-xs-12">

          <div class="box box-primary">

          	<div class="box-header label-primary">

                <h3>{{ tr('user_subscription_payments') }}

                  <a href="{{ route('admin.user_subscriptions.create') }}" style="float:right" class="btn btn-default">
                  	<i class="fa fa-plus"></i> {{ tr('add_subscription') }}
                  </a>

                </h3>
            </div>
            
            <div class="box-body">
            	
              	@include('admin.user_subscriptions.payments._details')

            </div>

          </div>

        </div>

    </div>

@endsection
