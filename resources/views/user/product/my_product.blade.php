@extends('layouts.user')


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
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                  <div class="box-head recom-head">
                        <h3  class="pull-left">My Products</h3>
                        <h3  class="pull-right"><a href="{{route('user.add_product')}}">Add Product</a></h3>
                    </div><br>
                    @if(count($myproduct) > 0)
<table cellpadding="0" cellspacing="0" width="100%" class="table-view">
    <thead>
      
        <tr>
            <th>No<br><span style="font-size: 12px;"></span></th>
            <th>Image<br><span style="font-size: 12px;"></span></th>
            <th>Name<br></th>
            <th>Price<span><b>(USD)</b></span><br></th>
            <th>Action<br> </th>
         
        </tr>
    </thead>
    <tbody>
    <?php $i=1; ?>
     @foreach($myproduct as $t)
        <tr>
            <td>{{$i}}</td>
            <td>
            <?php $data =  explode(".",$t->image);

           
            ?>
                 @if(@$data[1]=="img" || @$data[1]=="png" || @$data[1]=="jpg"  || @$data[1]=="gif" || @$data[1]=="JPEG"  || @$data[1]=="jpeg")
                  <img class="user-img" src="{{asset('/uploads/product')}}/{{$t->image}}" alt="{{$t->name}}" >@else
                  <video controls="" src="{{asset('/uploads/product')}}/{{$t->image}}" width="200" height="180"></video>
                  @endif</td>
                <td>    </a>
            <a href="{{route('user.product_preview',$t->id)}}" title="Preview">{{$t->name}}</a></td>
            <td>{{$t->price}}</td>
            <td><a href="{{route('user.editproduct',$t->id)}}"  title="Edit" ><i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size: 18px;" ></i>
               </a>
            <a href="{{route('user.deleteproduct',$t->id)}}" onclick="return confirm('Are you sure you want to delete this item?');" title="Delete"><i class="fa fa-trash-o" aria-hidden="true" style="font-size: 18px; margin-left: 5px;"></i>

</a>
            </td>
            
            <?php $i = $i+1 ?>
        </tr>
      @endforeach


       
    </tbody>
</table>
@else
 <h3>No product found</h3>
@endif
 @if(count($myproduct) > 0)

                        <div class="row">
                            <div class="col-md-12">
                                <div align="center" id="paglink">{{ $myproduct->links() }}</div>
                            </div>
                        </div>

                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection