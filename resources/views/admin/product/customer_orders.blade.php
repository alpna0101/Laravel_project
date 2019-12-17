@extends('layouts.admin')

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
	font-size: 14px;
	margin-top: 10px;
}
 thead {
    background: #3c8dbc;
    color: #fff;
}
.radio_top {
    margin: 30px 0 40px 0;
}
.custom_radio {
    display: inline-block;
    margin-right: 40px;
}
.name {
    font-size: 12px;
}
.product_submit.btn.btn-default {
    background: #fdc20f;
    border: none;
    padding: 8px 30px;
    font-size: 18px;
    color: #fff;
}
.product_submit1.btn.btn-default {
    background: #2e93e0;;
    border: none;
    padding: 8px 30px;
    font-size: 18px;
    color: #fff;
}
.product_submit.btn.btn-default:hover {
    background: #cd9c07;
}
.paid.btn.btn-default {
    background: #4CAF50;;
    border: none;
    padding: 8px 30px;
    font-size: 18px;
    color: #fff;
}
.deliver.btn.btn-default {
    background: #4CAF50;;
    border: none;
    padding: 8px 13px;
    font-size: 18px;
    color: #fff;
}

</style>

@endsection

@section('content')

<div class="y-content">

	<div class="row content-row">

	
		<div class="page-inner col-sm-9 col-md-10">


			@include('notification.notify')
            <div class="slide-area1 recom-area abt-sec">
               <div class="content-head" style="margin-top: 10px;">
				                    

             <div class="clearfix"></div>    
				                </div><!--end of content-head-->
				<div class="row">
			@if ($message = Session::get('flash_message'))

				<div class="alert alert-success alert-block">

					<button type="button" class="close" data-dismiss="alert">×</button>	

				        <strong>{{ $message }}</strong>

				</div>

				@endif
				<div class="radio_top">
				<div class="custom_radio">
			  @if(!@$_GET['action'])
				<input type="radio" id="pending" name="radio-group" checked="">
				@else
				<input type="radio" id="pending" name="radio-group">
				@endif
	    		 <label for="ongoing">Pending</label>
			  </div>
			<div class="custom_radio">
		
				  @if(@$_GET['action']=="ongoing")
				  <input type="radio" id="ongoing" name="radio-group" checked="">
				  @else
	    		  <input type="radio" id="ongoing" name="radio-group" >
	    		 @endif
	    		  <label for="ongoing">On Going Orders</label>
			</div>
			<div class="custom_radio">
			  @if(@$_GET['action']=="complete")
			   <input type="radio" id="completed" name="radio-group" checked="">
			    @else
				  <input type="radio" id="completed" name="radio-group">
				   @endif
	    		 <label for="completed">Completed</label>
			</div>
		</div>
				<div class="col-md-12">
				@if(@$orders)
				<div class="table-responsive ">          
				  <table class="table" colspacing="0" colpadding="0">
					    <thead>
					      <tr>
					     
					        <th>Item Name</th>
					        <th>Date</th>
					        <th>Total Price</th>
					        <th>Status</th>
					        <th>Invoice Detail</th>
					      
					        <th>Payment Status</th>
					        
					      </tr>
					    </thead>
					    <tbody>
					   <?php  //print_r($orders);die; ?>
					     @foreach($orders as  $key => $c)
					      <tr style="border: 1px solid;">
					   
					        <td style="width: 400px;">

                               @foreach($c->products as $product)
                              
						         <a href="{{route('user.product_preview',$product->id)}}" title="Preview" style="color: #337ab7;"><div class="cart_details table_product" target="_blank">
									<div class="cart_image">
									<img src="{{asset('uploads/product')}}/{{$product->image}}" width="100px" height=80px> 
								  </div>
									<p class="pro_name" >{{$product->name}},Buying from <span>{{$product->username}}</span></p>
									<p class="details_product">Quantity:{{$product->quantity}}</p><p class="details_product">Unit Price:{{$product->price}}</p>
								</div></a>
								@endforeach
								  
							</td>
					        <td><?php
                        $date1 = $c->created_at;?>
						{{date('d/m/Y',strtotime($date1))}}</td>
					        <td>${{$c->total_price}}</td>

					        <td>
					        @if($c->current_status=="D")
					        <button type="button" class="btn btn-default deliver "  data-id="{{$c->id}}"> Delivered </button>
                            @else
					        <div class="styled-select">
									   <select name="current_status" class="current_status" id="{{$c->id}}">
									     <option value="P" @if($c->current_status=="P") selected @endif>Pending</option>
									     <option value="IP" @if($c->current_status=="IP") selected @endif>Dispatch</option>
									     <option value="D" @if($c->current_status=="D") selected @endif>Delivered</option>
									   </select>
									</div>
                                 @endif

									</td>
					        <td> 
                            <a href ="{{url('/admin/view_invoice')}}/{{$c->id}}">#invoice{{$c->id}}</a>
                           </td>
					       
					         @if($c->status==false && $c->payment_sent==true)
					          <td><a href ="{{url('/mark_paid')}}/{{$c->id}}"><button type="button" class="btn btn-default product_submit "  data-id="{{$c->id}}">Mark As Paid </button></a></td>
					          @elseif($c->status==true && $c->payment_sent==true)
                              <td><button type="button" class="btn btn-default paid "  data-id="{{$c->id}}"> Paid </button></td>
                              @else
                               <td>No Payment Available</td>
					          @endif
					      </tr>
					      @endforeach
					      
					    </tbody>
					</table>
				 </div>
				 @else
				 No orders 
				 @endif
					</div>
					
				   <div id="myModal" class="modal fade" role="dialog">
				   <form id="checkoutForm" action="{{url('/transaction_update')}}" method="post"> 
										  <div class="modal-dialog">

										    <!-- Modal content-->
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal">&times;</button>
										        <h4 class="modal-title">Add payment instruction</h4>
										      </div>
										      <div class="modal-body">
										    <input type="hidden" name="transaction_id" value="" id ="txt_id">
										        <p><textarea name="seller_remark"  class="form-control" value="" required="true" placeholder="Enter Instruction" ></textarea></p>
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
                     	   <div id="shippingModal" class="modal fade" role="dialog">
				             <form id="checkoutForm" action="{{url('admin/save_shipping')}}" method="post" enctype="multipart/form-data"> 
										  <div class="modal-dialog">

										    <!-- Modal content-->
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal">&times;</button>
										        <h4 class="modal-title">Add Shipping</h4>
										      </div>
										      <div class="modal-body">
										    <input type="hidden" name="transaction_id" value="" id ="txt_id1">
										     <p>Provider Name  <input type="text" name="shipping_service"  class="form-control" value="" required="true"></p>
										     <p>Tracking Number<input type="text" name="tracking_number"  class="form-control" value="" required="true"></p>
										    <!--  <p>Tracking Link<input type="text" name="tracking_link"  class="form-control" value=""></p> -->
                                             <p>  Image :<input type="file" name="image"  class="form-control" value=""></p>
										        
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
					
					
				</div>

			
		</div>

	</div>
</div>

@endsection
@section('scripts')
   
<script>
$('document').ready(function() {
	$("#ongoing").click(function(){
      var url = "{{url('/admin/customerorders')}}?action=ongoing";
      window.location.href = url;
	});
	$("#completed").click(function(){
      var url = "{{url('/admin/customerorders')}}?action=complete";
      window.location.href = url;
	});
	$("#pending").click(function(){
      var url = "{{url('/admin/customerorders')}}";
      window.location.href = url;
	});
	$(".current_status").change(function(){
    if($(this).val()=="IP"){
      $("#txt_id1").val($(this).attr('id'));
        $("#shippingModal").modal('show');
    }else{
    	var id  =  $(this).attr("id");
         var status = $(this).val();
        $.ajax({
                url : "{{url('order_status')}}/"+id+'/'+status,
               
                success:function(data){
                	console.log(data);
                	
                	alert("Order status updated successfully!!!")
                	location.reload(true);
                    
                 },
                error:function(){
                  console.log("unable to send");
                }
              });
    }
	});
  $(".send_instruction").click(function(){
  	console.log($(this).attr('data-id'));
     $("#txt_id").val($(this).attr('data-id'));
     $("#myModal").modal('show');
   })

   });
	</script>

@endsection