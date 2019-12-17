@extends('layouts.user')
 
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/wizard.css')}}">
<style type="text/css">
  .add_product {
    margin-top: 69px;
}

.card-form {
    max-width: 600px;
    padding: 20px;
    margin: 0 auto;
    background: #f2f2f2;
    box-shadow: 0px 10px 20px #e6e6e6;
}
.card-form form label {
    font-weight: 400;
}
.card-form form input {
    height: 45px;
}
.imagePreview {
    width: 100%;
    height: 150px;
    background-position: center center;
  background:url(http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg);
  background-color:#fff;
    background-size: cover;
  background-repeat:no-repeat;
    display: inline-block;
  box-shadow: 0px -3px 6px 2px rgba(170, 170, 170, 0.2);
}
.btn-primary
{
  display:block;
  border-radius:0px;
  box-shadow:0px 4px 6px 2px rgba(0,0,0,0.2);
  margin-top:-5px;
}
.imgUp
{
  margin-bottom:15px;
}
.del
{
  position:absolute;
  top:0px;
  right:15px;
  width:30px;
  height:30px;
  text-align:center;
  line-height:30px;
  background-color:rgba(255,255,255,0.6);
  cursor:pointer;
}
.imgAdd {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #fdc20f;
    color: #fff;
    text-align: center;
    line-height: 30px;
    margin-top: 0px;
    cursor: pointer;
    font-size: 15px;
}
.container-box {
    margin-top: 8px;
}
.add_product_img .fa {
    line-height: 30px;
}
.product_submit.btn.btn-default {
    background: #fdc20f;
    border: none;
    padding: 12px 50px;
    font-size: 18px;
    color: #fff;
}
.product_submit.btn.btn-default:hover {
    background: #cd9c07;
}
section.token_sctn .card-form {
    max-width: 100%;
    margin: 0px 30px;
}
.inner_redem {
    background-color: #fff;
    padding: 20px 15px;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.10);
}
.inner_redem img {
    width: 80px;
    margin-bottom: 10px;
}
.inner_redem h6 {
    font-size: 20px;
    font-weight: 500;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
}
.token_sctn .inner_redem .product_submit {
    font-size: 18px;
    padding: 12px;
    width: 100%;
}

.error{
  color: red;
}
.product_image #product_images video {

    width: 100%;
    height: 150px;
    background-color: #000;

}

@media screen and (max-width:1200px){

}
</style>

<link rel="stylesheet" href="{{asset('admin-css/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">
@endsection

@section('content')

<div class="y-content">

  <div class="row content-row">
		@include('layouts.user.nav')
    <div class="page-inner">
    @if(count(@$newproducts)==0)
      <h1>Use TipMe Credit</h1>
    @elseif(@$data)
     <h1>Edit Product</h1>
     
    @else
      <h1>Add Product</h1>
        <a href="{{route('user.edit_payment_method')}}"><button type="button" class="btn btn-primary text-right" style="float: right;margin-right: 20px;">Edit Payment Method</button></a>
    @endif
<?php  $user = Auth::user();
 ?> 
@if(count(@$newproducts)==0 && !@$data && $tipme==0)
  <section class="add_product token_sctn">
    <div class="card-form">
     <div class="row ">

    <?php 
    //$totalp =(int)$user->total_points;
      //for($i=0; $i<$totalp; $i++){
     if(@$tokens[0] && @$tokens[0]->id && $tokens[0]->id) {
    ?>
   
      <form action="{{route('user.use_token')}}" method="post" >
       <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">

                <div class="inner_redem text-center">
                 <input type="hidden"  name="token" value="{{$tokens[0]->id}}">
                    <img src="{{asset('seller_token.png')}}">
                    <h6>{{$tokens[0]->token}}</h6>
              
                    <button type="submit" class="btn btn-default product_submit">Redeem</button>

                </div>
            </div>
       
      </form>
      <?php } ?>
    </div>
  </section>
