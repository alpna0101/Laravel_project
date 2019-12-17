@extends('layouts.user')


@section('content')

<div class="y-content">

    <div class="row y-content-row">

        @include('layouts.user.nav')

		<div class="page-inner col-sm-9 col-md-10 profile-edit">

			@include('notification.notify')

				<div class="profile-content">	

					<div class="row no-margin">

                    	<div class="col-sm-12 profile-view">

							<h3 class="m-0">
								
								{{tr('subscriptions')}} - 
								
								<a href="{{route('user.channel', $channel_details->id)}}"> {{$channel_details->name}}</a>

								@if($channel_details->user_id == Auth::user()->id)

									<a href="{{route('user.user_subscriptions.create', ['channel_id' => $channel_details->id])}}" class="btn btn-default active pull-right">	<i class="fa fa-plus"></i> <b>{{tr('add_subscription')}}</b>
									</a>

								@else 

									<a href="{{route('user.user_subscriptions.history', ['channel_id' => $channel_details->id])}}" class="btn btn-success active pull-right">	<i class="fa fa-eye"></i> <b>{{tr('my_plans')}}</b>

									</a>

								@endif

							</h3>


							<!-- new ui -->
							
							<div class="row">

								@if(count($user_subscriptions) > 0)
									
									@foreach($user_subscriptions as $s => $user_subscription_details)

									<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
										<div class="new-subs-card">
											<div class="new-subs-card-img">
												<img src="{{asset('images/subscriptions1.png')}}" alt="{{$user_subscription_details->title}}">
												<div class="new-subs-card-title">
													<div>
														<h3 class="amount">
															<span class="sign">{{ Setting::get('currency')}}</span>
															<span class="cash">{{$user_subscription_details->amount}}</span>
															<span class="period">/ {{$user_subscription_details->plan}} month</span>
														</h3>
														<h4 class="title">{{$user_subscription_details->title}}</h4>
													</div>
												</div>
											</div>

											<div class="new-subs-card-details">
												
												<?= $user_subscription_details->description; ?>
											</div>

											@if(Auth::user()->id == $user_subscription_details->user_id)

												<div>
													<a href="{{route('user.user_subscriptions.edit', ['user_subscription_id' => $user_subscription_details->user_subscription_id, 'channel_id' => $user_subscription_details->channel_id])}}" class="btn btn-primary active col-lg-3 text-uppercase btn-flat">
														<i class="fa fa-pencil"></i> {{tr('edit')}}
													</a>

													<a href="{{route('user.user_subscriptions.delete', ['user_subscription_id' => $user_subscription_details->user_subscription_id, 'channel_id' => $user_subscription_details->channel_id])}}" class="btn btn-default active col-lg-3 text-uppercase btn-flat" onclick="return confirm('Are you sure?')">
														<i class="fa fa-trash"></i> {{tr('delete')}}
													</a>

													<a href="{{route('user.user_subscriptions.subscribers', ['user_subscription_id' => $user_subscription_details->user_subscription_id, 'channel_id' => $user_subscription_details->channel_id])}}" class="btn btn-primary active col-lg-6 text-uppercase btn-flat">
														{{tr('subscribers')}} ({{$user_subscription_details->total_subscription}})
													</a>

												</div>

											@else

												<div>
													<a href="{{route('user.user_subscriptions.invoice' , ['user_subscription_id' => $user_subscription_details->user_subscription_id, 'channel_id' => $channel_details->id])}}" class="subscribe-btn">
														{{tr('choose_plan')}}
													</a>
												</div>

											@endif
										

										</div>
									
									</div>

									@endforeach

								@else

									<img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">


								@endif
							</div>
							<!-- new ui -->
						</div>
					</div>
				</div>
			<div class="sidebar-back"></div> 
		</div>

	</div>

</div>

@endsection
