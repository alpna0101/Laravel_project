@extends( 'layouts.user' )


@section( 'styles' )

<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/custom-style.css')}}">


@endsection

@section('content')

<div class="y-content">

	<div class="row content-row">

		@include('layouts.user.nav')

		<div class="page-inner col-sm-9 col-md-10">

			@include('notification.notify')

			<div class="spacing1 top">
				<div class="pull-right">
                	
                	<a href="{{route('user.user_subscriptions.index', ['channel_id' => $channel_details->id, 'type' => 'subscribe'] )}}"><button class="btn btn-sm btn-info mb-20 text-uppercase">{{tr('subscriptions')}}</button></a>

                </div>

				<h3 class="no-margin">

					{{tr('a')}}

					<a href="{{route('user.channel', $channel_details->id)}}"> 
						- {{$channel_details->name}}
					</a>

				</h3>	

                <br>			

				<div class="row">

					@if(count($user_subscription_payments) > 0)
						
						@foreach($user_subscription_payments as $up => $user_payment_details)

							<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
								<div class="new-subs-card">
									<div class="new-subs-card-img">
										<img src="{{asset('images/subscriptions2.png')}}">
										<div class="new-subs-card-title">
											<div>
												<div class="text-right">

													@if($up == 0)
														<img src="{{asset('images/guarantee.png')}}" class="active-plan">
													@endif

													@if($user_payment_details->status)
														<span class="label label-info">{{tr('success')}}</span>
													@else
														<span class="label label-warning">{{tr('failure')}}</span>
													@endif
												</div>
												<h3 class="amount">
													<span class="sign">{{$user_payment_details->currency}}</span>
													<span class="cash">{{$user_payment_details->amount}}</span>
													<span class="period">/ {{$user_payment_details->plan}} month</span>
												</h3>
												<h4 class="title">{{$user_payment_details->title}}</h4>
											</div>
										</div>
									</div>

									<div class="new-subs-card-details">
										<div class="new-sub-payment-details">
											
											<h4>
												<span class="bold-text">{{tr('subscription_amount')}}:&nbsp;</span>
												<span>{{$user_payment_details->currency}} {{$user_payment_details->subscription_amount}}</span>
											</h4>
											<h4>
												<span class="bold-text">{{tr('transaction_id')}}:&nbsp;</span>
												<span>{{$user_payment_details->payment_id}}</span>
											</h4>

											<h4>
												<span class="bold-text">{{tr('payment_mode')}}:&nbsp;</span>
												<span>{{$user_payment_details->payment_mode}}</span>
											</h4>
											@if($user_payment_details->status)
											<h4>
												<span class="bold-text">{{tr('paid_at')}}:&nbsp;</span>
												<span>{{date('d M, Y', strtotime($user_payment_details->created_at))}}</span>
											</h4>
											@endif

											
										</div>
										<div>
											<?= $user_payment_details->description;?>
										</div>
									</div>
									<div>
										<a class="subscribe-btn"><i class="fa fa-clock-o"></i>&nbsp;{{$user_payment_details->expiry_date}}</a>
									</div>
								</div>
							
							</div>
						@endforeach

						<div class="row">

	                        <div class="col-md-12">

	                            <div align="center" id="paglink"><?php echo $response->pagination; ?></div>
	                        </div>
	                    </div>

	                @else

						<img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">
					@endif	
				</div>

			</div>
		</div>

	</div>
</div>

@endsection