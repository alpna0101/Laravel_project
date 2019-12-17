@extends('layouts.user')

@section('content')
@section('styles')
<style>
.container1 {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}


.container1 input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 25px;
  width: 25px;
  background-color: #eee;
  border-radius: 50%;
}

.container1:hover input ~ .checkmark {
  background-color: #ccc;
}

.container1 input:checked ~ .checkmark {
  background-color: #fdc20f;
}


.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}


.container1 input:checked ~ .checkmark:after {
  display: block;
}

.container1 .checkmark:after {
    top: 9px;
    left: 9px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: white;
}
.radio_top_button ul{
    padding: 0px;
    margin: 0px;
    list-style-type: none;
}
.radio_top_button ul li {
    display: inline-block;
    float: left;
    margin-right:30px;
}
.radio_top_button {
    margin-bottom: 60px;
}


</style>
@endsection
<div class="y-content">
    <div class="row content-row">

        @include('layouts.user.nav')

        <div class="history-content page-inner col-sm-9 col-md-10">
            
            <div class="slide-area1">
            
                @include('notification.notify')

                <div class="new-history">
                    <div class="content-head">
                        <div><h4 class="bold no-margin-top">{{tr('wishlist')}}</h4></div>              
                    </div><!--end of content-head-->
                    <div class="row">
                   <div class="radio_top_button">
                    <ul>
                    <li>
                    <label class="container1">Videos
                    @if(!@$_GET['action'])
                    <input type="radio" id="video" name="radio-group" checked="">
                    @else
                    <input type="radio" id="video" name="radio-group">
                    @endif
                    <span class="checkmark"></span>
                    </label>
                    </li>
                    <li>
                    <label class="container1">Products
                    @if(@$_GET['action']=="product")
                    <input type="radio" id="product" name="radio-group" checked="">
                    @else
                    <input type="radio" id="product" name="radio-group" >
                    @endif
                    <span class="checkmark"></span>
                    </label>
                    </li>
                    </ul>
          </div>
        </div>

         @if(@$_GET['action']=="product")


                <ul class="history-list">
                     @if(@$products)
                            @foreach($products as $i => $data)

                            <li class="sub-list row">
                                <div class="main-history">
                                     <div class="history-image">
                                        <a href="{{route('user.product_preview',$data->id)}}">
                                           
                                            <img src="{{asset('uploads/product')}}/{{$data->image}}" data-src="{{asset('uploads/product')}}/{{$data->image}}" class="slide-img1 placeholder" />
                                        </a>
                                        
                                                                
                                    </div><!--history-image-->
                                    <div class="history-title">
                                        <div class="history-head row">
                                            <div class="cross-title1">
                                                <h5><a href="{{route('user.product_preview',$data->id)}}">{{$data->name}}</a></h5>
                                                <span class="video_views">
                                                    <div><a href="{{route('user.product_preview',$data->id)}}">{{$data->description}}</a></div>
                                                  
                                                </span> 
                                            </div> 
                                            <div class="cross-mark1">
                                                <a onclick="return confirm('Are you sure?');" class="wish" id="{{$data->id}}"><i class="fa fa-times" aria-hidden="true"></i></a>
                                            </div><!--end of cross-mark-->                       
                                        </div> <!--end of history-head--> 

                                        <div class="description">
                                           {{$data->description}}
                                        </div><!--end of description--> 

                                        <span class="stars">
                                            <a><i @if($data->ratings_average >= 1) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($data->ratings_average >= 2) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($data->ratings_average >= 3) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($data->ratings_average >= 4) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($data->ratings_average >= 5) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                        </span>                                          
                                    </div><!--end of history-title--> 
                                    
                                </div><!--end of main-history-->
                            </li>    

                            @endforeach
                            @else

                            <img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">
                            @endif
                           
                        </ul>


                     @if(count($products) > 0)
                  
                        @if($products)
                        <div class="row">
                            <div class="col-md-12">
                                <div align="center" id="paglink"><?php echo $products->links(); ?></div>
                            </div>
                        </div>
                        @endif
                    @endif
         @else
                    @if(count($videos->items) > 0)

                        <ul class="history-list">

                            @foreach($videos->items as $i => $video)

                            <li class="sub-list row">
                                <div class="main-history">
                                     <div class="history-image">
                                        <a href="{{$video->url}}">
                                            <!-- <img src="{{$video->video_image}}"> -->
                                            <img src="{{asset('streamtube/images/placeholder.gif')}}" data-src="{{$video->video_image}}" class="slide-img1 placeholder" />
                                        </a>
                                        @if($video->ppv_amount > 0)
                                            @if(!$video->ppv_status)
                                                <div class="video_amount">

                                                {{tr('pay')}} - {{Setting::get('currency')}}{{$video->ppv_amount}}

                                                </div>
                                            @endif
                                        @endif
                                        <div class="video_duration">
                                            {{$video->duration}}
                                        </div>                        
                                    </div><!--history-image-->
                                    <div class="history-title">
                                        <div class="history-head row">
                                            <div class="cross-title1">
                                                <h5><a href="{{$video->url}}">{{$video->title}}</a></h5>
                                                <span class="video_views">
                                                    <div><a href="{{route('user.channel',$video->channel_id)}}">{{$video->channel_name}}</a></div>
                                                    <i class="fa fa-eye"></i> {{$video->watch_count}} {{tr('views')}} <b>.</b> 
                                                    {{$video->created_at}}
                                                </span> 
                                            </div> 
                                            <div class="cross-mark1">
                                                <a onclick="return confirm('Are you sure?');" href="{{route('user.delete.wishlist' , array('video_tape_id' => $video->video_tape_id))}}"><i class="fa fa-times" aria-hidden="true"></i></a>
                                            </div><!--end of cross-mark-->                       
                                        </div> <!--end of history-head--> 

                                        <div class="description">
                                            <?= $video->description ?>
                                        </div><!--end of description--> 

                                        <span class="stars">
                                            <a><i @if($video->ratings >= 1) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($video->ratings >= 2) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($video->ratings >= 3) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($video->ratings >= 4) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                            <a><i @if($video->ratings >= 5) style="color:#ff0000" @endif class="fa fa-star" aria-hidden="true"></i></a>
                                        </span>                                          
                                    </div><!--end of history-title--> 
                                    
                                </div><!--end of main-history-->
                            </li>    

                            @endforeach
                           
                        </ul>

                    @else
                       <!--  <p>{{tr('no_wishlist_found')}}</p> -->
                       <img src="{{asset('images/no-result.jpg')}}" class="img-responsive auto-margin">
                    @endif

                    @if(count($videos->items) > 0)

                        @if($videos->items)
                        <div class="row">
                            <div class="col-md-12">
                                <div align="center" id="paglink"><?php echo $videos->pagination; ?></div>
                            </div>
                        </div>
                        @endif
                    @endif
             @endif
                </div>
                <div class="sidebar-back"></div> 
                
            </div>

        </div>

    </div>
</div>

@endsection
@section('scripts')
   
<script>
$('document').ready(function() {
    $("#product").click(function(){
      var url = "{{url('wishlist')}}?action=product";
      window.location.href = url;
    });
    $("#video").click(function(){
      var url = "{{url('wishlist')}}";
      window.location.href = url;
    });
    $(".wish").click(function(){
       var id = $(this).attr('id');
        $.ajax({
                url : "{{url('add_to_wishlist')}}/"+id,
               
                success:function(data){
                     var url = "{{url('wishlist')}}?action=product";
                  window.location.href = url;
                    console.log("success")
                }
            });
          });
});
</script>
    @endsection