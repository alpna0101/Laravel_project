@extends('layouts.user')


@section('styles')
<style type="text/css">
        body
        {
            font-family: 'Roboto', sans-serif;
        }
        .table-view thead tr th
        {
            background:#2E93E0;
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
       margin-top: 25px;
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
            color: #2E93E0;
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
/*.point_cal{
     opacity: 0.08;
    user-select: none;
}*/
    </style>
    @endsection
  
    @section('content')
    <div class="y-content">
        
        <div class="row content-row">

            @include('layouts.user.nav')

            <div class="page-inner col-sm-9 col-md-10">

                <div class="slide-area1 recom-area">
                    <div class="box-head recom-head">
                        <h3  class="pull-left">{{tr('top')}}</h3>
                        
                        <div class="pull-right reedeem_points">
                        <h1>REDEEM POINTS</h1>
                        <p>Coming Soon</p>

                    <div class="clearfix"></div>
                    </div>
                    </div>
<table cellpadding="0" cellspacing="0" width="100%" class="table-view">
    <thead>
      
        <tr>
            <th>Rank<br><span style="font-size: 12px;"></span></th>
            <th>Player<br><span style="font-size: 12px;"></span></th>
            <th>total points<br><span style="font-size: 12px;">( Calculating Points Coming Soon)</span></th>
            <th>Video show<br> <span style="font-size: 12px;">(Total Videos Uploaded)</span></th>
            <th>Viewers<br> <span style="font-size: 12px;">(Total amount of video Views)</span></th>
            <th>Like<br> <span style="font-size: 12px;">(Total amount of Video Likes)</span></th>
            <!-- <th>KDR</th>
            <th>Win PCT</th> -->
        </tr>
    </thead>
    <tbody>
    <?php $i=1; ?>
     @foreach($top as $t)
        <tr>
            <td>{{$i}}</td>
            <td>
                  <img class="user-img" src="{{$t->picture}}" alt="{{$t->name}}" >
                <span class="user-name">{{$t->name}}</span></td>
            <td class="point_cal">{{$t->points}}</td>
            <td>{{$t->video_count}}</td>
            <td>{{$t->video_view_count}}</td>
            <td>{{$t->video_like_count}}</td>
           <!--  <td>1.42</td>
            <td>0.556</td> -->
            <?php $i = $i+1 ?>
        </tr>
      @endforeach


       
    </tbody>
</table>
            </div>

        </div>
    </div>

@endsection