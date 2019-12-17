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

						    <li class="item  col-xs-4">
						        
						        <div class="product-info" style="margin-left: 0">
						            <a href="javascript:void(0)" class="product-title"> 
						            	{{tr('referral_code')}}
						            	
						            </a>
						            <span class="product-description">
						                <b>{{$user_referrer_details->referral_code}}</b>
						            </span>
						        </div>
						    </li>

						    <li class="item col-xs-4" style="margin-left: 10px">
						        
						        <div class="product-info" style="margin-left: 0">
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

						    <li class="item col-xs-3" style="margin-left: 10px">
						        
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


	            	@if(count($referrals) > 0)

	              	<table id="example1" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      	<th>{{tr('id')}}</th>
						      	<th>{{tr('username')}}</th>
						      	<th>{{tr('image')}}</th>
						      	<th>{{tr('referral_code')}}</th>
						      	<th>{{tr('created_at')}}</th>
						      	<th>{{tr('action')}}</th>
						    </tr>
						</thead>

						<tbody>

							@foreach($referrals as $i => $referral_details)

							    <tr>
							      	<td>{{$i+1}}</td>

							      	<td>
							      		<a href="{{route('admin.users.view' , $referral_details->id)}}"> 

							      			{{$referral_details->username}}

							      		</a>
							      	</td>

							      	<td >
										<img src="{{$referral_details->picture?: asset('placeholder.png')}}" class="img img-circle" style="width: 50px;height: 50px">
									</td>

									<td >
										{{$referral_details->referral_code}}
									</td>
									<td>{{$referral_details->created_at}}</td>
									<td>
										<a href="{{route('admin.users.referrals.view', ['user_id' => $referral_details->user_id, 'parent_user_id' => $referral_details->parent_user_id])}}" class="btn btn-info">
											More 
											<i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i>
										</a>
									</td>

							     
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

@endsection
