@extends('layouts.admin')

@section('title', tr('referrals'))

@section('content-header') 

{{tr('referrals')}} - 

<a href="{{route('admin.users.view', ['id' => $user_details->id])}}">{{$user_details->name}}</a>

@endsection

@section('breadcrumb')

    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

    <li><a href="{{route('admin.users')}}"><i class="fa fa-user"></i>{{tr('users')}}</a></li>

    <li><a href="{{route('admin.users.view', ['id' => $user_details->id])}}"><i class="fa fa-user"></i>{{tr('view_user')}}</a></li>

    <li class="active"><i class="fa fa-user"></i> {{tr('referrals')}}</li>

@endsection

@section('content')

	@include('notification.notify')

	<div class="row">
        
        <div class="col-xs-12">
          
          	<div class="box box-primary">
	          	
	          	<div class="box-header label-primary">

	                <b style="font-size:18px;">{{tr('referrals')}}</b>

	                <a href="{{route('admin.users.view', $user_details->id)}}" class="btn btn-default pull-right">
	                	{{tr('view_user')}}
	                </a>
	            
	            </div>
	            
	            <div class="box-body table-responsive">

	            	<div class="row col-md-12" style="margin-bottom: 20px">

		            	<ul class="products-list product-list-in-box">

						    <li class="item col-xs-3" style="border-bottom: 0px">
						        
						        <div class="product-info" style="margin-left: 0;border-bottom: 0px">
						            <a href="javascript:void(0)" class="product-title"> 
						            	{{tr('referral_code')}}
						            	
						            </a>
						            <span class="product-description">
						                <b>{{$user_referrer_details->referral_code}}</b>
						            </span>
						        </div>
						    </li>

						    <li class="item col-xs-3" style="border-bottom: 0px">
						        
						        <div class="product-info" style="margin-left: 0;">
						            <a href="javascript:void(0)" class="product-title">

						            	{{tr('username')}}

						            </a>
						            <span class="product-description">
						                <span class="badge bg-maroon bg-flat"> 
						                	{{$user_details->name}}
						                </span>
						            </span>
						        </div>
						    </li>

						    <li class="item col-xs-3" style="border-bottom: 0px">
						        
						        <div class="product-info" style="margin-left: 0;border-bottom: 0px">
						            <a href="javascript:void(0)" class="product-title">

						            	{{tr('total_referrals')}}

						            </a>
						            <span class="product-description">
						                <span class="badge bg-maroon bg-flat"> 
						                	{{$user_referrer_details->total_referrals}}
						                </span>
						            </span>
						        </div>
						    </li>

						    <li class="item col-xs-3" style="border-bottom: 0px">
						        
						        <div class="product-info" style="margin-left: 0">
						            <a href="javascript:void(0)" class="product-title">

						            	{{tr('total_referrals_earnings')}}

						            </a>
						            <span class="product-description">
						                <span class="badge bg-navy bg-flat"> 
						                	{{Setting::get('currency')}} {{$user_referrer_details->total_referrals_earnings}}
						                </span>
						            </span>
						        </div>
						    </li>

						    
						</ul>

					</div>


					<div class="col-md-6">

						@if(count($subscription_payments) > 0)

							<h3 class="text-uppercase">{{tr('subscriptions')}}</h3>

							<hr>

			              	<table id="example1" class="table table-bordered table-striped">

								<thead>
								    <tr>
								      	<th>{{tr('id')}}</th>
								      	<th>{{tr('subscription')}}</th>
								      	<!-- <th>{{tr('payment_id')}}</th> -->
								      	<th>{{tr('amount')}}</th>
								      	<th>{{tr('referral_commission')}}</th>
								      	
								    </tr>
								</thead>

								<tbody>

									@foreach($subscription_payments as $i => $payment)

									    <tr>
									      	<td>{{$i+1}}</td>
									      	<td>{{$payment->title}}</td>
									      	<!-- <td>{{$payment->payment_id}}</td> -->
									      	<td>{{Setting::get('currency')}} {{$payment->amount ?: 0.00}}</td>
									      	<td>{{Setting::get('currency')}} {{$payment->referral_commission ?: 0.00}}</td>
									    </tr>

									@endforeach

								</tbody>
							</table>

							<div>
								
							</div>
						@else
							<h3 class="no-result">{{tr('no_subscription_found')}}</h3>
						@endif

					</div>

					<div class="col-md-6">

						<h3 class="text-uppercase">{{tr('pay_per_view')}}</h3>

						<hr>

						@if(count($ppv_payments) > 0)

			              	<table id="example1" class="table table-bordered table-striped">

								<thead>
								    <tr>

										<th>{{tr('id')}}</th>
										<th>{{tr('video')}}</th>
										<th>{{tr('amount')}}</th>
								      	<th>{{tr('referral_commission')}}</th>
								    </tr>
								</thead>

								<tbody>

									@foreach($ppv_payments as $i => $payment)

									    <tr>
									      	<td>{{$i+1}}</td>

									      	<td>

								      			<a href="{{route('admin.videos.view' , array('id' => $payment->video_id))}}">
								      				{{$payment->title ?: "-"}}
								      			</a>

									      	</td>

									      	<td>{{Setting::get('currency')}} {{$payment->amount ?: 0.00}}</td>

									      	<td>{{Setting::get('currency')}} {{$payment->referral_commission ?: 0.00}}</td>


									    </tr>	


										

									@endforeach
								</tbody>
							
							</table>

						@else

							<h3 class="no-result">{{tr('no_result_found')}}</h3>

						@endif

					</div>

	            </div>

          	</div>
        </div>
    </div>

@endsection
