@extends('layouts.user')


@section('content')

<div class="y-content">

    <div class="row y-content-row">

        @include('layouts.user.nav')

		<div class="page-inner col-sm-9 col-md-10 profile-edit">
				
				<div class="profile-content">	

					<div class="row no-margin">

                    	<div class="col-sm-12 profile-view">

							<div class="pull-left"><h3>{{tr('subscriptions')}}</h3></div>

							
							<div class="clearfix"></div>

							<hr>

							@include('notification.notify')

							<div class="row">

								@if(count($model) > 0)

									@foreach($model as $s => $subscription)
									
										<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">

											<div class="thumbnail">

												<img alt="{{$subscription->title}}" src="{{$subscription->image ?  $subscription->image : asset('images/subscription.jpg')}}" class="subscription-image" />

												<div class="caption">

													<h3>
														{{$subscription->title}}
													</h3>

													<div class="subscription-desc">
														<?php echo $subscription->description; ?>
													</div>

													<p>
														<span class="btn btn-danger pull-left">{{ Setting::get('currency')}} {{$subscription->amount}} / {{$subscription->plan_duration}} M</span>


														<a href="{{route('user.subscribe.invoice' , ['video_subscription_id' => $subscription->video_subscription_id, 'id'=>Auth::user()->id,
														'video_id'=>$video_tape_id])}}" class="btn btn-success pull-right">{{tr('choose_plan')}}</a>

															
													</p>
													
													<br>
													<br>
												</div>
											
											</div>
										
										</div>

									@endforeach

								@else

									<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">

										 <!-- <p class="no-result">{{tr('no_search_result')}}</p> -->
                            			<img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">

									</div>

								@endif
								
							</div>
						</div>
					</div>
				</div>
			<div class="sidebar-back"></div> 
		</div>

	</div>

</div>

@endsection