@else
<!-- Form Name -->

<!-- Text input-->

  

<section class="add_product">
    <div class="card-form">
    @if(!@$allmethod)
      <form action="{{route('user.save_product')}}" method="post"  enctype="multipart/form-data" >
        <div class="form-group">
          <label for="email">Title*:</label>
          @if(count(@$newproducts)==0)
          <input type="hidden" value ="{{@$data->id}}" name="id">
          @else
        <input type="hidden" value ="{{@$newproducts[0]->id}}" name="id">
          @endif
          <input type="text" class="form-control" id="title" placeholder="Enter title here" value ="{{@$data->name}}" name="name" required="true">
        </div>
        <div class="form-group">
       <label for="email">Type:</label>
   
    <select id="product_categorie" name="type" class="form-control" required="true">
    <option value="">Please Select</option>
    <option value="product" {{@$data->type == "product"  ? 'selected' : ''}}>Product</option>
    <option value="service" {{@$data->type == "service"  ? 'selected' : ''}}>Service</option>
    </select>
 </div>
   <div class="form-group">
          <label for="email">Price:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter price here" name="price" value ="{{@$data->price}}" required="true">
        </div>
         <div class="form-group">
          <label for="pwd">Description:</label>
          <textarea class="form-control" rows="5" placeholder="Enter description here" name="description">{{@$data->description}}</textarea>
        </div>
    

         <div class="form-group ">
          <label for="pwd" style="width: 100%">Add Product Images/Video:</label>
          <div class="product_image">
          <div class="container-box">
              <div class="row">
                <div id="product_images">
                    <div class="col-sm-4 imgUp">
                      
                      <?php 
                      if(@$data->image && !empty(@$data->image)){
                        $url =  url("/public/uploads/product/")."/".@$data->image;
                      }else {
                        $url = null;
                      }
                      ?>
                      <div class="imagePreview" <?php if($url) { ?>style="background-image: url('{{$url}}');" <?php } ?>></div>
                  <label class="btn btn-primary">
                   Upload<input type="file" class="uploadFile img" value="Upload Photo" name = "image" style="width: 0px;height: 0px;overflow: hidden;">
                    </label>
                    </div>
                    <?php 
                    if(isset($data->getImages) && !empty($data->getImages)){
                      $count = count($data->getImages);
                      for($i = 0;$i < $count;$i++){
                        $image_url = url("/public/uploads/product/")."/".$data->getImages[$i]->image;
                        if(!empty($data->getImages[$i]->image)) { 
                    ?>
                    <div class="col-sm-4 imgUp">
                      <div class="imagePreview" style="background-image: url('{{$image_url}}');"></div>
                  <label class="btn btn-primary">
                   Upload<input type="file" class="uploadFile img" value="Upload Photo" name = "product_images[]" style="width: 0px;height: 0px;overflow: hidden;">
                          </label>
                          <i class="fa fa-times del" id="{{$data->getImages[$i]->id}}"></i>
                    </div>
                    <input type="hidden" name="image_id[]" value="{{$data->getImages[$i]->id}}">

                  <?php }} } ?>

              </div>
              <input type="hidden" name="delete_product_image" id="delete_images">
              <div class="add_product_img">
                  <i class="fa fa-plus imgAdd"></i>
              </div>
             </div>
            </div>
          </div>
        </div>
       <button type="submit" class="btn btn-default product_submit">Submit</button>







      </form>
      @else
        <form action="{{route('user.save_payment_method')}}" method="post" id="payment_form">
        <h4>Select Your Payment Methods</h4>
         @foreach($allmethod as $method)
        <div class="row">

          <div class="col-sm-3 ">
          <input type="checkbox"  name="payment_methods[]" value="{{$method->id}}" class="payment_check">
          </div> 
          <div class="col-sm-3 ">{{$method->name}}</div> <div class="col-sm-6 input_box">
        </div>
         </div>
        @endforeach
        <button type="submit" class="btn btn-default product_submit text-align">Save</button>
      </form>
      @endif
    </div>
