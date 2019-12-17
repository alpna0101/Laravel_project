@extends( 'layouts.user' )


@section( 'styles' )

<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/custom-style.css')}}"> 

<style>
.addcart_table table tr td:nth-child(1) {
    width: 5%;
}
.addcart_table table tr td img {
    width: 100px;
}
.addcart_table tr td a.dlt {
    color: #0066C0;
    text-decoration: underline;
    font-size: 13px;
}
.addcart_table h4 {
    margin: 10px 0px 15px;
}
.addcart_table tr:nth-child(2n) {
    background-color: #f7f7f7;
    box-shadow: 2px 0px 4px rgba(0,0,0,0.20);
}
.addcart_table tr.totl_sctn {
    background-color: transparent;
    box-shadow: unset;
    text-align: right;
}
.addcart_table tr.totl_sctn p, .ryt_subtotl h4 {
    font-size: 20px;
    font-weight: bold;
}
.addcart_table tr.totl_sctn p span, .ryt_subtotl h4 span {
    color: #B12704;
}
.ryt_subtotl {
    border-radius: 4px;
    background-color: #f3f3f3 !important;
    border: 1px #ddd solid;
    margin-top: 88px;
    padding: 20px;
}
.chck_btn {
    margin: 30px 0px;
}
.chck_btn .btn-chckout {
    box-shadow: 0 1px 0 rgba(255,255,255,.4) inset;
    background: #f4d078;
    background: -webkit-linear-gradient(top,#f7dfa5,#f0c14b);
    background: linear-gradient(to bottom,#f7dfa5,#f0c14b);
    max-width: 100%;
    width: 100%;
    border-color: #a88734 #9c7e31 #846a29;
}
</style>

@endsection

@section('content')

<div class="y-content">

	<div class="row content-row">

		@include('layouts.user.nav')

		<div class="page-inner col-sm-9 col-md-10">


			@include('notification.notify')
            <div class="slide-area1 recom-area abt-sec">
               <div class="content-head" style="margin-top: 10px;">
				                    

             <div class="clearfix"></div>    
				                </div><!--end of content-head-->
				<div class="row">
				@if(count($addresses)>0)
				<form id="checkoutForm" action="{{url('/checkout_order')}}" method="post"> 
				<div class="col-md-8">
				<div class='addcart_table'>
					 <h4 class="bold">Shipping Address</h4>
				<div class="table-responsive">
				
				<table class="table">
				<thead>
					<tr>
						<td></td>
						<td></td>
						
					</tr>
				</thead>
				<tbody>
				
				 @foreach($addresses as  $key => $c)
				 <tr>
			    	<td> 
			    	
			    	@if($key==0)
			    	<input type="radio" name="address_id" value="{{$c->id}}" checked="checked">
			    	@else
			    	<input type="radio" name="address_id" value="{{$c->id}}">
			    	@endif
			    	</td>
			    	<td>
			    	    {{$c->first_name}} {{$c->last_name}},<br>
			    		{{$c->address_1}},{{$c->address_2}},<br>
			    		{{$c->city}},{{$c->state}},<br>
			    		{{$c->pincode}}<br>
			    		<a href="{{route('user.edt_address',$c->id)}}" class="dlt delete_cart" id="{{$c->id}}"><span>Edit</span></a>
			    		<a  href="{{route('user.delete_address',$c->id)}}" class="dlt delete_cart" id="{{$c->id}}"><span>Delete</span></a>
			    	</td>
				   
				    </tr>
				    @endforeach
				   
				    <tr class="totl_sctn">
				    	<td colspan="4">
						<a href="{{route('user.add_address')}}" title="Preview" style="color: #337ab7;"><p> <span>Add New 	</span></p>	</a>				
				    	</td>
				    </tr>
				    
				    </tbody>
				    </table>
				    </div>
					</div>
					</div>
					
					<input type="hidden" name = "order_id" value = "{{$order->id}}" >

					<div class="col-md-4">
						<div class="ryt_subtotl">
						      <h4>Subtotal ({{$cart_item}} items): <span>${{$total_price}}</span></h4>

						      <h4 class="no-margin black-clr top mb-15">{{tr('payment_options')}}:</h4>
						      @foreach($payment_method as $method)
						           <div>
						                {{$method->name}} @if(@$method->payment_detail) => {{$method->payment_detail}}@endif

											</div>
											@endforeach
                                         
											@if($products->generated_by=="A")
                                                <!--  <div>
						    
												<label class="radio1">
												    <input id="radio1" type="radio" name="payment_type" checked value="{{PAYPAL}}">
													<span class="outer"><span class="inner"></span></span>{{tr('paypal')}}
												</label>
											</div> -->
											<div class="clear-fix"></div>

											@if(Setting::get('payment_type') == 'stripe')
												<div>
												    <label class="radio1">
													    <input id="radio2" type="radio" name="payment_type" checked value="{{CARD}}">
													    <span class="outer"><span class="inner"></span></span>{{tr('card_payment')}}
													</label>
												</div>
											@endif
                                         <div>
											    <label class="radio1">
												    <input id="radio3" type="radio" name="payment_type" value="coin">
												    <span class="outer"><span class="inner"></span></span>{{tr('coin_payment')}}
												</label>
											</div>
											@endif

									    	<input type="hidden" name="logged_user_id" value="{{Auth::user()->id}}">
							<div class="chck_btn text-center">
								<input type="button" value="Proceed to checkout" class="btn btn-chckout">
							</div>
							
						</div>
					</div>
					   <div id="myModal" class="modal fade" role="dialog">
										  <div class="modal-dialog">

										    <!-- Modal content-->
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal">&times;</button>
										        <h4 class="modal-title">Wallet Addres</h4>
										      </div>
										      <div class="modal-body">
										        <h4>{{@$wallet->wallet_address}}</h4>
										        <a href="https://www.coinpayments.net/index.php?cmd=acct_balances&action=withdraw&coin=BTC" target="_blank">BTC</a>  <a href="https://www.coinpayments.net/index.php?cmd=acct_balances&action=withdraw&coin=ETH" target="_blank">ETH</a>  <a href="https://www.coinpayments.net/acct-balances" target="_blank">Other</a>
										        <p>Once your payment will complete  the <b>transaction id</b> you will get from <b>coinpayment</b> please submit here to complete process of payment.</p>
										        <p><input type="text" name="gateway_id"  class="form-control" value="" placeholder="Enter Transaction Id" id ="txt_id"></p>
										        <p class="error"></p> 
										      </div>

										      <div class="modal-footer">
										        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										            <button  type="button" id="my_button" class="btn btn-danger">
													<i class="fa fa-credit-card"></i> &nbsp; Proceed
												</button>
										      </div>
										    </div>

										  </div>
										</div>
					 </form>
					  @endif
				</div>

			
		</div>

	</div>
</div>

@endsection
@section('scripts')
   
<script>
$('document').ready(function() {
	$("#my_button").click(function(){
     var txt_id  = $("#txt_id").val();
     
     if(txt_id!=""){
     	$('form').submit();
     }else{
     	$('.error').html("Please add your transaction id");
     }
	});

	
$(".btn-chckout").click(function(){
if($('#generated').val()=="A"){
$('form').submit();
}else{
if($('#radio3').is(':checked')) {
	  $("#myModal").modal("show");	
	}else{
		$('form').submit();
	}	
}
	 
// if($("#address_id").val()==""){
// 	alert("Please select a shipping address");
// }else{
// $('form').submit();
// }	
});
});
 
</script>
@endsection