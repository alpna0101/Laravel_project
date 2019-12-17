<table id="example1" class="table table-bordered table-striped">

	<thead>
	    <tr>
			<th>{{tr('id')}}</th>
			<th>{{tr('payment_id')}}</th>
			<th>{{tr('subscription')}}</th>
			<th>{{tr('username')}}</th>
			<th>{{tr('plan')}}</th>
			<th>{{tr('payment_mode')}}</th>
			<th>{{tr('plan_amount')}}</th>
			<th>{{tr('paid')}}</th>
			<th>{{tr('expiry_date')}}</th>
			<th>{{tr('status')}}</th>
	    </tr>
	
	</thead>

	<tbody>

		@if(count($user_subscription_payments) > 0)

			@foreach($user_subscription_payments as $i => $user_subscription_payment)

			    <tr>
			      	<td>{{$i+1}}</td>

			      	<td>{{$user_subscription_payment->payment_id}}</td>

			      	<td>	

		      			<a href="{{route('admin.user_subscriptions.view' , ['user_subscription_id' => $user_subscription_payment->user_subscription_id])}}">

		      				{{$user_subscription_payment->subscription_name}}

		      			</a>

			      	</td>
			      	

			      	<td>
			      		<a href="{{route('admin.users.view' , $user_subscription_payment->user_id)}}"> 
			      			{{$user_subscription_payment->username}}
			      		</a>
			      	</td>


			      	<td>{{$user_subscription_payment->subscription_plan}}</td>

			      	
		   
		      		<td class="text-capitalize">{{$user_subscription_payment->payment_mode}}</td>


			      	<td class="text-red">
			      		<b>{{Setting::get('currency')}} {{$user_subscription_payment->subscription_amount ?: "0.00"}}</b>
			      	</td>


			      	<td>{{Setting::get('currency')}} {{$user_subscription_payment->paid_amount ? $user_subscription_payment->paid_amount : "0.00" }}</td>
			      	
			      	<td>{{date('d M Y',strtotime($user_subscription_payment->expiry_date))}}</td>
			      	
			      	<td>
			      		@if($user_subscription_payment->status) 
			      			<span style="color: green;"><b>{{tr('paid')}}</b></span>
			      		@else
			      			<span style="color: red"><b>{{tr('not_paid')}}</b></span>

			      		@endif
			      	</td>
			    </tr>					

			@endforeach

		@endif

	</tbody>

</table>