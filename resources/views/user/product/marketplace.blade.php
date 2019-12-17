@extends('layouts.user')
 
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/wizard.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/owl.carousel.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/owl.theme.css')}}">  
     <link rel="stylesheet" href="{{asset('assets/css/star-rating.css')}}">
<link rel="stylesheet" href="{{asset('admin-css/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">
<style type="text/css">
.star-rating .clear-rating {
    padding-right: 5px;
    display: none;
}  
</style>

@endsection

@section('content')

<div class="y-content">

  <div class="row content-row">

    @include('layouts.user.nav')

    <div class="page-inner">
     
      <!-- start banner part here -->
      <section class="banner_area">
         <div class="container-fluid">
               <h1>Marketplace</h1>
         </div>
      </section>
      <?php if(@$_GET['token']){?>
       <h4 class="text-center">Buy token from admin to sell your product</h4>
      <?php   } ?>
      <!-- start Features part here -->
      <section class="market_place">
        <div class="container-fluid">
            <div class="product_title">
               <h2> Admin Generated Products <a href="{{url('/admin-products')}}">View All</a> </h2>
            </div>
            
             @if(count($adminproduct)>0)
           
            <div class="owl-carousel owl-theme">
                @foreach($adminproduct as $product)
               <div class="item">
                  @if($product->type='seller_token')
                   <img src="{{asset('/seller_token.png')}}">
                 <!--  <img src="{{url('/seller_token.png')}}"> -->
                  @else
                <img src="{{asset('uploads/product')}}/{{$product->image}}">
                @endif
                   <div class="content_box">
                    <?php  $name=substr($product->name,0,20) . '...';  ?>
                   <a href="{{route('user.product_preview',$product->id)}}" title="Preview" style="color: #337ab7;">
                   
                       <h4> @if($product->type='seller_token') TipMe Credits @else {{ $name}} @endif</h4></a>
                       <p>by Admin</p>
                       <h2>$ {{$product->price}}</h2>
                        <h5><input id="view_rating" name="rating" type="number" class="rating view_rating" min="1" max="5" step="1" value="{{$product->ratings_average}}">
                  </h5>
                   <a href="javascript:void(0)" title="Add to cart" > <button class="btn btn-default product_submit" id="{{$product->id}}">Add Cart</button> </a>
                    </div>
               </div>
             @endforeach
               </div>
               @else 
               <h3>No admin product found</h3>
               @endif
          
          
      
               
               
            </div>
     
      </section>

      <section class="market_place">
        <div class="container-fluid">
            <div class="product_title">
               <h2> User Generated Products <a href="{{url('/user-products')}}">View All</a> </h2>
            </div>
            <div class="owl-carousel owl-theme">
                @foreach($userproduct as $product)
               
                <div class="item">
                 <?php $datas =  explode(".",$product->image);

                

                  ?>
                 
              @if(@$datas[1]=="img" || @$datas[1]=="png" || @$datas[1]=="png" || @$datas[1]=="JPG" || @$datas[1]=="jpg" || @$datas[1]=="gif" || @$datas[1]=="JPEG" || @$datas[1]=="jpeg")
                <img src="{{asset('uploads/product')}}/{{$product->image}}">
                
                  @else
                 <video controls="" id="thumb"  class="" src="{{asset('uploads/product')}}/{{$product->image}}"></video>
                  @endif
            
                 <div class="content_box">
                 
                      <?php  $name = substr($product->name,0,20) . '...';  ?>
                   <a href="{{route('user.product_preview',$product->id)}}" title="Preview" style="color: #337ab7;">

                       <h4>{{ $name }}</h4></a>

                       <p>by {{$product->username}}</p>
                       <h2>$ {{$product->price}}</h2>
                       <h3><input id="view_rating" name="rating" type="number" class="rating view_rating" min="1" max="5" step="1" value="{{$product->ratings_average}}">
                  </h3>
            
                         <a href="javascript:void(0)" title="Add to cart" > <button class="btn btn-default product_submit" id="{{$product->id}}">Add Cart</button> </a>
                          </div>
                          </div>
                 
             
               @endforeach
               </div> 
              
              
           
          
      
               
               
            </div>
      
      </section>
  
      
      <!-- end Everything Made Easy part here -->
      <!-- start Contact part here -->
   
     
      <!-- start footer start-->
      
      <!-- end Footer  -->
  
   </div>
   </div>
  </div>
@endsection

     @section('scripts')

    

<script type="text/javascript" src="{{asset('assets/js/star-rating.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/toast.script.js')}}"></script>
       <script type="text/javascript" src="{{asset('streamtube/js/owl.carousel.js')}}"></script>
      <script>
         $('.owl-carousel').owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            navText:['<img src="{{asset("streamtube/images/left_arrow.png")}}">', '<img src="{{asset("streamtube/images/right_arrow.png")}}">'],
            dots:false,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:2
                },
                1000:{
                    items:5
                }
            }
         })
         $(".product_submit").click(function(){
            var cart = $(".my_cart").text();
            var id  =  $(this).attr("id");

            $.ajax({
                url : "{{url('check_cart_seller')}}",
                type: "POST",
                data: {product_id: id},
                success:function(data){
                    
                 console.info('----', data);
                 if(data > 0) {
                  if(confirm("Your cart contains products from other seller. Do you want to discard the selection and add this product in the cart?")) {
                    updateCartItems(id)
                  }
                 } else {
                    updateCartItems(id)
                 }
                },
                error:function(){
                  console.log("unable to check cart seller");
                }
              });
            
         });

        function updateCartItems(id) {
          $.ajax({
            url : "{{url('add_to_cart')}}/"+id,
           
            success:function(res){
              console.info('---======', res);
                $(".my_cart").text(res);
            },
            error:function(){
              console.log("unable to send");
            }
          });
        }
      </script>

  @endsection