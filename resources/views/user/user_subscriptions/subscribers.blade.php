@extends('layouts.user')

@section('styles')

<style>
    .referral-tr-img {
        width: 10%
    }
</style>

@endsection

@section('content')

<div class="y-content">

    <div class="row content-row">

        @include('layouts.user.nav')

        <div class="page-inner col-sm-9 col-md-10">

            @include('notification.notify')

           
            <div class="spacing1 top"></div>

            <div class="col-md-12">

                <div class="">

                    <h3 class="text-uppercase"> <span class="text-gray"></span>{{$user_subscription_details->total_subscription}} {{tr('subscribers')}} - {{Setting::get('currency')}} {{total_user_subscription_revenue($user_subscription_details->id)}} </h3>

                    <hr>

                    <div style="line-height: 1.5" class="content-sub-body">

                        <div class="content-sub-body-section">

                            <span class="text-uppercase">{{tr('channel')}}:</span> 

                            <a href="{{route('user.channel', $channel_details->id)}}"> {{$channel_details->name}}</a>

                        </div>

                        <div class="content-sub-body-section">
                            
                            <span class="text-uppercase">{{tr('subscription')}}:</span> 

                            <a href="{{route('user.user_subscriptions.index', ['channel_id' => $channel_details->id])}}"> {{$user_subscription_details->title}}</a>

                        </div>

                    </div>

                    <hr>

                    <div class="card">

                        <div class="card-body">

                            <table id="example2" class="table table-bordered table-striped table-responsive referral-table">

                                <thead>
                                    <tr style="background: white">
                                        <th>{{tr('s_no')}}</th>
                                        <th>{{tr('payment_id')}}</th>
                                        <th>{{tr('subscription')}}</th>
                                        <th>{{tr('username')}}</th>
                                        <th>{{tr('plan')}}</th>
                                        <th>{{tr('payment_mode')}}</th>
                                        <th>{{tr('paid')}}</th>
                                        <th>{{tr('expiry_date')}}</th>
                                        <th>{{tr('status')}}</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @if(count($user_subscription_payments) > 0)

                                        @foreach($user_subscription_payments as $i => $user_subscription_payment)

                                            <tr>
                                                <td>{{$i+1}}</td>

                                                <td>{{$user_subscription_payment->payment_id}}</td>

                                                <td>{{$user_subscription_payment->subscription_name}}</td>
                                            
                                                <td>{{$user_subscription_payment->username}}</td>

                                                <td>{{$user_subscription_payment->subscription_plan}}</td>
                                       
                                                <td class="text-capitalize">{{$user_subscription_payment->payment_mode}}</td>


                                                <td>{{Setting::get('currency')}} {{$user_subscription_payment->paid_amount ? $user_subscription_payment->paid_amount : "0.00" }} (<b class="text-red">{{Setting::get('currency')}} {{$user_subscription_payment->subscription_amount ?: "0.00"}})</td>
                                                
                                                <td>{{date('d M Y',strtotime($user_subscription_payment->expiry_date))}}</td>
                                                
                                                <td>
                                                    @if($user_subscription_payment->status) 
                                                        <span style="color: green;"><b>{{tr('paid')}}</b></span>
                                                    @else
                                                        <span style="color: red"><b>{{tr('not_paid')}}</b></span>

                                                    @endif
                                                </td>
                                            </tr>                   

                                        @endforeach

                                    @endif
                                    

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>


@endsection

@section('scripts')

    <link rel="stylesheet" href="{{ asset('admin-css/plugins/datatables/dataTables.bootstrap.css')}}">

    <script src="{{asset('admin-css/plugins/datatables/jquery.dataTables.min.js')}}"></script>

    <script src="{{asset('admin-css/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>

    <script>

        $(function () {

            $("#example1").DataTable();

            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false
            });
        
          
        }());
        
    </script>
@endsection