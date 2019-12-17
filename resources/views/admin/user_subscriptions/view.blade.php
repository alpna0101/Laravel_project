@extends('layouts.admin')

@section('title', tr('view_subscription'))

@section('content-header', tr('view_subscription'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.subscriptions.index')}}"><i class="fa fa-key"></i> {{tr('subscriptions')}}</a></li>
    <li class="active"><i class="fa fa-eye"></i>&nbsp;{{tr('view_subscriptions')}}</li>
@endsection

@section('styles')

<style type="text/css">
    .user-block .username, .user-block .description, .user-block .comment {
        margin-left: 0px;
    }
</style>

@endsection

@section('content')

	@include('notification.notify')

	<div class="row">

        <div class="col-xs-12">

            <div class="panel">
                
                <div class="panel-body">

                    <div class="post">
                        
                        <div class="user-block">

                            <span class="username">
                                <a>{{$user_subscription_details->title}}</a>
                            </span>

                            <span class="description">{{$user_subscription_details->created_at->diffForHumans()}}</span>

                        </div>

                        <div class="row margin-bottom">

                            <div class="col-sm-12">

                                <div class="row">

                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('title')}}</b></h4>

                                            <label>{{$user_subscription_details->title}}</label>

                                        </div>

                                    </div>

                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('plan')}}</b></h4>

                                            <label class="text-red">{{$user_subscription_details->plan}}</label>                                            
                                        </div>

                                    </div>

                                   
                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('status')}}</b></h4>

                                            @if($user_subscription_details->status)

                                                <label class="text-green"><b>{{tr('approved')}}</b></label>

                                            @else

                                                <label class="text-navyblue"><b>{{tr('pending')}}</b></label>
                                            @endif

                                        </div>

                                    </div>

                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('amount')}}</b></h4>

                                            {{Setting::get('currency')}} {{$user_subscription_details->amount}}

                                        </div>
                                    
                                    </div>

                                    <div class="col-sm-6">

                                        <div class="header">

                                            <h4><b>{{tr('total_subscription')}}</b></h4>

                                            <a href="{{ route('admin.user_subscriptions.payments', ['user_subscription_id' => $user_subscription_details->id]) }}" class="btn btn-success btn-xs">{{$user_subscription_details->total_subscription}}</a>

                                        </div>
                                    
                                    </div>

                                    <div class="col-sm-12">

                                        <h3><b>{{tr('description')}}</b></h3>

                                        <p><?= $user_subscription_details->description ?></p>

                                    </div>
                            	
                            	</div>
                        
                       		</div>

                    	</div>
                
                	</div>

            	</div>

        	</div>

    	</div>

    </div>

@endsection


