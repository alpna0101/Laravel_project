@extends('layouts.admin')

@section('title', 'Products')

@section('content-header', 'Products')

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>Home</a></li>
    <li><a href="{{route('admin.products')}}"><i class="fa fa-user"></i> Product </a></li>
@endsection

@section('styles')
<style type="text/css">
        body
        {
            font-family: 'Roboto', sans-serif;
        }
        .table-view thead tr th
        {
            background:#fdc20f;
            font-size: 16px;
            font-weight: 500;
            text-transform: uppercase;
            color:#fff;
            padding: 10px;
        }
        .table-view tbody tr td
        {
            font-size: 14px;
            color:#000;
            padding: 10px;
        }
        .table-view tbody tr:nth-child(2n) td
        {
            background:#e8f3fc;
        }
        .table-view {
       margin-top: 35px;
      }
        .user-img
        {
            width:50px;
            height:50px;
            border-radius: 100%;
            margin-right:10px;
        }
        .user-name
        {
            font-weight: 500;
            color: #fdc20f;
            text-transform: uppercase;
        }
        .reedeem_points {
    background: #ff00001a;
    border-radius: 10px;
    padding: 15px 20px;
    color: red;
    text-align: center;
    margin: 10px 0;
}
.reedeem_points h1 {
    font-size: 24px;
    margin-top: 0;
    margin-bottom: 2px;
}
.reedeem_points p {
    margin-bottom: 0;
}
.box-head{
    margin-top: 20px; 
}
    </style>
    @endsection
  
    @section('content')
    <?php error_reporting(0); ?>
    <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header label-primary">
                <b style="font-size:18px;">Products</b>

                <a href="{{url('admin/add-product')}}" class="btn btn-default pull-right">Add Product</a>

                <!-- EXPORT OPTION START -->

                    {{--<!-- <ul class="admin-action btn btn-default pull-right" style="margin-right: 20px">
                            
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                  {{tr('export')}} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li role="presentation">
                                        <a role="menuitem" tabindex="-1" href="{{route('admin.users.export' , ['format' => 'xls'])}}">
                                            <span class="text-red"><b>{{tr('excel_sheet')}}</b></span>
                                        </a>
                                    </li>

                                    <li role="presentation">
                                        <a role="menuitem" tabindex="-1" href="{{route('admin.users.export' , ['format' => 'csv'])}}">
                                            <span class="text-blue"><b>{{tr('csv')}}</b></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul> -->--}}

                <!-- EXPORT OPTION END -->
            </div>
            <div class="box-body table-responsive">

               

                    <table id="example1" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                              <th>Serial no.</th>
                              <th>Name</th>
                              <th>Price<span><b>(TruAu)</b></span></th>
                              <th>Image</th>
                              <th>Added by</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                @if(count($products) > 0)
                        <tbody>
                            <?php $i = 0; ?>
                            @foreach($products as $product)
                                <?php $i++; ?>
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>{{$product->name}}</td>
                                    <td>{{$product->price}}</td>  
                                    <td>

                                        <?php if(!empty($product->image))  { ?>
                                            <img src="{{url('/public/uploads/product/').'/'.$product->image}}" height="100" width= "100" />
                                        <?php } ?>
                                    </td> 
                                    <td>
                                        <?php 
                                        if($product->generated_by == "A"){
                                            echo "Admin";
                                        }else if($product->generated_by == "U"){
                                            echo "User";
                                        }
                                        ?>
                                    </td> 
                                  <td>
                                        <ul class="admin-action btn btn-default">

                                            <li class="@if($i < 2) dropdown @else dropup @endif">
                                               
                                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                  {{tr('action')}} <span class="caret"></span>
                                                </a>

                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.editProduct' ,  $product->id)}}">{{tr('edit')}}</a></li>

                                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="{{route('admin.delete_product' , $product->id)}}">{{tr('delete')}}</a></li>
                                                    
                                                    
                                                </ul>
                                            </li>
                                        </ul>
                                  </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h3 class="no-result">{{ "No product found" }}</h3>
                @endif
            </div>
          </div>
        </div>
    </div>

@endsection