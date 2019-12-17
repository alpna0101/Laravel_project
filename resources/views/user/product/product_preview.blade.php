@extends('layouts.user')


@section('styles')

<link rel="stylesheet" href="{{asset('assets/css/star-rating.css')}}">
<style type="text/css">
.main_img {
    text-align: center;
    border: 1px solid #ddd;
    padding: 20px;
    margin-top: 25px;
}

.product_discription i {
    color: #f0c14b;
    font-size: 20px;
}

.product_discription p a {
    margin-left: 40px;
    text-decoration: underline;
}
.product_discription h4 {
    margin: 15px 0;
    font-size: 18px;
}
.product_discription h4 span {
    color: #b12704;
}
#preview{
  height: 300px;  
}
.main_img img 
{
    height: 300px;
    
}
.buy_now {
    background: #2e93e0;
    border: none;
    color: #fff;
    padding: 10px 25px;
    border-radius: 6px;
    font-size: 18px;
    line-height: 25px;
}
.product_img ul li {

    display: inline-block;
    border: 1px solid #ddd;
    padding: 5px;
    margin: 20px 10px 0px 0px;
    text-align: center;
    float: left;
    width: 23%;

}
.product_img ul li:last-child{
  margin-right: 0px;
}
.product_img ul li img {
    height: 100px;
}
.product_img ul .active {border: 1px solid #2e93e0;}

.review i {
    color: #f0c14b;
}
.review hr
{
    margin-top: 50px;
}
.send 
{
    background: #2e93e0;
    border: none;
    color: #fff;
    font-size: 17px;
    padding: 6px 15px;
    border-radius: 4px;
    margin-top: 20px;
    float: right;
}
.jack_b
 {
    border-bottom: 2px solid #ddd;
    padding-top: 30px;
}
.jack_img 
{
    float: left;
    width: 100%;
    margin-top: 20px;
}
.user_im {
    border-radius: 100%;
    height: 90px;
    width: 90px;
    float: left;
}
.jack_tx
 {
    padding-left: 140px;
}
.jack_b p {
    padding: 30px 0;
    display: inline-block;
}
     .magnifier-thumb-wrapper {
    position: relative;
    display: block;
    top: 0;
    left: 0
}

.magnifier-lens {
    position: absolute;
    border: solid 1px #ccc;
    z-index: 1000;
    top: 0;
    left: 0;
    overflow: hidden
}

.magnifier-loader {
    position: absolute;
    top: 0;
    left: 0;
    border: solid 1px #ccc;
    color: #fff;
    text-align: center;
    background: transparent;
    background: rgba(50, 50, 50, 0.5);
    z-index: 1000;
    -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#7F323232,endColorstr=#7F323232)";
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#7F323232,endColorstr=#7F323232)
}

.magnifier-loader-text {
    font: 13px Arial;
    margin-top: 10px
}

.magnifier-large {
    position: absolute;
    z-index: 100
}

.magnifier-preview {
    padding: 0;
    width: 100%;
    height: 150px;
    position: relative;
    overflow: hidden
}

.magnifier-preview img {
    position: absolute;
    top: 0;
    left: 0
}

.opaque {
    opacity: .5;
    filter: alpha(opacity=50);
    -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=50)
}

.hidden {
    display: none
}
.product_discription {
    margin-top: 20px;
}
.product_discription h2 {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 0px;
}
.product_discription h3 {
    font-size: 18px;
    font-weight: bold;
}
.product_discription p {
    min-height: 130px;
    margin-bottom: 20px;
    line-height: 23px;
    overflow-y: auto;
}
.product_img ul {

    float: left;
    width: 100%;
    padding: 0px;

}
.product_img ul li video, .product_img ul li img {

    width: 100%;

}
.upload_fileicon {
    position: absolute;
    right: 0px;
    top: 39px;
    font-size: 19px;
    
}
.upload_fileicon .btn{
background-color: #fdc20f;
    color: #fff;
    padding: 5px 10px;
    width: 35px;
    height: 35px;
  }
