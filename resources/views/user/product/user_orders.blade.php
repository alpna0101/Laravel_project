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
.cart_details {
    position: relative;
   
}
.table_product .cart_image {
    padding: 4px 15px 0 0;
border: none;
float: left;
}
.cart_image {
   
    left: 0;
    }
    .table_product .details_product {
    font-size: 14px;
    color: #757575;
    padding-left: 63px;
}
.pro_name{
	margin-top: 10px;
}
 thead {
    background: #fdc20f;
    color: #fff;
}
.product_submit.btn.btn-default {
    background: #fdc20f;
    border: none;
    padding: 12px 50px;
    font-size: 18px;
    color: #fff;
}
.product_submit1.btn.btn-default {
    background: #2e93e0;;
    border: none;
    padding: 12px 50px;
    font-size: 18px;
    color: #fff;
}
.product_submit.btn.btn-default:hover {
    background: #cd9c07;
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
			

				<div class="col-md-12">
				
				@if ($message = Session::get('flash_message'))

				<div class="alert alert-success alert-block">

					<button type="button" class="close" data-dismiss="alert">×</button>	

				        <strong>{{ $message }}</strong>

				</div>

				@endif
								<div class="table-responsive ">          
				  <table class="table" colspacing="0" colpadding="0">
					    <thead>
					      <tr>
					        <th>Item Name</th>
					        <th>Date</th>
					        <th>Price</th>
					        <th>Status</th>
					        <th>Invoice Detail</th>
					        <th>Payment Send</th>
					        <th>Shipping Detail</th>
					      </tr>
					    </thead>
					    <tbody>
					   <?php  //print_r($orders);die; ?>
					     @foreach($orders as  $key => $c)
					      <tr style="border: 1px solid;">
					        <td>

                               @foreach($c->products as $product)
                               <?php  $datas1 =  explode(".",$product->image); ?>
                                 <a href="{{route('user.product_preview',$product->id)}}" title="Preview" target ="_blank" style="color: #337ab7;">
						        <div class="cart_details table_product">
									<div class="cart_image">
									@if(@$datas1[1]=="img" || @$datas1[1]=="png" || @$datas1[1]=="jpg"  || @$datas1[1]=="JPG"  || @$datas1[1]=="gif" || @$datas1[1]=="JPEG" )
									<img src="{{asset('uploads/product')}}/{{$product->image}}" width="50px" height=50px> 
									@else
                                  <video  class="imgage"  src="{{asset('uploads/product')}}/{{$product->image}}" width="50" height="50"></video>
                                  @endif
								  </div>
									<p class="pro_name" >{{$product->name}}</p>
									<p class="name">Buying from <span>{{$product->username}}</span></p>
									<p class="details_product">Quantity:{{$product->quantity}}</p>
									<p class="details_product">Price:{{$product->quantity}}X{{$product->price}}</p>
								</div>
								</a>
								@endforeach
								  
							</td>
					        <td><?php
                        $date1 = $c->created_at;?>
						{{date('d/m/Y',strtotime($date1))}}</td>
					        <td>${{$c->total_price}}</td>

					        <td><a class="status_btn">@if($c->current_status=="P") Pending @elseif($c->current_status=="IP") In progress @elseif($c->current_status=="C") Cancel @else Delivered @endif</a></td>
					        <td>
					        @if($c->invoice_date!="0000-00-00 00:00:00")
                            <a href ="{{url('/view_invoice')}}/{{$c->id}}">#invoice{{$c->id}}</a>
                            @else
                            Invoice Is Not Available
                            @endif
                            </td>
					       <td>
					         @if($c->payment_sent==true)
					            <button type="button" class="btn btn-default product_submit1 "  data-id="{{$c->id}}">Sent</button></td>
					         @else
					       <button type="button" class="btn btn-default product_submit send_instruction"  data-id="{{$c->id}}">Send</button></td>
					       @endif
					       @if(@$c->shipping_id) <td class="view_shipping" id="{{$c->id}}">View</td> @else <td>No Shipping Available </td>@endif</td>
					      </tr>
					      @endforeach
					      
					    </tbody>
					</table>
				 </div>
					</div>
					
				  <div id="myModal" class="modal fade" role="dialog">
				   <form id="checkoutForm" action="{{url('/payment_send')}}" method="post"> 
										  <div class="modal-dialog">

										    <!-- Modal content-->
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal">&times;</button>
										        <h4 class="modal-title">Add message to seller</h4>
										      </div>
										      <div class="modal-body">
										    <input type="hidden" name="transaction_id" value="" id ="txt_id">
										        <p><textarea name="seller_remark"  class="form-control" value="" required="true" placeholder="Enter Message" ></textarea></p>
										        <p class="error"></p> 
										      </div>

										      <div class="modal-footer">
										        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										            <button  type="submit" id="my_button" class="btn btn-danger">
													<i class="fa fa-credit-card"></i> &nbsp; Proceed
												</button>
										      </div>
										    </div>

										  </div>
										  </form>
										</div>

				<!-- 	<div class="col-md-4">
						<div class="ryt_subtotl">
						    
							
						</div>
					</div> -->
					
						  <div id="shipping_info" class="modal fade" role="dialog">
				   <form id="checkoutForm" action="{{url('/payment_send')}}" method="post"> 
										  <div class="modal-dialog">

										    <!-- Modal content-->
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal">&times;</button>
										        <h4 class="modal-title">Shipping Detail</h4>
										      </div>
										      <div class="modal-body ship">
										       
										      </div>

										      <div class="modal-footer">
										        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										            <button  type="submit" id="my_button1" class="btn btn-danger" data-dismiss="modal">
													<i class="fa fa-credit-card"></i> &nbsp; Ok
												</button>
										      </div>
										    </div>

										  </div>
										  </form>
										</div>
				</div>

			
		</div>

	</div>
</div>

@endsection
@section('scripts')
   
<script>
$('document').ready(function() {
  $(".send_instruction").click(function(){
  	console.log($(this).attr('data-id'));
     $("#txt_id").val($(this).attr('data-id'));
     $("#myModal").modal('show');
   })
 $(".view_shipping").click(function(){
  var id  =  $(this).attr("id");
  
        $.ajax({
                url : "{{url('shipping_detail')}}/"+id,
               
                success:function(data){
                	console.log(data);
                	
                	$(".ship").html(data);
                	//location.reload(true);
                    $("#shipping_info").modal('show');
                 },
                error:function(){
                  console.log("unable to send");
                }
              });
 	});
   });
	</script>

@endsection