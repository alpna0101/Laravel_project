@extends('layouts.admin')

@section('title', tr('point'))

@section('content-header')



@endsection

@section('breadcrumb')

    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

    <li class="active"><i class="fa fa-key"></i> {{tr('point')}}</li>

@endsection

@section('content')

	@include('notification.notify')

	<div class="row">

        <div class="col-xs-12">

          <div class="box box-primary">

          	<div class="box-header label-primary">

                <b>{{tr('point')}}</b>

                <a href="{{route('admin.point_create')}}" style="float:right" class="btn btn-default">{{tr('add_point')}}</a>
            </div>
            
            <div class="box-body">
            	
              	<table id="example1" class="table table-bordered table-striped">

					<thead>
					    <tr>
					      	<th>{{tr('id')}}</th>
					      	<th>{{tr('category')}}</th>
					      	<th>{{tr('point_type')}}</th>
					      
					      	<th>{{tr('point')}}</th>
					      	<th>{{tr('action')}}</th>
					    </tr>
					</thead>

					<tbody>
					
						@foreach($data as $i => $value)

						    <tr>
						      	<td>{{$i+1}}</td>
						      	<td><a href=""> {{$value->type}} </a></td>
						      	<td>{{$value->name}}</td>
						      	<td>{{$value->point}}</td>

						      

						      	
						      
								<td>
									<ul class="admin-action btn btn-default">

										<li class="dropdown">

								            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
								              {{tr('action')}} <span class="caret"></span>
								            </a>

								            <ul class="dropdown-menu">

								              	<li role="presentation">
								              		<a role="menuitem" tabindex="-1" href="{{url('admin/point_edit').'/'.$value->id}}"><i class="fa fa-edit"></i>&nbsp;{{tr('edit')}}
								              		</a>
								              	</li>

								         

								    								            

								              	<li role="presentation">

												
														<a role="menuitem" tabindex="-1" onclick="return confirm('Are you sure?');" href="{{url('admin/delete_point').'/'. $value->id}}"><i class="fa fa-trash"></i>&nbsp;{{tr('delete')}}</a>
																	

								              	</li>

								            </ul>
										
										</li>
									</ul>

								</td>
						    
						    </tr>

						@endforeach

					</tbody>
				
				</table>
            </div>
          </div>
        </div>
    </div>

@endsection