.fileinputs {
    position: relative;
}
.upload_fileicon a {
    color: #000;
    font-weight: bold;
}
.fileinputs textarea#comment:focus, .fileinputs textarea#comment {
    margin-top: 30px;
    border-bottom: 1px solid #ccc!important;
    padding-right: 47px;
}
.comment_img{
  position: relative;
    float: left;
    width:  200px;
    height: 200px;
    background-position: 50% 50%;
    background-repeat:   no-repeat;
    background-size:     cover;
}
.app {
  .particles {
    position: fixed;
    top: 0;

    .particle {
      position: absolute;
      transition: all 5s ease-out;
    }
  }
  
  .button {
    position: fixed;
    left: 50%;
    top: 50%;
    margin-top: -20px;
    margin-left: -100px;
    height: 40px;
    width: 200px;
    font-family: Roboto;
    background-color: #34495e;
    color: white;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s ease-out;
    
    &:hover {
      background-color: #8e44ad;
    }
    
    .popper {
      margin-right: 20px;
      margin-left: -20px;
      width: 64px;
      height: 64px;
      background-image: url('data:image/png;base64,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');
    }
  }
}

@media screen and (max-width: 480px) {
.jack_tx h4 {
    font-size: 16px;
}
.jack_tx
 {
    padding-left: 100px;
}
}
    </style>
    @endsection
  
    @section('content')
    <div class="y-content product_firework">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                  <div class="box-head recom-head">
                        <h3 >My Products</h3>
                       
                    </div>
                    <?php $datas =  explode(".",$data->image);

           
            ?>
                   <section class="product">
         <div class="container-fluid">
            <div class="row">
               <div class="col-sm-6">
                  <div class="product_img">
                     <div class="main_img">
                       <a class="magnifier-thumb-wrapper magnifyimg thumb" href="{{asset('uploads/product')}}/{{$data->image}}" target="_blank">
 @if(@$datas[1]=="img" || @$datas[1]=="png" || @$datas[1]=="jpg"  ||  @$datas[1]=="JPG"  || @$datas[1]=="gif" || @$datas[1]=="JPEG" )
        <img id="thumb" src="{{asset('uploads/product')}}/{{$data->image}}"
        data-large-img-url=""
        data-large-img-wrapper="preview" >
        @else
     <video controls="" id="thumb"  class="" src="{{asset('uploads/product')}}/{{$data->image}}" width="200" height="180"></video>
                  @endif
    </a>
    <a class="magnifier-thumb-wrapper magnifyimg thumb0" href="{{asset('uploads/product')}}/{{$data->image}}" target="_blank">
        <img id="thumb0" src=""
        data-large-img-url=""
        data-large-img-wrapper="preview" >
    </a>
        <a class="magnifier-thumb-wrapper magnifyimg thumb1" href="{{asset('uploads/product')}}/{{$data->image}}" target="_blank">
        <img id="thumb1" src=""
        data-large-img-url=""
        data-large-img-wrapper="preview" >
       </a>
    <a class="magnifier-thumb-wrapper magnifyimg thumb2" href="{{asset('uploads/product')}}/{{$data->image}}" target="_blank">
        <img id="thumb2" src=""
        data-large-img-url=""
        data-large-img-wrapper="preview" >
    </a>
    
                        <!-- <img src="images/1.jpg"> -->
                     </div>

<ul>
 <li>
@if(@$datas[1]=="img" || @$datas[1]=="png" || @$datas[1]=="jpg"  ||  @$datas[1]=="JPG"  || @$datas[1]=="gif" || @$datas[1]=="JPEG" ) 
<img class="imgage" id="4" src="{{asset('uploads/product')}}/{{$data->image}}">

