<div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header label-primary">
                <b style="font-size:18px;">{{tr('owners_list')}}</b>
            </div>
            <div class="box-body table-responsive">

                <?php 

                $active_notes = tr('owner_active_notes');
                $inactive_notes = tr('owner_inactive_notes');
                $delete_notes = tr('owner_delete_notes');
                ?>

                @if(count($owners) > 0)

                    <table id="example1" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                              <th>{{tr('id')}}</th>
                              <th>{{tr('image')}}</th>
                              <th>{{tr('status')}}</th>
                              <th>{{tr('action')}}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($owners as $i => $owner)

                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>
                                        <img src="{{url('/uploads/owner')}}/{{$owner->image}}" height="100px" />
                                    </td>
                                  <td>
                                        
                                        @if($owner->status)
                                            <span class="label label-success">{{tr('active')}}</span>
                                        @else
                                            <span class="label label-warning">{{tr('inactive')}}</span>
                                        @endif

                                  </td>
                             
                                  <td>
                                        <ul class="admin-action btn btn-default">

                                            <li class="@if($i < 2) dropdown @else dropup @endif">
                                               
                                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                  {{tr('action')}} <span class="caret"></span>
                                                </a>

                                                <ul class="dropdown-menu dropdown-menu-right">

                                                    @if($owner->status==0)
                                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.owner.status',array('id'=>$owner->id,'status'=>1))}}" onclick='return confirm("{{$active_notes}}")'>{{tr('active')}}</a></li>
                                                    @else
                                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.owner.status',array('id'=>$owner->id,'status'=>0))}}" onclick='return confirm("{{$inactive_notes}}")'>{{tr('inactive')}}</a></li>
                                                    @endif

                                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.owner.delete',array('id'=>$owner->id))}}" onclick='return confirm("{{$delete_notes}}")'>{{tr('delete')}}</a></li>

                                                    
                                                </ul>
                                            </li>
                                        </ul>
                                  </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h3 class="no-result">{{tr('no_owner_found')}}</h3>
                @endif
            </div>
          </div>
        </div>
    </div>