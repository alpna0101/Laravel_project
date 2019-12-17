@extends('layouts.user')

@section('content')
<style type="text/css">
    .item {
        border-bottom: none;
    }
    .product_price {
        font-size:18px;
        color: #b12704;
    }
    .slide-box {
        margin-bottom:20px;
    }
    .admin_product .slide-image {
    height: auto!important;
    text-align: center;
}
.admin_product .slide-image img {
    max-width: 200px;
    width: 100%;
    margin: 0 auto;
}
</style>
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                    <div class="box-head recom-head" style="margin-bottom: 30px;">
                        <h3>Admin products</h3>
                    </div>

  
                    @if($adminproduct && count($adminproduct) > 0)

                        <div class="recommend-list row userproduct_new admin_product">

                            @foreach($adminproduct as $product)
                      
                             <?php $datas =  explode(".",$product->image);
                

                  ?>
                 
   <div class=" col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                     <div class="slide-box">
                                    <div class="slide-image">
                                        <a href="{{route('user.product_preview',$product->id)}}">
                                         @if($product->type='seller_token')
                   <img src="{{asset('/seller_token.png')}}" >
                 <!--  <img src="{{url('/seller_token.png')}}"> -->
                  @else
 @if(@$datas[1]=="img" || @$datas[1]=="png" || @$datas[1]=="jpg" || @$datas[1]=="JPG" || @$datas[1]=="gif" || @$datas[1]=="JPEG" )

   
                <img src="{{asset('uploads/product')}}/{{$product->image}}" data-src="{{asset('uploads/product')}}/{{$product->image}}" class="slide-img1 placeholder">
                    
                  @else
                 <video controls="" id="thumb"  class="" src="{{asset('uploads/product')}}/{{$product->image}}"></video>
                  @endif
                  @endif

                                        </a>
                                    </div><!--end of slide-image-->



                                    <div class="video-details recom-details">
                                       
                                             <?php  $name = substr($product->name,0,20) . '...';  ?>
                   <a href="{{route('user.product_preview',$product->id)}}" title="Preview" style="color: #337ab7;">
                          @if($product->type='seller_token')
                          TipMe Credit
                          @else
                       <h4>{{ $name }}</h4>@endif
                       </a>                                    
                                                <p>by Admin</p>
                                                <p><span class="product_price">$ {{$product->price}} </span></p>
                                                <a href="javascript:void(0)" class="btn_user_product" title="Add to cart"> <button class="btn btn-default product_submit" id="{{$product->id}}" style="">Add Cart</button> </a>
                                  
                                    </div><!--end of video-details-->



                                </div>
                                </div>



                                 
                            @endforeach
                        </div>
                    @else

                         <div class="recommend-list row">
                            <div class="slide-box recom-box">No product found</div>
                        </div>

                    @endif

                    <!--end of recommend-list-->

                     @if(count($adminproduct) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div align="center" id="paglink"><?php echo $adminproduct->links(); ?></div>
                            </div>
                        </div>

                    @endif
                </div>

                <!--end of slide-area-->

                <div class="sidebar-back"></div> 
            </div>

        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(".product_submit").click(function(){
            var cart = $(".my_cart").text();

            var id  =  $(this).attr("id");
              $.ajax({
                url : "{{url('add_to_cart')}}/"+id,
               
                success:function(data){

                  

                    $(".my_cart").text(data);

                    
                 
                },
                error:function(){
                  console.log("unable to send");
                }
              });
            
         });
    </script>

@endsection