@else
     <video id="4"  class="imgage" class="" src="{{asset('uploads/product')}}/{{$data->image}}" height="100"></video>
                  @endif

 </li>
               <?php if($data->getImages && !empty($data->getImages) && count($data->getImages) > 0){
                $count = count($data->getImages);
                 
                for($i = 0; $i < $count; $i++){
                   $datas1 =  explode(".",$data->getImages[$i]->image);

           

           
                ?>

               <li >@if(@$datas1[1]=="img" || @$datas1[1]=="png" || @$datas1[1]=="jpg"  || @$datas1[1]=="JPG"  || @$datas1[1]=="gif" || @$datas1[1]=="JPEG" ) <img class="imgage"  id = "{{$i}}" src="{{asset('/uploads/product')}}/{{$data->getImages[$i]->image}}">@else
     <video id="{{$i}}" class="imgage"  src="{{asset('uploads/product')}}/{{$data->getImages[$i]->image}}"  height="100"></video>
                  @endif</li>
                       
              <?php } }  ?>
    </ul>
                  </div>
              
               </div>
               <div class="col-sm-6 product_discription">
                  <h2>{{$data->name}} @if(@$wishlist->wish == 1) <span class="wish" id="{{$data->id}}"><i class="fa fa-heart" aria-hidden="true" style="color:#b31217;cursor: pointer;"></i></span> @else <span class="wish" id="{{$data->id}}"><i class="fa fa-heart" aria-hidden="true" style="color:#999;cursor: pointer;"></i></span>@endif</h2>
                  
                <h5><input id="view_rating" name="rating" type="number" class="rating view_rating" min="1" max="5" step="1" value="{{$average}}"> @if(count($comments) > 0) <a href="#all_comments" class="nav-link"> ({{count($comments)}} customer reviews)</a>@endif
                  </h5>
            
                  <h4><span>$ {{$data->price}}</span></h4>
                  <h3>Description:</h3>
                  <p>{{$data->description}}</p>
        
            @if(@Auth::user()->id != $data->user_id)
                  <div class="btn_descrptn">
                   <a href="javascript:void(0)" class="add_to_cart" title="Add to cart" > <button class="btn product_submit" id="{{$data->id}}"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
                   Add Cart</button> </a>
        <a href="#" class="buy_a"><button class="buy_now">Buy Now</button></a> 
        </div>
       @endif
     

         <div class="magnifier-preview" id="preview" style="margin-top: 30px;"></div>
         </div>
             <div class="v-comments">
                           <div class="pull-left">
                                @if(count($comments) > 0)
                              <h3 class="mb-15"><span class="c-380" id="comment_count">{{count($comments)}}</span>&nbsp;Comments</h3>
                              @endif
                                                         </div>
                           <div class="clearfix"></div>
                            
                           <!-- <p class="small mb-15">Note : Rating can give only one time to each User</p> -->
                                            <div class="com-content">
                                                                                      
                                          @if(Auth::check())
                                          @if(Auth::user()->id != $data->user_id && $can_comment== true)
                                 <div class="image-form">
                                 <div class="comment-box1">
                                    <div class="com-image">
                                       <img style="width:50px;height:50px; border-radius:25px;object-fit: cover;object-position: center;" src="{{Auth::user()->picture}}">                                    
                                    </div>

                                    <!--end od com-image-->
                                           <div id="comment_form">
                                       <div>
                                          <form method="post" id="comment_sent" name="comment_sent" action="{{route('user.save_rating_comment')}}" enctype="multipart/form-data">
                                             <input type="hidden" value="{{$data->id}}" name="product_id">
                                           
                                             @if($comment_rating_status)
                                             <input id="rating_system" name="ratings" type="number" class="rating comment_rating" min="1" max="5" step="1">
                                             @endif
                                             <div class="fileinputs">


                                             <textarea rows="10" id="comment" name="comments" placeholder="{{tr('add_comment_msg')}}"></textarea>
                                          <div class="upload_fileicon">
                                       <label class="btn upload_btn">

                                   <i class="fa fa-paperclip" aria-hidden="true"></i><input type="file" class="uploadFile img" value="Upload Photo" name = "media" style="width: 0px;height: 0px;overflow: hidden;" title="select file">
                                      </label>
                                      </div>                         </div>
                                             <p class="underline"></p>
                                             <button class="btn pull-right btn-sm btn-info btn-lg top-btn-space"  type="submit" id="comment_btn" >{{tr('comment')}}</button>
                                             <div class="clearfix"></div>
                                          </form>
                                       </div>
                                    </div>
                                    <!--end of comment-form-->
                                 </div>
                              </div>
                              @endif
                              @endif
                                 @if(count($comments) > 0)
                              <div class="feed-comment" id="all_comments">
                                 <span id="new-comment"></span>
                                 @foreach($comments as $c =>  $comment)
                                 <div class="display-com">
                                    <div class="com-image">
                                       <img style="width:50px;height:50px; border-radius:25px;object-fit: cover;object-position: center;" src="{{$comment->picture}}">                                    
                                    </div>
                                    <!--end od com-image-->
                                    <div class="display-comhead">
                                       <span class="sub-comhead">
                                          <a>
                                             <h5 style="float:left">{{$comment->username}}</h5>
                                          </a>
                                          <a class="text-none">
                                             <p>{{\Carbon\Carbon::parse($comment->created_at)->diffForHumans()}}</p>
                                          </a>
                                          <p><input id="view_rating" name="rating" type="number" class="rating view_rating" min="1" max="5" step="1" value="{{$comment->rating}}"></p>
                                         <?php  
                                        if($comment->media!=""){

                                         $datas1 =  explode(".",$comment->media);

           

           
                ?>
  <div class="row">
                                <div class="col-sm-3">
               <p>@if(@$datas1[1]=="img" || @$datas1[1]=="png" || @$datas1[1]=="jpg"  || @$datas1[1]=="JPG"  || @$datas1[1]=="gif" || @$datas1[1]=="JPEG" ) <img src="{{asset('/uploads/product')}}/{{$comment->media}}" class="comment_img">@else
                 <video  controls=""  src="{{asset('uploads/product')}}/{{$comment->media}}"  class="comment_img"></video>
                  @endif</p></div></div>
                  <?php }?>
                                          <p class="com-para">{{$comment->comment}}</p>
                                       </span>
                                    </div>
                                    <!--display-comhead-->                                        
                                 </div>
                                 <!--display-com-->
                                 @endforeach
                              </div>
                              @else
                              <div class="feed-comment">
                                 <span id="new-comment"></span>
                              </div>
                              <!-- <p>{{tr('no_comments')}}</p> -->
                              @endif
                                
                                                         </div>
                        </div>
      </section>
     
  

               
 
                </div>
            </div>

        </div>
    </div>

