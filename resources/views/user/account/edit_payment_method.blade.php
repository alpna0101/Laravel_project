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
    
      <h1>Update Payment Method</h1>
 


  

<section class="add_product">
    <div class="card-form">
   
        <form action="{{route('user.update_payment_method')}}" method="post" id="payment_form">
        <h4>Update Your Payment Methods</h4>
         @foreach($allmethod as $method)
        <div class="row">
 
       <?php $checked = ""; ?>
       <?php $methodselect = ""; ?>
        
           @foreach($mymethod as $myethod)
           
           
           @if($myethod->payment_method_id==$method->id)
            <?php $checked = "checked"; ?>
               
             
            
  
             @endif
              <input type="hidden"  name="id[]" value="{{$myethod->id}}" >
            @endforeach 
          
          
              <div class="col-sm-3 ">

          <input type="checkbox"  name="payment_methods[]" value="{{$method->id}}" {{$checked}} class="payment_check">

          
          </div> 
          <div class="col-sm-3 ">{{$method->name}}</div>
           <div class="col-sm-6 " >
            @foreach($mymethod as $myethod)
           @if($myethod->payment_method_id==$method->id)
             <input type="text" class="form-control"  placeholder="Enter detail here" name="payment_detail[]" value="{{$myethod->payment_detail}}" required="true">
  
             @endif
            
            @endforeach 
          
           </div>
        </div>
         
        @endforeach
        <button type="submit" class="btn btn-default product_submit text-align">Save</button>
      </form>
   
    </div>
</section>

   </div>
   </div>
  </div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

 <script>
 $("#payment_form").validate();
$(document).on("click", ".payment_check" , function() {
                   if($(this).prop("checked") == true){

               $(this).parent().next().next().html('<input type="text" class="form-control"  placeholder="Enter detail here" name="payment_detail[]"  required="true">');

            }

            else if($(this).prop("checked") == false){

                $(this).parent().next().next().html('');

            }

  });
</script>
@endsection