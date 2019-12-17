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
   
    @if(@$data)
     <h1>Edit Address</h1>
    @else
      <h1>Add Address</h1>
    @endif

<!-- Form Name -->

<!-- Text input-->

<section class="add_product">
    <div class="card-form">
      <form action="{{route('user.save_address')}}" method="post"  enctype="multipart/form-data" >
        <div class="form-group">
          <label for="email">First Name*:</label>
          <input type="hidden" value ="{{@$data->id}}" name="id">
          <input type="text" class="form-control" id="title" placeholder="Enter title here" value ="{{@$data->first_name}}" name="first_name" required="true">
        </div>
           <div class="form-group">
          <label for="email">Last Name*:</label>
         
          <input type="text" class="form-control" id="title" placeholder="Enter title here" value ="{{@$data->last_name}}" name="last_name" required="true">
        </div>
         <div class="form-group">
          <label for="email">Address 1:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter address line 1 here" name="address_1" value ="{{@$data->address_1}}" required="true">
        </div>
          <div class="form-group">
          <label for="email">Address 2:</label>
          <input type="text" class="form-control" id="title" placeholder="Enter address here" name="address_2" value ="{{@$data->address_2}}" >
        </div>
         <div class="form-group">
          <label for="pwd">Zip Code:</label>
            <input type="text" class="form-control" id="title" placeholder="Enter zipcode here" name="pincode" value ="{{@$data->pincode}}" required="true">
        
        </div>
         <div class="form-group">
          <label for="pwd">City:</label>
            <input type="text" class="form-control" id="title" placeholder="Enter city here" name="city" value ="{{@$data->city}}" required="true">
        
        </div>
        <div class="form-group">
          <label for="pwd">State:</label>
            <input type="text" class="form-control" id="title" placeholder="Enter state here" name="state" value ="{{@$data->state}}" required="true">
        
        </div>
       <button type="submit" class="btn btn-default product_submit">Submit</button>







      </form>
    </div>
</section>

   </div>
   </div>
  </div>

@endsection
@section('scripts')


 <script>
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