@endsection
 @section('scripts')
       <script type="text/javascript" src="{{asset('streamtube/js/event.js')}}"></script>
       <script type="text/javascript" src="{{asset('streamtube/js/magnify.js')}}"></script>

<script type="text/javascript" src="{{asset('assets/js/star-rating.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/toast.script.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.2.0/anime.min.js"

        integrity="sha384-BnFYVbw3PHhz5qWXTCSL12MjPc3KxjdKPx7R4R5JjIzxFmYX267NDyJ9B/nZANdg"

        crossorigin="anonymous">

</script>

<script type="text/javascript">
   $(document).ready(function(){
   
 
    $(".heart").on('click touchstart', function(){
      $(this).toggleClass('is_animating');
    });
   
    $(".heart").on('animationend', function(){
      $(this).toggleClass('is_animating');
    });
   });
</script>


<!-- wishlist animation -->
<script type="text/javascript">
    function throwConfetti() {
     $('.product_firework').fireworks({ sound: true, opacity: 0.9, width: '100%', height: '100%' });

 setInterval(function() {
      $("canvas").hide();
    }, 10000);
    // confetti.maxCount = Math.random() * 100 + 50;
    // confetti.start();
    // setInterval(function() {
    //   confetti.stop();
    // }, 1200);
  }
   
   $(document).ready(function(){
       $('.video-y-menu').addClass('hidden');
   }); 
   
 
   $('.view_rating').rating({disabled: true, showClear: false});
   
   $('.comment_rating').rating({showClear: false});
   
   $(document).on('ready', function() {
       $("#copy-embed1").on( "click", function() {
           $('#popup1').modal('hide'); 
       });
   });
   
   
   jQuery(document).ready(function(){ 
   
       // Opera 8.0+
       var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
       // Firefox 1.0+
       var isFirefox = typeof InstallTrigger !== 'undefined';
       // At least Safari 3+: "[object HTMLElementConstructor]"
       var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
       // Internet Explorer 6-11
       var isIE = /*@cc_on!@*/false || !!document.documentMode;
       // Edge 20+
       var isEdge = !isIE && !!window.StyleMedia;
       // Chrome 1+
       var isChrome = !!window.chrome && !!window.chrome.webstore;
       // Blink engine detection
       var isBlink = (isChrome || isOpera) && !!window.CSS;
   
   
       
   
       $('#comment').keydown(function(event) {
           if (event.keyCode == 13) {
               $(this.form).submit()
               return false;
           }
       }).focus(function(){
           if(this.value == "Write your comment here..."){
               this.value = "";
           }
       }).blur(function(){
           if(this.value==""){
               this.value = "";
           }
       });

//        $('.uploadFile').change(function(){    
//     var form = document.getElementById('comment_sent');
//       var formData = new FormData(form);
//     if($(this).prop('files').length > 0)
//     {
//         file =$(this).prop('files')[0];
//         console.log(file);
//         formData.append("media1", file);
//     }
// });
   

       jQuery("form[name='comment_sent']").submit(function(e) {
            var media = "";
          var form = document.getElementById('comment_sent');
      var formData = new FormData(form);
           //prevent Default functionality
           e.preventDefault();
   
 
   
           //get the action-url of the form
           var actionurl = e.currentTarget.action;
   
           var form_data = $.trim(jQuery("#comment").val());
   
           if(form_data) {
   
               $("#comment_btn").html("Sending...");
   
               $("#comment_btn").attr('disabled', true);
   
              var my_point = $(".my_point").text();
               //do your own request an handle the results
               jQuery.ajax({
                   url: actionurl,
                   type: 'post',
                   dataType: 'json',
                  data: new FormData(this),
                  contentType: false,
                  cache: false,
                  processData:false,
                   success: function(data) {

   
                       $("#comment_btn").html("Comment");
   
                       $("#comment_btn").attr('disabled', false);
                       console.log(data);
                  
   
                           @if(Auth::check())
                               jQuery('#comment').val("");
                               jQuery('.uploadFile').val("");

                               jQuery('#no_comment').hide();
                               var comment_count = 0;
                               var count = 0;
                               comment_count = jQuery('#comment_count').text();
                               var count = parseInt(comment_count) + 1;
                               jQuery('#comment_count').text(count);
                               jQuery('#video_comment_count').text(count);
                               
                                 giftimagecom
                                $(".points-btn").html('<span class="my_point">'+data.total_points+"</span> TipMe");
                                if(data.total_points>my_point){

                                  throwConfetti();
                                }
                               // var stars = 0;
   
                               var first_star = data.comment.rating >= 1 ? "color:#ff0000" : "";
   
                               var second_star = data.comment.rating >= 2 ? "color:#ff0000" : "";
   
                               var third_star = data.comment.rating >= 3 ? "color:#ff0000" : "";
   
                               var fourth_star = data.comment.rating >= 4 ? "color:#ff0000" : "";
   
                               var fifth_star = data.comment.rating >= 5 ? "color:#ff0000" : "";
   
                               var stars = '<span class="stars">'+
                               '<a><i style="'+first_star+'" class="fa fa-star-o comment-stars" aria-hidden="true"></i></a>'+
                               '<a><i style="'+second_star+'" class="fa fa-star-o comment-stars" aria-hidden="true"></i></a>'+
                               '<a><i style="'+third_star+'" class="fa fa-star-o comment-stars" aria-hidden="true"></i></a>'+
                               '<a><i style="'+fourth_star+'" class="fa fa-star-o comment-stars" aria-hidden="true"></i></a>'+
                               '<a><i style="'+fifth_star+'" class="fa fa-star-o comment-stars" aria-hidden="true"></i></a></span>';   
   
                               /**
                               <p><input id="view_rating" name="rating" type="number" class="rating view_rating" min="1" max="5" step="1" value="'+data.comment.rating+'"></p>
                               **/
   
                               if (data.comment.rating > 1) {
   
                               $('.comment_rating').rating('clear');
   
                               window.location.reload();
   
                               }
                           
                                if(data.comment.media!=""){
                                 
                                         var arr  = data.comment.media.split(".");
                                        var img =' {{asset("uploads/product")}}/'+data.comment.media;
                                 
                                if(arr[1]=="png" || arr[1]=="jpg" || arr[1]=="gif" || arr[1]=="JPEG" || arr[1]=="JPG")
                                {
                                   media = '<div class="row"><div class="col-sm-3"><p><img src='+img+' class="comment_img" ></p></div></div>';
                                }else{
                                  media = '<div class="row"><div class="col-sm-3"><p><video controls="" class="comment_img" src='+img+' height="100"></video></p></div></div>';
                                }

                                }
                               jQuery('#new-comment').prepend('<div class="display-com"><div class="com-image"><img style="width:48px;height:48px;  border-radius:24px;" src="{{Auth::user()->picture}}"></div><div class="display-comhead"><span class="sub-comhead"><a><h5 style="float:left">{{Auth::user()->name}}</h5></a><a><p>'+data.date+'</p></a><p>'+stars+'</p> '+media+'<p class="com-para">'+data.comment.comment+'</p></span></div></div>');
                           @endif
                     
                   }
               });
           } else {
   
               alert("Please fill the comment field");
   
               return false;
   
           }
   
       });
   
       
   
         }); 
   
  
   

