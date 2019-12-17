@extends('layouts.user')


@section('content')

<div class="y-content">

    <div class="row y-content-row">

        @include('layouts.user.nav')

		<div class="page-inner col-sm-9 col-md-10 profile-edit">
				   <section class="token_banner">
  <div class="container-fluid text-center">
      <h2>Limited Time Offer 

</h2>
    
      <h3 class="free_coin">Subscribe And Get Free Token</h3>
      <h3 class="truau_game">TruAu Token</h3>
      <div class="row">
        <div class="countdown_box pull-left">
           <p> Tokens Given Away</p>
            <div class="countdown">
                <h2>{{$given_token}}</h2>
            </div>
        </div>
        <div class="countdown_box pull-right">
            <p>Tokens Left</p>
            <div class="countdown">
                <h2>{{$token_left}}</h2>
            </div>
        </div>
      </div>
  </div>
</section>
				<div class="profile-content">	

					<div class="row no-margin">

                    	<div class="col-sm-12 profile-view">

							<h3 class="m-0">{{tr('subscriptions')}}</h3>

							@include('notification.notify')

							<?php /*
							<div class="row">

								@if(count($subscriptions) > 0)

									@foreach($subscriptions as $s => $subscription)
									
										<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">

											<div class="thumbnail">

												<img alt="{{$subscription->title}}" src="{{$subscription->picture ?  $subscription->picture : asset('images/landing-9.png')}}" class="subscription-image" />
												<div class="caption">

													<h3>
														{{$subscription->title}}
													</h3>

													<div class="subscription-desc">
														<?php echo $subscription->description; ?>
													</div>

														<span class="btn btn-danger pull-left" style="cursor: auto";>{{ Setting::get('currency')}} {{$subscription->amount}} / {{$subscription->plan}} M</span>

															comment part starts @ ranjitha

															@if(Setting::get('payment_type') == 'paypal')

															<a href="{{route('user.paypal' , $subscription->id)}}" class="btn btn-success pull-right">{{tr('choose_plan')}}</a>

															@else

																<a href="{{route('user.card.stripe_payment' , ['subscription_id' => $subscription->id])}}" class="btn btn-success pull-right">{{tr('choose_plan')}}</a>

															@endif  

															<button  type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#paypal">{{tr('choose_plan')}}</button>

															comment part ends @ ranjitha

															<a href="{{route('user.subscription.invoice' , ['s_id' => $subscription->id, 'u_id'=>Auth::user()->id])}}" class="btn btn-success pull-right">{{tr('choose_plan')}}</a>
													</p>
													<br>
													<br>

												</div>
											
											</div>
										
										</div>

									@endforeach

								@else

									 <img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">

								@endif
								
							</div>

							*/?>

							<!-- new ui -->
							<div class="row">
								@if(count($subscriptions) > 0)
									@foreach($subscriptions as $s => $subscription)
									<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
										<div class="new-subs-card">
											<div class="new-subs-card-img">
												<img src="{{asset('images/subscriptions1.png')}}" alt="{{$subscription->title}}">
												<div class="new-subs-card-title">
													<div>
														<h3 class="amount">
															<span class="sign">{{ Setting::get('currency')}}</span>
															<span class="cash">{{$subscription->amount}}</span>
															<span class="period">/ {{$subscription->plan}} month</span>
														</h3>
														<h4 class="title">{{$subscription->title}}</h4>
														 @if($subscription->amount=="0.99")
								                        <h5 class="title">(Get 1 TruAu Token)</h5>
								                       
								                        @elseif($subscription->amount=="99")
								                        <h5 class="title">(Get 3 TruAu Tokens)</h5>
								                       
								                        @elseif($subscription-> amount=="299")
								                           <h5 class="title">(Get 5 TruAu Tokens)</h4>
								                          @else
								                          <h5 class="title"></h4>
								                          @endif
													</div>
												</div>
											</div>
											<div class="new-subs-card-details">
												
												<?= $subscription->description; ?>
											</div>
											<div>
												<a href="{{route('user.subscription.invoice' , ['s_id' => $subscription->id])}}" class="subscribe-btn">{{tr('choose_plan')}}</a>
											</div>
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
