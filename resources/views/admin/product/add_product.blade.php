@extends('layouts.admin')

@section('title', tr('add_product'))

@section('content-header', tr('add_product'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.products')}}"><i class="fa fa-user"></i> {{tr('product')}}</a></li>
    <li class="active"><i class="fa fa-user-plus"></i> {{tr('add_product')}}</li>
@endsection

@section('styles')

<link rel="stylesheet" href="{{asset('admin-css/plugins/datepicker/datepicker3.css')}}">

@endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/wizard.css')}}">
<style type="text/css">
  .add_product {
    //margin-top: 69px;
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
.content {
  margin:15px;
}
textarea {
  resize: none;
}
@media screen and (max-width:1200px){

}
</style>

<link rel="stylesheet" href="{{asset('admin-css/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">


<div class="y-content">

  <div class="row content-row">

		

    <div class="page-inner">

    @if(@$data)
     <h1>Edit Product</h1>
    @else
      <h1>Add Product</h1>
      @endif
     


<!-- Form Name -->


<!-- Text input-->

<section class="add_product">
    <div class="card-form">
      <?php if(@$data->id){
        $edit_id = "/".@$data->id;
      }else {
        $edit_id = null;
      } ?>
      <form action="{{url('admin/save-product').$edit_id}}" method="post"  enctype="multipart/form-data" >
        <div class="form-group">
          <label for="email">Title*:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter title here" value ="{{@$data->name}}" name="name" required="true">
        </div>
        <div class="form-group">
       <label for="email">Type:</label>
   
    <select id="product_categorie" name="type" class="form-control" required="true">
    <option value="">Please Select</option>
    <option value="product" {{@$data->type == "product"  ? 'selected' : ''}}>Product</option>
    <option value="service" {{@$data->type == "service"  ? 'selected' : ''}}>Service</option>
     <option value="seller_token" {{@$data->type == "seller_token"  ? 'selected' : ''}}>Seller Token</option>
    </select>
 </div>
    <div class="form-group">
          <label for="email">Price:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter price here" name="price" value ="{{@$data->price}}" required="true">
        </div>
        @if(@$data->type == "seller_token")
        <div class="form-group">
          <label for="email">Token:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter token here" name="token" value ="{{@$data->token}}" required="true">
        </div>
        @else
        <div class="form-group token_seller">
          
        </div>
        @endif
         <div class="form-group">
          <label for="pwd">Description:</label>
          <textarea class="form-control" rows="5" placeholder="Enter description here" name="description">{{@$data->description}}</textarea>
        </div>

         <!-- <div class="form-group">
          <label for="pwd" style="float:left">Add Main Image:</label>
          <div class="product_image">
          <div class="container-box">
              <div class="row">
              <div class="col-sm-4 imgUp">
                <div class="imagePreview"></div>
            <label class="btn btn-primary">
            @if(@$data)
            Upload<input type="file" class="uploadFile img" value="Upload Photo" name = "image" style="width: 0px;height: 0px;overflow: hidden;" >
            @else
             Upload<input type="file" class="uploadFile img" value="Upload Photo" name = "image" style="width: 0px;height: 0px;overflow: hidden;" required="true">
             @endif
                    </label>
              </div>
              
             </div>
            </div>
          </div>
        </div> -->

      <div class="form-group ">
          <label for="pwd" style="width: 100%">Add Product Images:</label>
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
                      <?php if(@$data->image){
                        echo '<input type="hidden" name="old_image" value="'.$data->image.'"/>';
                      } ?>
                  <label class="btn btn-primary">
                   Upload<input type="file" class="uploadFile img" value="Upload Photo" name = "image" style="width: 0px;height: 0px;overflow: hidden;">
                    </label>
                    </div>
                    <?php
                    if(@$data) {
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

                  <?php }} } } ?>

              </div>
              <input type="hidden" name="delete_product_image" id="delete_images">
              <div class="add_product_img">
                  <i class="fa fa-plus imgAdd"></i>
              </div>
             </div>
            </div>
          </div>
        </div>
        


       <button type="submit" class="btn btn-success product_submit">Submit</button>







      </form>
    </div>
</section>

</form>  
   </div>
   </div>
  </div>

@endsection
@section('scripts')


 <script>
 /*$(document).ready(function(){
  var img = "{{@$data->image}}"
 
 if(img!=""){
  $('.imagePreview').css('background-image', "url('{{asset('uploads/product')}}/{{@$data->image}}')");
}
  
 });
$(".imgAdd").click(function(){
  $(this).closest(".row").find('.imgAdd').before('<div class="col-sm-4 imgUp"><div class="imagePreview"></div><label class="btn btn-primary">Upload<input type="file" class="uploadFile img" value="Upload Photo" style="width:0px;height:0px;overflow:hidden;"></label><i class="fa fa-times del"></i></div>');
});
$(document).on("click", "i.del" , function() {
  $(this).parent().remove();
});
$(function() {
    $(document).on("change",".uploadFile", function()
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
            }
        }
      
    });
});*/
$("#product_categorie").change(function(){
  if($(this).val()=="seller_token"){
    $(".token_seller").html(`<label for="email">Token:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter price here" name="token" value ="{{@$data->token}}" required="true">`);
  }else{
 $(".token_seller").html("");
  }
})
$(".imgAdd").click(function(){
  let len = $("#product_images").children(".imgUp").length;
  //console.log(len); return;
  if(len < 4){
    $("#product_images").append('<div class="col-sm-4 imgUp"><div class="imagePreview"></div><label class="btn btn-primary">Upload<input type="file" class="uploadFile img" value="Upload Photo" style="width:0px;height:0px;overflow:hidden;" name="product_images[]"></label><i class="fa fa-times del"></i><input type="hidden" name="image_id[]" value=""></div>');
  }
  
  /*$(this).closest(".row").find('.imgAdd').before('');*/
});
let ar = new Array();

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
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
                console.log(uploadFile.closest(".imgUp").html());
    uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
  //$("#preview1").css("background-image", "url("+this.result+")");
            }
        }
      
    });
});
</script>
@endsection