@extends('layouts.admin')

@section('title', tr('coin_price'))

@section('content-header', tr('coin_price'))



@section('content')

	@include('notification.notify')

	<div class="row">

        <div class="col-xs-12">

          	<div class="box box-primary">

	          	<div class="box-header label-primary">

	                <b style="font-size:18px;">{{tr('coin_price')}}</b>

	               
	            </div>
            	
            	<div class="box-body">

					<table id="example1" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      <th>{{tr('id')}}</th>
						      <th>{{tr('price')}}</th>
						     
						      <th>{{tr('action')}}</th>
						    </tr>
						
						</thead>

						<tbody>


							    <tr>

							      	<td>1</td>

							      

							      

							   

							    <td><b>{{$data->price}}</b></td>
							  <td><a href="{{route('admin.users.coin_price_update', array('id' => $data->id))}}">edit</a></td>
							 
							      

							    </tr>
					
						
						</tbody>

					</table>

				</div>
			</div>
		</div>
	</div>

@endsection