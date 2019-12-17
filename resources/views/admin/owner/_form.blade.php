@include('notification.notify')

<div class="row">

    <div class="col-md-12">

        <div class="box box-primary">

            <div class="box-header label-primary">
                <b style="font-size:18px;">{{tr('add_owner')}}</b>
            </div>

            <form class="form-horizontal" action="{{route('admin.owner.save')}}" method="POST" enctype="multipart/form-data" role="form">

                <div class="box-body">

                    <div class="row">

                        <div class="col-lg-3 text-center">

                            <img id="picture_preview" style="width: 150px;height: 150px;cursor: pointer;" src="{{asset('placeholder.png')}}" onclick="return $('#picture').click()" />

                        </div>
                        <div class="col-lg-9">
                             <input type="file" name="picture" id="picture" onchange="loadFile(this, 'picture_preview')" style="width: 200px;" accept="image/jpeg, image/png" />
                        </div>

                    </div>

                </div>

                <div class="box-footer">
                    <a href="" class="btn btn-danger">{{tr('reset')}}</a>
                    <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                </div>
                <input type="hidden" name="timezone" value="" id="userTimezone">
            </form>
        
        </div>

    </div>

</div>
        <div id="imagemodal" class="modal fade" role="dialog">
  <div class="modal-dialog" >

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
        
          <img src="" id="preview_image">
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default" id="save_image">save image</button>
      </div>
    </div>

  </div>
</div>