</section>

@endif
   </div>
   </div>
  </div>

@endsection
@section('scripts')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

 <script>
 $("#payment_form").validate();
function stripeResponseHandler(status, response) {

  // Grab the form:
  var $form = $('#payment-form');

  if (response.error) { // Problem!

    // Show the errors on the form:
    // $form.find('.bank-errors').text(response.error.message);
    // $form.find('button').prop('disabled', false); // Re-enable submission

  } else { // Token created!

    // Get the token ID:
    var token = response.id;


    // Insert the token into the form so it gets submitted to the server:
    // $form.append($('<input type="hidden" name="stripeToken" />').val(token));

    // // Submit the form:
    // $form.get(0).submit();

  }
}
 $(document).ready(function(){


  var img = "{{@$data->image}}"
 
 if(img!=""){
  $('#preview1').css("background-image", 'url({{asset("/public/uploads/product")}}/{{@$data->image}})');
}

var img = "{{@$data->image2}}"
 
 if(img!=""){
  $('#preview2').css('background-image', "url('{{asset('public/uploads/product')}}/{{@$data->image2}}')");
}

var img = "{{@$data->image3}}"
 
 if(img!=""){
  $('#preview3').css('background-image', "url('{{asset('public/uploads/product')}}/{{@$data->image3}}')");
}
  
 });

$(".imgAdd").click(function(){
  let len = $("#product_images").children(".imgUp").length;
  //console.log(len); return;
  if(len < 4){
    $("#product_images").append('<div class="col-sm-4 imgUp"><div class="imagePreview"></div><label class="btn btn-primary">Upload<input type="file" class="uploadFile img" value="Upload Photo" style="width:0px;height:0px;overflow:hidden;" name="product_images[]"></label><i class="fa fa-times del"></i><input type="hidden" name="image_id[]" value=""></div>');
  }
  
  /*$(this).closest(".row").find('.imgAdd').before('');*/
});
let ar = new Array();
$(document).on("click", ".payment_check" , function() {
              if($(this).prop("checked") == true){

               $(this).parent().next().next().html('<input type="text" class="form-control"  placeholder="Enter detail here" name="payment_detail[]"  required="true">');

            }

            else if($(this).prop("checked") == false){

                $(this).parent().next().next().html('');

            }

  });

$(document).on("click", "i.del" , function() {
  let id = $(this).attr("id");
  ar.push(id);
  $(this).parent().remove();

  $("#delete_images").val(ar);
});
/*preview */

$(function() {
    $(document).on("change",".img", function()
    {
        var uploadFile = $(this);
        var files = !!this.files ? this.files : [];
          
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
 console.log(/^video/.test( files[0].type));
        if (/^video/.test( files[0].type)){ // only image file
          var source = document.createElement('video'); //added now

      source.width = 180;

      source.height = 180;

      source.controls = true;

      source.src = URL.createObjectURL(files[0]);

      document.body.appendChild(source);
                              
         uploadFile.closest(".imgUp").find('.imagePreview').append(source);
        }else{
          var src = URL.createObjectURL(files[0]);
          uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+src+")");
        }
    
      
    });
});

/* preview 2*/
/*$(function() {
    $(document).on("change","#image2", function()
    {
        var uploadFile = $(this);
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
//uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
$("#preview2").css("background-image", "url("+this.result+")");
            }
        }
      
    });
});*/
/*preview 3*/
/*$(function() {
    $(document).on("change","#image3", function()
    {
        var uploadFile = $(this);
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
      //uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
      $("#preview3").css("background-image", "url("+this.result+")");
            }
        }
      
    });
});
*/
/*$(function() {
    $(document).on("change",".imagePreview", function()
    {
        var uploadFile = $(this);
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
      uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
      //$("#preview3").css("background-image", "url("+this.result+")");
            }
        }
      
    });
});*/
</script>
@endsection