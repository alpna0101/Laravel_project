@include('notification.notify')

<div class="row">

    <div class="col-md-10 ">

        <div class="box box-primary">

            <div class="box-header label-primary">

                <b>@yield('title')</b>
 <?php if(@$data->id){
        $edit_id = "/".@$data->id;
      }else {
        $edit_id = null;
      } ?>
                <a href="{{route('admin.point_index')}}" style="float:right" class="btn btn-default">{{tr('view_point_setting')}}</a>
            </div>


            <form class="form-horizontal" action="{{url('admin/save_point')}}" method="POST" enctype="multipart/form-data" role="form">

                <input type="hidden" name="id" value="{{$data->id}}">

              

                <div class="box-body">

                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="amount" class="">{{tr('category')}}</label>

                        <!-- <div class="col-sm-10"> -->
                            <select id="product_categorie" name="type" class="form-control" required="true">
                        <option value="">Please Select</option>
                        <option value="product" {{@$data->type == "product"  ? 'selected' : ''}}>Product</option>
                        <option value="video" {{@$data->type == "video"  ? 'selected' : ''}}>Video</option>
                         <option value="tip_credit" {{@$data->type == "tip"  ? 'selected' : ''}}>Tip Credit</option>
                        </select>
                        <!-- </div> -->
                    </div>
                     <div class="form-group">
          <label for="email">Tip Type:</label>
           @if(@$point_type)
              <select id="point_type_id" name="point_type_id" class="form-control" required="true">
            <option value="">Please Select</option>
           @foreach($point_type as $type)
         <option value="{{$type->id}}" {{@$data->point_type_id == $type->id  ? 'selected' : ''}}>{{$type->name}}</option>
          @endforeach
          </select>
            @endif
        </div>
                   
                     <div class="form-group">
                    
                        <label for="point" class="">{{tr('Tip')}} <br></label>

                        <input type="number" min="1" max="12" pattern="[0-9][0-2]{2}"  required name="point" class="form-control" id="point" value="{{($data->point) ? $data->point : old('point')}}" title="{{tr('point')}}" placeholder="{{tr('Tip')}}">
                    </div>

                  

                  
                
                        
                    </div>

                </div>

                <div class="box-footer">
                    <a href="" class="btn btn-danger">{{tr('cancel')}}</a>
                    <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                </div>
            </form>
        
        </div>

    </div>

</div>
