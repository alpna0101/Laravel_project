@extends('layouts.user')

@section('content')
<style type="text/css">
    .item {
        border-bottom: none;
    }
    .product_price {
        font-size:18px;
        color: #b12704;
    }
    .slide-box {
        margin-bottom:20px;
    }
</style>
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10 notification">

                <div class="slide-area1 recom-area">
                    <div class="box-head recom-head" style="margin-bottom: 20px;">
                        <h3>All Notifications</h3>
                    </div>
              

                   <ul class="list-group">
                   @if(count(@$nnotifications)>0)
        @foreach($nnotifications as $notify)
            @if($notify->type == 'chat')
                <li class="list-group-item"> <a href ="{{url('/chat')}}/{{$notify->sender_id}}" target="_blank">{{$notify->message}}</a><p>{{$notify->created_at}}</p></li>
            @else
                <li class="list-group-item"> {{$notify->message}} <a href ="{{url('/view_invoice')}}/{{$notify->transaction_id}}" target="_blank">@if($notify->label!="New Order")#invoice{{$notify->transaction_id}}@endif</a><p>1 day ago</p></li>
            @endif
     @endforeach
        @else
       <li class="list-group-item text-center" style="font-size: 20px;"> No notificatin Found</li>
       @endif
        </ul>
                    <!--end of recommend-list-->

                     @if(count($nnotifications) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div align="center" id="paglink"><?php echo $nnotifications->links(); ?></div>
                            </div>
                        </div>

                    @endif
                </div>

                <!--end of slide-area-->

                <div class="sidebar-back"></div> 
            </div>

        </div>
    </div>
   

@endsection