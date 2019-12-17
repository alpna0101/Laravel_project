<div class="page-inner col-sm-9 col-md-10 profile-edit">
        
    <div class="profile-content">   

        <div class="row no-margin">

            <div class="col-sm-12 profile-view">

                <h3 class="m-0">
                    
                    @if($user_subscription_details->id) 

                        {{tr('edit_subscription')}} - 

                        <a href="{{route('user.channel', $channel_details->id)}}">{{$channel_details->name}}</a> 

                    @else 

                        {{tr('add_subscription')}} - 

                        <a href="{{route('user.channel', $channel_details->id)}}">{{$channel_details->name}}</a>
                    @endif

                    <a href="{{route('user.user_subscriptions.index', ['channel_id' => $channel_details->id])}}" style="float:right;letter-spacing: 0.7px" class="btn btn-success">
                        {{tr('view_subscriptions')}}
                    </a>

                </h3>

                @include('notification.notify')

                <form action="{{route('user.user_subscriptions.save')}}" method="POST" enctype="multipart/form-data" role="form">

                    <div class="box-body">

                        <div class="col-md-12">

                            @if($user_subscription_details->id) 

                            <input type="hidden" name="user_subscription_id" value="{{$user_subscription_details->id}}">

                            @endif

                            <input type="hidden" name="channel_id" value="{{$channel_details->id}}">

                            <input type="hidden" name="user_id" value="{{Auth::user()->id}}">

                            <div class="row">

                                <div class="form-group col-md-6">

                                    <label for="title" class="">{{tr('title')}}</label>

                                    <input type="text" required name="title" class="form-control" id="title" value="{{old('title') ?: $user_subscription_details->title}}" placeholder="{{tr('title')}}" >
                                </div>

                                <div class="form-group col-md-6">
                                    
                                    <label for="amount" class="">{{tr('amount')}}</label>

                                    <input type="number" required name="amount" class="form-control" id="amount" placeholder="{{tr('amount')}}" step="any" min="0" value="{{old('amount') ?: $user_subscription_details->amount}}">
                                
                                </div> 

                            </div>


                            <!-- <div class="col-lg-3" style="display: none;">

                                <label for="image" class="">{{tr('image')}}</label>

                                <input type="file" name="image" class="form-control" id="image" value="{{old('image')}}" placeholder="{{tr('image')}}" accept="image/png, image/jpeg" onchange="loadFile(this, 'image_preview')" style="display: none" >

                                <div class="clearfix"></div>

                                <img id="image_preview" style="width:100%;height:150px;" src="{{$user_subscription_details->image ? $user_subscription_details->image : asset('images/subscription.jpg')}}" onclick="$('#image').click()">

                            </div> -->

                            <div class="row">

                                <div class="form-group col-md-6">
                                        
                                    <label for="plan" class="">{{tr('plan')}} <br>

                                    <small><span class="text-danger"><b>{{tr('plan_note')}}</b></span></label></small>

                                    <input type="number" min="1" max="12" pattern="[0-9][0-2]{2}"  required name="plan" class="form-control" id="plan" value="{{old('plan') ?: $user_subscription_details->plan}}" title="Please enter the plan months. Max : 12 months" placeholder="{{tr('plan')}}">
                                </div>

                                                 

                                <div class="form-group col-md-6">

                                    <label for="description" class="">{{tr('description')}}</label>

                                    <div class="clearfix"></div>

                                    <textarea name="description" required class="form-control" placeholder="{{tr('description')}}.">{{old('description') ?: $user_subscription_details->description}}</textarea>
                                
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                    </div>
                
                </form>


            </div>

        </div>

    </div>

</div>

@section('scripts')
    
    <script src="http://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>
    
    <script>
        CKEDITOR.replace( 'ckeditor' );
        function loadFile(event, id){

           $("#"+id).show();

            // alert(event.files[0]);
            var reader = new FileReader();
            reader.onload = function(){
              var output = document.getElementById(id);
              // alert(output);
              output.src = reader.result;
               //$("#imagePreview").css("background-image", "url("+this.result+")");
            };
            reader.readAsDataURL(event.files[0]);
        }
    
    </script>
@endsection