</script>

  
<script type="text/javascript">








$(document).ready(function(){
function giftimagecom(){
       $('.gif_commt').fadeToggle("slow");
        setInterval(function() {
      giftimagehidecom();
    }, 2000);
    }
     function giftimagehidecom(){
       $('.gif_commt').css("display","none");
    }
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
  
      
   $(".wish").click(function(){



  
      @if(Auth::check())
       var id = $(this).attr('id');
        $.ajax({
                url : "{{url('add_to_wishlist')}}/"+id,
               
                success:function(data){
                console.log(data.wish);
                  

                      if(data.wish == true) {
                        console.log("#"+data.product_id+ " i");
                        jQuery("#"+data.product_id+ " i").css({'background-color':'transparent','color' : '#b31217 !important'});
   
                           // jQuery("#added_wishlist").css({'font-family':'arial','background-color':'transparent','color' : '#b31217'});
   
                           // if (jQuery(window).width() > 640) {
                           // var append = '<i class="fa fa-heart">';
                           // // var append = '<i class="fa fa-times-circle">&nbsp;&nbsp;{{tr('wishlist')}}';
                           // } else {
                           // var append = '<i class="fa fa-heart">';
                           // }
                           // jQuery("#added_wishlist").append(append);
   
                       } else {
                   jQuery("#"+data.product_id+ " i").css({'background-color':'transparent','color' : '#999 !important'});
                           // jQuery('#status').val("1");
                           // jQuery('#wishlist_id').val("");
                           // jQuery("#added_wishlist").css({'font-family':'arial','background':'','color' : ''});
                           // if (jQuery(window).width() > 640) {
                           // var append = '<i class="fa fa-heart">';
                           // // var append = '<i class="fa fa-plus-circle">&nbsp;&nbsp;{{tr('wishlist')}}';
                           // } else {
                           // var append = '<i class="fa fa-heart">';
                           // }
   
                           // jQuery("#added_wishlist").append(append);
   
                       }

                    
                 
                },
                error:function(){
                  console.log("unable to send");
                }
              });
        @else
          var url = '{{url("/login")}}';
      window.location.href = url;
      @endif
   });


  var evt = new Event(),
    m = new Magnifier(evt);
// $(".magnifyimg").hide();
// $(".thumb").hide();
 $(".magnifyimg").attr('style', 'display:none');
 $(".thumb").attr('style', 'display:block');
 $('video.imgage').on('click', function () {
     
  var img  = $(this).attr("src");

 });
$(document).on("click",".imgage", function(){
  var img  = $(this).attr("src");
  var arr1 = img.split("{{url('/')}}");
  var arr  = arr1[1].split(".");
  var id  = $(this).attr("id");

  if(id!=4){
        console.log(arr[1]);
    if(arr[1]=="png" || arr[1]=="jpg" || arr[1]=="gif" || arr[1]=="JPEG" || arr[1]=="JPG")
    {
      console.log(arr[1]);
    var t = "thumb"+id;
    
     $("#thumb-large").hide();
     $("#thumb0-large").hide();
     $("#thumb1-large").hide();
     $("#thumb2-large").hide();
     $("#thumb"+id+"-large").show();
     $("#"+t).show();
     $(".magnifyimg").hide();
     $(".thumb"+id).show();
     $("#thumb"+id).attr('src',img);
     $("#thumb"+id).attr('data-large-img-url',img);
     $(".magnifier-thumb-wrapper").attr('href',img);
  // var evt = new Event(),
  //   m = new Magnifier(evt);
var evt = new Event(),
    m = new Magnifier(evt);
m.attach({
    thumb: '#thumb'+id,
    large: img,
    largeWrapper: 'preview',
    zoom: 3
    });
  }else{
   
     var source = document.createElement('video'); //added now

       source.width = 400;

       source.height = 400;

      source.controls = true;

      source.src = img;

  $("#thumb-large").hide();
     $("#thumb0-large").hide();
     $("#thumb1-large").hide();
     $("#thumb2-large").hide();
     $("#thumb"+id+"-large").show();
     $("#"+t).show();
     $(".magnifyimg").hide();
     $(".thumb"+id).show();
     $(".thumb"+id).html(source);
     $("#thumb"+id).attr('data-large-img-url',img);
     $(".magnifier-thumb-wrapper").attr('href',img);
    // $(".thumb").append(source);
  
  }
  }else{
   if(arr[1]=="png" || arr[1]=="jpg" || arr[1]=="gif" || arr[1]=="JPEG" || arr[1]=="JPG")
    {
    console.log( $(".thumb").attr('src'));
     var img  = $(this).attr("src");

    $("#thumb-large").show();
     $("#thumb-large").attr('src',img);
     $("#thumb0-large").hide();
     $("#thumb1-large").hide();
     $("#thumb2-large").hide();
     $("#thumb").show();
     $(".magnifyimg").hide();
    $(".thumb").attr('style', 'display:block');
    $(".thumb").show();
    $("#thumb").attr('src',img);
    $("#thumb").attr('data-large-img-url',img);
     $(".magnifier-thumb-wrapper").attr('href',img);
  var evt = new Event(),
    m = new Magnifier(evt);
  m.attach({
    thumb: '#thumb',
    large: img,
    largeWrapper: 'preview',
    zoom: 3
});
}else{
var source = document.createElement('video'); //added now

       source.width = 400;

       source.height = 400;

      source.controls = true;

      source.src = img;

  $("#thumb-large").show();
     $("#thumb-large").attr('src',img);
     $("#thumb0-large").hide();
     $("#thumb1-large").hide();
     $("#thumb2-large").hide();
     $("#thumb").show();
     $(".magnifyimg").hide();
    $(".thumb").attr('style', 'display:block');
    $(".thumb").show();
   
     $(".thumb").html(source);
     $("#thumb").attr('data-large-img-url',img);
     $(".magnifier-thumb-wrapper").attr('href',img);

}
}

  });
var evt = new Event(),
    m = new Magnifier(evt);
m.attach({
    thumb: '#thumb',
    large: "{{asset('uploads/product')}}/{{$data->image}}",
    largeWrapper: 'preview',
    zoom: 3
});
})

</script>
@endsection