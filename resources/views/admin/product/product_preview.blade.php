@extends('layouts.user')


@section('styles')
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
.product_discription h4 
{
    margin: 38px 0 30px 0;
    font-size: 24px;
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
.buy_now 
{
    background: #2e93e0;
    border: none;
    color: #fff;
    padding: 12px 25px;
    border-radius: 4px;
    font-size: 18px;
    margin-top: 40px;
}
ul li {
    display: inline-block;
    border: 1px solid #ddd;
    padding: 5px;
    margin: 30px 25px 0px 0px;
    text-align: center;
}
ul li img {
    height: 100px;
}
ul .active {border: 1px solid #2e93e0;}

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
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                  <div class="box-head recom-head">
                        <h3 >My Products</h3>
                       
                    </div><br>
                   <section class="product">
         <div class="container">
            <div class="row">
               <div class="col-sm-6">
                  <div class="product_img">
                     <div class="main_img">
                       <a class="magnifier-thumb-wrapper" href="{{asset('uploads/product')}}/{{$data->image}}" target="_blank">
        <img id="thumb" src="{{asset('uploads/product')}}/{{$data->image}}"
        data-large-img-url="{{asset('uploads/product')}}/{{$data->image}}"
        data-large-img-wrapper="preview">
    </a>
    
                        <!-- <img src="images/1.jpg"> -->
                     </div>

                   <!--   <ul>
                        <li class="active"><img src="images/1.jpg"></li>
                        <li><img src="images/1.jpg"></li>
                        <li><img src="images/1.jpg"></li>
                        <li><img src="images/1.jpg"></li>
                     </ul> -->
                  </div>
                  <div class="magnifier-preview" id="preview" ></div>
               </div>
               <div class="col-sm-6 product_discription">
                  <h2>{{$data->name}} </h2>
                 <!--  <h5> <i class="fa fa-star" aria-hidden="true"></i> <i class="fa fa-star" aria-hidden="true"></i>
                     <i class="fa fa-star" aria-hidden="true"></i>
                     <i class="fa fa-star" aria-hidden="true"></i>
                     <i class="fa fa-star-half-o" aria-hidden="true"></i>
                     <a href="#"> (10 customer reviews)</a>
                  </h5> -->
            
                  <h4>Price: <span>TruAu{{$data->price}}</span></h4>
                  <h3>Description:</h3>
                  <p>{{$data->description}}</p>
                  <button class="buy_now">Buy Now</button> 
               </div>
            </div>
         </div>
      </section>
  <!--     <section class="review">
         <div class="container">
            <hr>
            </hr>
            <div class="review1">
               <div class="row">
                  <div class="col-sm-8">
                     <h2>Review: <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <i class="fa fa-star-half-o" aria-hidden="true"></i>
                     </h2>
                     <form>
                        <textarea class="form-control">Add Your Comment</textarea>
                        <button class="send">Send</button>
                     </form>
                     <div class="review_box">
                        <div class="jack_b">
                           <div class="jack_img">
                              <img class="user_im" src="images/pic_1.png">
                              <div class="jack_tx">
                                 <h4>Oliver Jack <span><i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star-half-o" aria-hidden="true"></i></span>
                                 </h4>
                                 <h5>January 24, 2019</h5>
                              </div>
                           </div>
                           <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries</p>
                        </div>
                        <div class="jack_b">
                           <div class="jack_img">
                              <img class="user_im" src="images/pic_2.png">
                              <div class="jack_tx">
                                 <h4>Harry Kyle <span><i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star-half-o" aria-hidden="true"></i></span>
                                 </h4>
                                 <h5>January 27, 2019</h5>
                              </div>
                           </div>
                           <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries</p>
                        </div>
                        <div class="jack_b">
                           <div class="jack_img">
                              <img class="user_im" src="images/pic_1.png">
                              <div class="jack_tx">
                                 <h4>Oliver Jack <span><i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    <i class="fa fa-star-half-o" aria-hidden="true"></i></span>
                                 </h4>
                                 <h5>January 28, 2019</h5>
                              </div>
                           </div>
                           <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries</p>
                        </div>
                     </div>
                  </div>
                  <div class="col-sm-4"></div>
               </div>
            </div>
         </div>
      </section>
   -->

               
 
                </div>
            </div>

        </div>
    </div>

@endsection
 @section('scripts')
       <script type="text/javascript" src="{{asset('streamtube/js/event.js')}}"></script>
       <script type="text/javascript" src="{{asset('streamtube/js/magnify.js')}}"></script>

<script type="text/javascript">
var evt = new Event(),
    m = new Magnifier(evt);
m.attach({
    thumb: '#thumb',
    large: "{{asset('uploads/product')}}/{{$data->image}}",
    largeWrapper: 'preview',
    zoom: 3
});
</script>
@endsection