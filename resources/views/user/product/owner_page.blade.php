@extends('layouts.user')

@section('content')
<link href="{{asset('streamtube/css/nanogallery2.min.css')}}" rel="stylesheet" type="text/css">

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

</style>
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                    <div class="box-head recom-head" style="margin-bottom: 30px;">
                        <h3>CJC Owners</h3>
                    </div>


                    @if($owners && count($owners) > 0)
<?php $fileUrl = asset('/uploads/owner')."/"; ?>

                    <div id="ngy2p" data-nanogallery2='{
                        "itemsBaseURL": "{{$fileUrl}}",
                        "thumbnailWidth": "auto",
                        "thumbnailHeight": "300",
                        "thumbnailLabel": {
                          "display": false
                        },
                        "thumbnailHoverEffect2": "imageScale150",
                        "galleryDisplayMode": "moreButton",
                       
                        "thumbnailAlignment": "center",
                        "thumbnailGutterWidth": 20,
                        "thumbnailGutterHeight": 20
                      }'>
                      @foreach($owners as $own)
                      <a href="{{$own->image}}" data-ngthumb="{{$own->image}}" data-ngdesc=""></a>
                      @endforeach
                    </div>

                    <!--     <div class="recommend-list row userproduct_new">

                            @foreach($owners as $own)
                             
                                    <div class=" col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                     <div class="slide-box">
                                  <!--   <div class="slide-image"> -->
                                     

                                          <!--   <img src="{{asset('uploads/owner')}}/{{$own->image}}" data-src="{{asset('uploads/owner')}}/{{$own->image}}" class="slide-img1 placeholder">
 -->
                                       
                                <!--     </div> -->



                                <!--     <div class="video-details recom-details">
                                       
                                          
                                  
                                    </div> -->



                                <!-- </div>
                                </div>
                            @endforeach
                        </div> -->
                    @else

                         <div class="recommend-list row">
                            <div class="slide-box recom-box">No product found</div>
                        </div>

                    @endif

                    <!--end of recommend-list-->

                     
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