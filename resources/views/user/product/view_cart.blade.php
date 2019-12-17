@extends( 'layouts.user' )


@section( 'styles' )

<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/custom-style.css')}}"> 

<style>
.addcart_table table tr td:nth-child(1) {
    width: 10%;
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
		
				<div class="col-md-8">
				<div class='addcart_table'>
					 <h4 class="bold">Shopping Cart</h4>
				<div class="table-responsive">
			
				<table class="table">
				<thead>
					<tr>
						<td>Image</td>
						<td>Title</td>
						<td>Price</td>
						<td>Quantity</td>
					</tr>
				</thead>
				<tbody>

				<?php  $total_price = 0; ?>
				 @foreach($carts as $c)
				  <?php  $datas1 =  explode(".", $c->image); ?>
				 <tr>
			    	<td> 
			    		@if(@$datas1[1]=="img" || @$datas1[1]=="png" || @$datas1[1]=="jpg"  || @$datas1[1]=="JPG"  || @$datas1[1]=="gif" || @$datas1[1]=="JPEG" )
			    		<img src="{{asset('uploads/product')}}/{{$c->image}}"> 
			    		@else
                                  <video   class="imgage"  src="{{asset('uploads/product')}}/{{$c->image}}" width="100" height="100"></video>
                                  @endif
			    	</td>
			    	<td>
			    		<a href="{{route('user.product_preview',$c->id)}}" title="Preview" style="color: #337ab7;"> <h4>{{$c->name}}</h4>
			    		</a>
			    		<a  class="dlt delete_cart" id="{{$c->id}}"><span>Delete</span></a>
			    	</td>
				    <td>
				    	{{$c->price}}
				    </td> 

				    <?php $total_price += $c->quantity*$c->price ?>
				    <td>
                 
				    	<select class="form-control quantity" id="{{$c->id}}" >
				    	 @for ($i =1; $i <= 10; $i++)
                <option value="{{ $i }}" <?php if($c->quantity==$i){ echo "selected";}?>>{{ $i }}</option>
                   @endfor           
				    
				    	</select>
				    </td>
				    </tr>
				    @endforeach
				    <tr class="totl_sctn">
				    	<td colspan="4">
						<p>Subtotal ({{$cart_item}} items): <span>USD {{$total_price}}	</span></p>					
				    	</td>
				    </tr>
				    </tbody>
				    </table>
				    </div>
					</div>
					</div>
					<div class="col-md-4">
						<div class="ryt_subtotl">
						
							<h4>Subtotal ({{$cart_item}} items): <span>${{$total_price}}</span></h4>
							<div class="chck_btn text-center">
								<a href = "{{url('/checkout')}}" ><button class="btn btn-chckout">Place Order</button></a>
							</div>
						</div>
					</div>
				
				</div>

			
		</div>

	</div>
</div>

@endsection
@section('scripts')
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
 $(".quantity").change(function(){
  var id  =  $(this).attr("id");
  var qty  =  $(this).val();
        $.ajax({
                url : "{{url('update_cart')}}/"+id+'/'+qty,
               
                success:function(data){
                	location.reload(true);
                    
                 },
                error:function(){
                  console.log("unable to send");
                }
              });
 	});
   $(".delete_cart").click(function(){
         
            var id  =  $(this).attr("id");
              $.ajax({
                url : "{{url('delete_cart')}}/"+id,
               
                success:function(data){

               location.reload(true);

                    // $("tbody").html(data);

                    
                 
                },
                error:function(){
                  console.log("unable to send");
                }
              });
            
         });
</script>
@endsection