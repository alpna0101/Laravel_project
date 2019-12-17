@extends('app.layout')

@section('title', 'Share Screen')

@section('content')
<style>
    video {
        width: 100%;
    }

    .outer_video_sction{
	position: relative;
	height: 100vh;
    background: #000;
	}
.inner_video_sction {
    position: absolute;
    z-index: 99999;
    left: 0px;
    right: 0px;
    margin: 0 auto;
    text-align: center;
	bottom:0px;
  top: 70%;
}
.inner_video_sction ul li {
    display: inline-block;
    margin-right: 15px;
}
.inner_video_sction ul{
padding:0px;
margin:0px;
list-style-type:none;
}
.inner_video_sction ul li img {
    width: 45px;
    cursor: pointer;
}
.inner_video_sction ul li:nth-child(1) img{
width:45px;
cursor: pointer;
}
video::-webkit-media-controls {
  display: none;
}
.video_small {
    position: absolute;
    right: 50px;
    background-color: #ffff;
    width: 100px;
    height: 100px;
    bottom: 150px;
}
.share_title {
    position: relative;
    z-index: 999999;
    top: 30%;
    color: #fff;
    text-align: center;
}
.watch_screen {
  top: 80% !important;
  opacity: 0.6;
}
.page-inner {
  min-height: 250px;
}
</style>
<div class="y-content">

   
        <div class="row content-row">

            @include('layouts.user.nav')
        
    <div class="page-inner col-xs-12 col-sm-9 col-md-10">
<!-- <video id="myscreen" autoplay muted></video> -->
    <div id="app" class="ui main container chatting-module" style="margin-top:30px;">
      <!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script> -->

      <!-- <button onclick="start()">Share My Screen</button>
      <button onclick="joinRoom()">Join</button> -->
      @if($isAdmin)
         
          <div class="outer_video_sction">
            <h2 class="share_title">Screen Sharing...</h2>
                <div class="inner_video_sction">
                    <ul>
                        <li><img title="Stop Sharing" onclick="stopScreen()" src="{{url('/end_call.png')}}"></li>
                    </ul>
                </div>
          </div>
      @else
        <div class="inner_video_sction watch_screen" style="display: none">
                    <ul>
                        <li><img title="Stop Watching" onclick="stopScreen()" src="{{url('/end_call.png')}}"></li>
                    </ul>
                </div>
      @endif
      
</div>
</div>
</div>
</div>
@endsection

@section('script')
      <script src="//cdn.temasys.com.sg/skylink/skylinkjs/0.6.x/skylink.complete.js"></script>
      <script>
            var adminId = "<?php echo $isAdmin;?>";
            var room = "<?php echo $room;?>";
            console.info(adminId);

            var skylink = new Skylink();
            console.info(new Skylink());
                skylink.on('peerJoined', function(peerId, peerInfo, isSelf) {
                  //console.info('------', peerId, peerInfo);
                  if(isSelf) return; // We already have a video element for our video and don't need to create a new one.

                    var vid = document.createElement('video');
                    vid.autoplay = true;
                    vid.muted = false; // Added to avoid feedback when testing locally
                    vid.id = peerId;
                    document.getElementById("app").appendChild(vid);
                });
                skylink.on('incomingStream', function(peerId, stream, isSelf) {
                  console.info('isSelf', isSelf);
                  if(isSelf) return;
                  var vid = document.getElementById(peerId);
                  attachMediaStream(vid, stream);
                  $('video[id!='+peerId+']').hide();
                  $('.watch_screen').show();
                });
                skylink.on('peerLeft', function(peerId, peerInfo, isSelf) {
                  var vid = document.getElementById(peerId);
                  document.body.removeChild(vid);
                });
                // skylink.on('mediaAccessSuccess', function(stream) {
                //   var vid = document.getElementById('myscreen');
                //   //attachMediaStream(vid, stream);
                // });
                skylink.init({
                  apiKey: "{{envfile('SKYLINK_APIKEY')}}",
                  usePublicStun: false,
                  defaultRoom: room
                });
        
        jQuery(document).ready(function() {
            if(adminId) {
                start();
            } else {
                joinRoom();
            }
        })

        function start() {
          // skylink.shareScreen();
          skylink.shareScreen(true, function (error, success) {
            console.info('share screen error: ', error, 'success: ', success)
            if (error) return;
            attachMediaStream(document.getElementById("myscreen"), success);
          });
          skylink.joinRoom();
        }
        function joinRoom() {
          skylink.joinRoom({audio: true});
        }
        function stopScreen () {
          skylink.stopScreen();
          window.location.href = "<?php echo url('/chat'); ?>";
        }

      </script>
@endsection
