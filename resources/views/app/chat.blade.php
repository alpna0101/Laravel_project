@extends('app.layout')

@section('title', 'Chat | Laravel Chat')

    <style type="text/css">

        .ui-autocomplete{
          z-index: 99999 !important;
        }
        .ui-autocomplete-category{
          z-index: 99999; 
        }
.chosen-container{
    width: 375px !important;
}
.create_grup button {

    box-shadow: unset;
    margin: 0px;
    text-align: center;
    height: 30px;
    padding: 0px 10px 0px 0px;

}
.create_grup button:focus{
  outline: unset;
}
.create_grup button i {
    color: #000;
    margin: -10px auto -4px 0px;
}
.create_grup {
    padding: 10px 0px;
    border-top: 1px solid #ccc;
}
#create_group .modal-dialog, #get_group .modal-dialog{
    max-width: 500px;
    margin: 100px auto 0px auto;
    width: 100%;
}
#create_group .chosen-container, #create_group .chosen-container-multi .chosen-choices, 
#get_group .chosen-container, #get_group .chosen-container-multi .chosen-choices{
   width: 100% !important;
}
#create_group .chosen-container-multi .chosen-choices {

    min-height: 36px !important;
    height: auto !important;

}
.create_btn {
    margin-top: 30px;
    text-align: center;
}
.create_btn #submit_btn {

    background-color: #fdc20f;
    border: none;
    border-radius: 10px;
    color: #fff;
    line-height: 22px;
    font-size: 14px;
    font-weight: bold;

}
.view_user_icon {
    cursor: pointer;

}
.titl_reltv {

    position: relative;

}
.view_user_icon img {

    width: 30px;

}
.ui.dividing.header.text-center.welcome {
    padding-bottom: 30px;
}
#groups ul.contnt_user {
      max-height: 534px;
    min-height: 534px;
}
#submit_btn2{
     color: #fff;
    background-color: #FFC107;
    border-color: #FFC107;
    padding: 4 4 4 4;
    border-radius: 5px;
}
.y-menu.scroll {
    height: 750px;
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
  bottom:50px;
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
    width: 40px;
}
.inner_video_sction ul li:nth-child(1) img{
width:35px;
}
video::-webkit-media-controls {
  display: none;
}
.video_small {
    position: absolute;
    right: 25px;
    background-color: #ffff;
    width: 100px;
    height: 100px;
    bottom: 25px;
}
@media(max-width: 767px){
  .chatting-module .ui.grid > .row > [class*="thirteen wide"].column .ui.dividing.header.titl_reltv {
      font-size: 16px;
      max-width: 75%;
      width: 100%;
      border-bottom: unset;
  }
  #comments-container {
    border-top:1px solid rgba(34,36,38,.15);
    padding-top: 15px;
  }
}
    </style>
     <style>

.outer_group_sction {
    position: relative;
        height: 100vh;
    background: #000;
    float: left;
    width: 100%;
}
  .box ul{
    padding: 0px;
    margin: 0px;
    list-style-type: none;
    float: left;
    width: 100%;
    height: 100%;
}
  video::-webkit-media-controls {
  display: none;
}
.box {
    float: left;
    width: 100%;
    height: 100%;
}
.box ul li{
    display: inline-block;
    float: left;
    height: 100%;
}
.box ul li.box2{
  width: calc(100% - 50%);
}
.box ul li.box1{
  width: 100%;
}
.box ul li.box3 {
    width: 33.3%;
}
.box ul li.box4 {
    width: 50%;
    height: 50%;
}

  </style>
@section('content')
       
 <div class="y-content">

   
        <div class="row content-row">

            @include('layouts.user.nav')
            <div class="col-xs-12 col-sm-9 col-md-10">
         
     <div class="outer_group_sction" style="display: none;">
         <div class="inner_video_sction">
                <ul>
               <!--    <li><img src="{{asset('/images/mute.png')}}" ></li> -->
                 <!--  <li><img src="{{asset('/images/end_video.png')}}"></li> -->
                  <li onclick="stopStream()" style="cursor: pointer"><img src="{{asset('/images/end_call.png')}}"></li>
                </ul>
              </div>
               <div class="video_small">
                <video id="myvideo_group" controls="true" playsinline autoplay   muted width="100%" height="100%"></video>
              
              </div>
      <div class="box">
    <ul class="video_section">
   
      <!-- <li class="box3">
     <video id="myvideo" controls="true" playsinline autoplay   muted width="100%" height="100%"></video>
      </li> -->
    

    </ul>
  </div>
</div>

   <div class="outer_video_sction" style="display:none;">
             <div class="video_large">
             </div>
              <div class="inner_video_sction">
                <ul>
               <!--    <li><img src="{{asset('/images/mute.png')}}" ></li> -->
                 <!--  <li><img src="{{asset('/images/end_video.png')}}"></li> -->
                  <li onclick="stopStream()" style="cursor: pointer"><img src="{{asset('/images/end_call.png')}}"></li>
                </ul>
              </div>
              <div class="video_small">
                <video id="myvideo" controls="true" playsinline autoplay   muted width="100%" height="100%"></video>
              
              </div>
          </div>

     </div>   
    <div class="page-inner col-xs-12 col-sm-9 col-md-10 mobile_page_inner">

    <div id="app" class="ui main container chatting-module" style="margin-top:30px;">
    
        <div class="ui grid">
            <div class="row">
                <div class="three wide column">
                    <div class="ui vertical pointing menu">

                     <div class="chat_module">
                      <aside class="user_messages">
                            <div class="chat_search">

                <input type="text" class="form-control" id="myInput" placeholder="Search in all messages.....">
            </div>
        
           

            <div class="top_tabbar_new">
                <ul class="nav nav-pills">
                 <li class="{{(empty($chat)) ? 'active' : ''}}"><a data-toggle="pill" href="#all">All</a></li>
                 <li class="{{(@$chat->type == 'user') ? 'active' : ''}}"><a data-toggle="pill" href="#conver">Conversation</a></li>
                 <li class="{{(@$chat->type == 'group') ? 'active' : ''}}"><a data-toggle="pill" href="#groups">Groups</a></li>

                </ul>
            </div>

            <div class="tab-content tab_msg_new">
              @if(!@$chat)
                <div id="all" class="tab-pane fade in active">
                @else
                    <div id="all" class="tab-pane fade">
                @endif
                  
                    <ul class="contnt_user">
                       <?php $i =1;?>
                     @foreach($users as $user)
                    
                      @if($user->id == @$receptorUser->id)

                                <li class="activeChat">
                            @else
                            <?php if($user->new_message == 1) {
                                $cls = 'usename newmsg';
                            } else {
                                $cls = 'usename';
                            } ?>
                              <li class="{{$cls}}">   
                            @endif

                              @if($user->picture)
                              <img src="{{$user->picture}}">
                                    @else
                             <img src="{{asset('placeholder.png')}}">
                                    @endif    
                               <a href="{{route('chat')}}/{{$user->id}}" > <h4>
                                    {{ $user->name }} 
                                    @if($user->online == true)
                                <span class="online "><i class="fa fa-circle online "></i></span>@else <span class="offline"><i class="fa fa-circle online"></i></span>@endif

                                </h4></a>
                                @if($user->new_message == 1)
                                    <img class="new_message" src="{{asset('New-icon.png')}}">
                                @endif
                    </li>
                       <?php $i = $i+1;?>
              @endforeach
           

      
    </ul>
            
</div>
           <div id="conver" class="tab-pane fade {{(@$chat->type == 'user') ? 'in active' : ''}}">

               <ul class="contnt_user">
                @foreach($chatusers as $user)
                    
                @if($user->id == @$receptorUser->id)
                                <li class="activeChat">
                            @else
                            <?php if($user->new_message == 1) {
                                $cls = 'usename newmsg';
                            } else {
                                $cls = 'usename';
                            } ?>
                              <li class="{{$cls}}">   
                            @endif
                              @if($user->picture)
                              <img src="{{$user->picture}}">
                                    @else
                             <img src="{{asset('placeholder.png')}}">
                                    @endif    
                               <a href="{{route('chat')}}/{{$user->id}}" > <h4>
                                    {{ $user->name }}
                                    @if($user->online == true)
                                <span class="online "><i class="fa fa-circle online "></i></span>@else <span class="offline"><i class="fa fa-circle online"></i></span>@endif

                                </h4></a>
                                @if($user->new_message == 1)
                                    <img class="new_message" src="{{asset('New-icon.png')}}">
                                @endif
                    </li>

                 
                       @endforeach
                       
                    </ul>
                </div>


                <div id="groups" class="tab-pane fade {{(@$chat->type == 'group') ? 'in active' : ''}}">

                
                    <div class="create_grup text-right">
                      <button type="button" class="btn btn-link" data-toggle="modal" data-target="#create_group" title="Create Group"><i class="fa fa-user-plus" aria-hidden="true"></i>
                     </button>
                    </div>

            <ul id="groupList" class="contnt_user">

                    @foreach($groups as $group)
                      <?php if($group->new_message == 1) {
                          $cls = 'usename newmsg';
                      } else {
                          $cls = 'usename';
                      } ?>
                @if(collect(request()->segments())->last()!="chat" &&  collect(request()->segments())->last()==$group->id)
                        <li class="active {{$cls}}">
                        @else
                      <li class="{{$cls}}">
                        @endif
                       
                             <img src="{{asset('placeholder.png')}}">
                              <a href="{{route('group', [$group->id])}}" > <h4>
                                    {{$group->group_name}}

                                </h4></a>
                                @if($group->new_message == 1)
                                    <img class="new_message" src="{{asset('New-icon.png')}}">
                                @endif
                           <!--  <p>Hp envy 1201 light weighted laptop</p> -->
                        </li>
                    @endforeach
                    </ul>
                </div>

                         </div>


                      </aside>


                     </div>



                  
                    </div>
                </div> 

               
                <div class="thirteen wide column right_chattng">
                    <div class="ui segment" style="padding: 1.5em 1.5em;">
                        <div class="ui comments" style="max-width: 100%;">
                       
                         @if(@$receptorUser->name )

                        
                        @if(@$chat && $chat->type === 'user')
                          <h3 class="ui dividing header titl_reltv"><i class="talk outline icon"></i>{{ @$receptorUser->name }}</h3>
                            <ul class="caht_optn_list">
								<li><a  onclick="start(event)"><img src="{{asset('video_calling.png')}}"></a></li>
								<li><span class="view_user_icon" data-toggle="modal" data-target="#screenshare_link" title="Share Screen"><img src="{{asset('screen_share.png')}}"></span></li>
							</ul>
                           <firebase-messages user-id="{{ Auth::user()->id }}" chat-id="{{ @$chat->id }}" receptor-name="{{ @$receptorUser->name }}" receptor-id="{{ @$receptorUser->id }}" chat-type="user" group-members="{{json_encode([$receptorUser->id => $receptorUser])}}" my-picture="{{Auth::user()->picture}}"></firebase-messages>
                        
                          @else
                           @if($chat)
                             <h3 class="ui dividing header"><i class="talk outline icon"></i>{{ @$chat->group_name }}</h3>
                            <ul class="caht_optn_list">
                            <li><a onclick="start(event)"><img src="{{asset('video_calling.png')}}"></a></li>
								<li><span class="view_user_icon" data-toggle="modal" data-target="#screenshare_link" title="Share Screen"><img src="{{asset('screen_share.png')}}"></span></li>
								<Li><span class="view_user_icon" data-toggle="modal" data-target="#get_group" id="{{@$chat->id}}" title="View Group"><img src="{{asset('view_group.png')}}"></span></li>
							</ul>
                          
                             <firebase-messages user-id="{{ Auth::user()->id }}" chat-id="{{ @$chat->id }}" receptor-name="{{ @$receptorUser->name }}" receptor-id="{{ @$receptorUser->id }}" chat-type="group" group-members="{{json_encode(@$groupMembers)}}" my-picture="{{Auth::user()->picture}}"></firebase-messages>
                            @else
                              <h3 class="ui dividing header"><i class="talk outline icon"></i>No group found</h3>
                        <div class="commnt_ui">
                         <h3 class="ui dividing header text-center welcome">Welcome to Live Chat</h3>
                         </div> 
                            @endif

                        @endif
                        @else
                        <h3 class="ui dividing header"><i class="talk outline icon"></i>No conversation found</h3>
                        <div class="commnt_ui">
                         <h3 class="ui dividing header text-center welcome">Welcome to Live Chat</h3>
                         </div>
                           @endif
                        </div>
                    </div>
                    
                </div>
             
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
      
    <div id="create_group" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Create Group</h4>
      </div>
      <div class="modal-body">
       <form action="{{route('save_group')}}" method="post" id="goup_form" enctype="multipart/form-data">
        <label for="name" class="control-label">Name</label>
        <input type="text" id="groupname" name="name" placeholder="Enter Name" class="form-control" required="true"><br>
       <input type="hidden" id="user_ids" name ="user_ids" placeholder="Enter Name" class="form-control" required="true"><br>
       <select id="first" data-placeholder="Choose user..." class="chosen-select form-control" multiple style="width:350px;" tabindex="4" >
          <option value=""></option> 
         @foreach($users as $user)
       <option value="{{$user->id}}">{{$user->name}}</option> 
         @endforeach
        </select>
         <div class="create_btn">
         <button type="button" name="submit" id="submit_group" class="btn btn-default">Submit</button>
         </div>
         </form>
      </div>
    </div>

  </div>
</div>


   <div id="get_group" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Manage Group</h4>
      </div>
      <div class="modal-body">
      <?php if(@$groupadmin->user_id1 != Auth::user()->id){?>

          <button type="button" name="submit" id="submit_btn2" class="btn btn-default leave_group" id="{{@$chat->id}}"><span class="leave_group" >Leave Group</span></button>
        <?php }?>
       <form action="{{route('update_group')}}" method="post" id="goup_form1" enctype="multipart/form-data">
      
       <input type="hidden" id="user_ids1" name ="user_ids1" placeholder="Enter Name" class="form-control" required="true"><br>
       <input type="hidden" id="group_id" name ="group_id" placeholder="Enter Name" class="form-control" required="true"><br>
      

       <select  id="first1"  name ="users[]" data-placeholder="Choose user..." class="chosen-select form-control multipleSelect" multiple style="width:350px;" tabindex="4" value="">
         <option></option>
        </select>
         <div class="create_btn">
         <button type="submit" name="submit" id="submit_btn1" class="btn btn-default">Submit</button>
         </div>
         </form>
      </div>
    </div>

  </div>
</div>
<div id="messages"></div>

<div id="screenshare_link" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Screen Sharing</h4>
      </div>
      <div class="modal-body text-center">
        <h5>Are you sure you want to share your screen?</h5>
       
        <?php $url = url('/screenshare')."/".base64_encode('user_'.Auth::id()); ?>
          <button onclick='shareconfirm("<?php echo $url; ?>")'>Yes</button>
          <button data-dismiss="modal">No</button>
        </div>
       
      </div>
    </div>

  </div>
</div>
 

@endsection

@section('script')
 <script src="https://cdn.temasys.com.sg/skylink/skylinkjs/latest/skylink.complete.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 
<script>
// video calling code
var roomidget="";
var roomid="";
var videurl="";
var myurl="";
var isgroup="";
var chat_type="";
 // var username  =  var notify_token = "";
$(document).ready(function(){
   isgroup = "<?php echo @$_GET['group'] ?>";
    chat_type = "<?php echo @$chat->type ?>";
   if(isgroup=="" && chat_type=="group"){
    
    isgroup = "true";
   }
  console.log("isgroup "+isgroup);
  console.log("chat_type "+chat_type);
    $("#submit_group").click(function(){
    var name =  $("#groupname").val();
      $.ajax({
      type: 'POST',
      data:{user_ids:selectedValue,name:name},
     url : "{{url('gettokens')}}",
    success: function (res) {
       var username = '{{Auth::user()->name}}';
      $.each(res, function( index, value ) {
     
      var notify_token =  value;
    
            var noti_data = {"data": {
    "notification": {
        "title": "Group",
        "body": username+" added you in new group",
        "icon": "{{url('/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png')}}",
       }}, "to":notify_token['notify_token']
      };
     notify_user(noti_data);
    
      });

    }

   });
      setInterval(function(){  location.reload();  }, 5000);
     
  // $("#goup_form").submit();
 })
 
  myurl = window.location.href;
  if(myurl.indexOf("?id=")>0){
     t = myurl.split('?')
      myurl =  t[0];
  }
  roomidget = "<?php echo @$_GET['id'] ?>";

if(roomidget==""){

 roomid  = getRoomId()
 var siteurl = '{{url("/")}}'+"/chat/"+'<?php echo Auth::id();?>';
<?php  if(@$chat->type === 'user'){ ?>
  videurl = siteurl+"?id="+roomid
 <?php }else{ ?>
   videurl = siteurl+"?id="+roomid+"&group=true"
  <?php } ?>
}else{
  roomid = roomidget
}
  if(roomidget!=""){
   if(isgroup!=""){
     $(".outer_group_sction").css('display','block');
   }else{
      $(".outer_video_sction").css('display','block');
   }
  
 $(".chatting-module").css('display','none');

  skylink.joinRoom({
    audio: true,
    video: true
  }, function (error, success) {
    if (error) {
  //     document.getElementById('status').innerHTML = 'Failed joining room.<br>' +
  // 'Error: ' + (error.error.message || error.error);
    } else {
      // document.getElementById('status').innerHTML = 'Joined room.';
    }
  });

  }
})
  var skylink = new Skylink();
  skylink.setLogLevel(4);
  skylink.on('peerJoined', function(peerId, peerInfo, isSelf) {
  if(isSelf) return; // We already have a video element for our video and don't need to create a new one.$("#mylist li").length
  console.log("Lengthli "+ $(".video_section li").length);
  if( $(".video_section li").length==3){
    alert("You can not join video call already 4 people in chat");
    return;
  }
  var vid = document.createElement('video');
  vid.autoplay = true;
  vid.controls = true;
 
  vid.setAttribute('playsinline', true);
  vid.setAttribute('width', "100%");
  vid.setAttribute('height', "100%");
  vid.muted = false; // Added to avoid feedback when testing locally
  vid.id = peerId;

     if(isgroup!=""){
        $(".video_section").append("<li class='box3 "+peerId+"'></li>");
      $("."+peerId).append(vid);
   }else{
       $(".video_large").append(vid);
   }
     
  
     

     
  
 
  
 });
skylink.on('incomingStream', function(peerId, stream, isSelf) {
  if(isSelf) return;
  var vid = document.getElementById(peerId);
  attachMediaStream(vid, stream);
});

skylink.on('peerLeft', function(peerId) {
    console.log("peerLeft "+peerId);
  var vid = document.getElementById(peerId);
  $("."+peerId).remove();
});

skylink.on('mediaAccessSuccess', function(stream) {
    if(isgroup!=""){
  var vid = document.getElementById('myvideo_group');
}else{
  var vid = document.getElementById('myvideo');
}
    attachMediaStream(vid, stream);
  console.log("roomidget  "+roomidget);
  if(roomidget==""){
  
     var person = prompt("Please share link with your friends to start call:", videurl);
     var username = '{{Auth::user()->name}}';
      var notify_token =  '{{@$receptorUser->notify_token}}';
       videoconfirm(videurl);
            var noti_data = {"data": {
    "notification": {
        "title": "Video Caling",
        "body": username+" wants to start a video call",
        "icon": "https://www.cjclive.com/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png",
    }}, "to":notify_token
  };
          notify_user(noti_data);
         if(chat_type=="group"){
     $(".outer_group_sction").css('display','block');
   }else{
      $(".outer_video_sction").css('display','block');
   }
 $(".chatting-module").css('display','none');    
  // if (person == null || person == "") {
  //   txt = "User cancelled the prompt.";
  // } else {
  //   txt = "Hello " + person + "! How are you today?";
  // }
  // document.getElementById("demo").innerHTML = txt;
    // alert(videurl)
  }

});

skylink.init({
  apiKey: "{{envfile('SKYLINK_APIKEY')}}", // Get your own key at https://console.temasys.io
  defaultRoom: roomid
}, function (error, success) {
 
  if (error) {
    // document.getElementById('status').innerHTML = 'Failed retrieval for room information.<br>Error: ' + (error.error.message || error.error);
  } else {
    //    document.getElementById('status').innerHTML = 'Room information has been loaded. Room is ready for user to join.';
    // document.getElementById('start').style.display = 'block';
  }
});
skylink.on("peerLeft", function (peerId, peerInfo, isSelf) {
  console.log("peerLeft "+peerId);
    // if (!isSelf) {
    //   var peerVideo = document.getElementById(peerId);
    //   // do a check if peerVideo exists first
    //   if (peerVideo) {
    //     document.getElementById("peersVideo").removeChild(peerVideo);
    //   } else {
    //     console.error("Peer video for " + peerId + " is not found.");
    //   }
    // }
  });
function chatNotification() {
    var username = "{{Auth::user()->name}}";
    var user = "{{Auth::user()->id}}";
    var notify_token =  '{{@$receptorUser->notify_token}}';
    var noti_data = {"data": {
      "notification": {
          "title": "New message on CJC Live",
          "body": " You have received a message from " + username,
          "icon": "{{url('/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png')}}",
           "user":user,
      }}, "to":notify_token
    };
    notify_user(noti_data);
  }
  
 function stopStream () {
    skylink.stopStream();
    window.location.href=myurl;
  }
  function videoconfirm(url) {

      var msg = "I want to start a video call. Please visit to "+url;
      console.log(msg);
      var chatId = "<?php echo @$chat->id; ?>";
      var userId = "<?php echo Auth::id();?>";

      database.ref("/chats/" + chatId).push({
                        userId: userId,
                        text: msg,
                        type: "text",
                        date: moment().format()
                    }).then(function() {
                        resetChatReadBy(chatId);
                        chatNotification();
                    })
             
      // $("#video_link").modal('show');
     
    }
function start(event) {
  event.target.style.visibility = 'hidden';
 

  skylink.joinRoom({
    audio: true,
    video: true
  }, function (error, success) {
    if (error) {
  //     document.getElementById('status').innerHTML = 'Failed joining room.<br>' +
  // 'Error: ' + (error.error.message || error.error);
    } else {
      // document.getElementById('status').innerHTML = 'Joined room.';
    }
  });



}

function myFunction() {
  var txt;
  var person = prompt("Please enter your name:", "Harry Potter");
  if (person == null || person == "") {
    txt = "User cancelled the prompt.";
  } else {
    txt = "Hello " + person + "! How are you today?";
  }
  document.getElementById("demo").innerHTML = txt;
}

/* Helper functions */

function getRoomId() {
  var roomId = document.cookie.match(/roomId=([a-z0-9-]{36})/);
  // if(roomId) {
  //   return roomId[1];
  // }
  // else {
    roomId = skylink.generateUUID();
    var date = new Date();
    date.setTime(date.getTime() + (30*24*60*60*1000));
    document.cookie = 'roomId=' + roomId + '; expires=' + date.toGMTString() + '; path=/';
    return roomId;
  // }
};



//Video calling code end



$(".chosen-select").chosen();
<?php if(@$groupadmin->user_id1 != Auth::user()->id){?>
  var is = '{{@$groupadmin->user_id1}}';
 
  $('#goup_form1 .chosen-select').prop('disabled', true).trigger("chosen:updated");
    $("#submit_btn1").css('display','none');
  <?php } ?>
$('button').click(function(){
        $(".chosen-select").val('').trigger("chosen:updated");
});
var selectedValue = [];
$('#goup_form .chosen-select').on('change', function(evt, params) {
 if(params.deselected){
   selectedValue = $.grep(selectedValue, function(value) {
  return value != params.selected;
});
   console.log(params.deselected);
   console.log(selectedValue);

} 
if(params.selected){
  selectedValue.push(params.selected);
  
    console.log(selectedValue);
    
  }

   console.log(selectedValue);
   $("#user_ids").val(selectedValue);
   $("#first").val(selectedValue);
});
var selectedValue1 = [];
$('#goup_form1 .chosen-select').on('change', function(evt, params) {
 if(params.deselected){
  console.log(params.deselected);
  console.log(selectedValue1);
  // selectedValue1.splice($.inArray(params.deselected, selectedValue1),1);

  selectedValue1 = $.grep(selectedValue1, function(value) {
  return value != params.deselected;
});
  
   console.log(selectedValue1);

} 
if(params.selected){
  selectedValue1.push(params.selected);
  
    console.log(selectedValue1);
    
  }

 
   $("#user_ids1").val(selectedValue1);
  
});



$(document).ready(function(){
    $(".textfield").attr('data-emojiable',true);
      $(".leave_group").click(function(){
          $.ajax({
          type: 'GET',
           url : "{{url('leave_group')}}/"+$(this).attr('id'),
          success: function (res) {
            
          window.location.href="{{route('chat')}}";

              
            
          },
          error:function(e){
            
          }
      });


      });
      var allUsers = <?php echo $users;?>;
     $(".view_user_icon").click(function(){
      $("#group_id").val($(this).attr('id'));
       $.ajax({
          type: 'GET',
           url : "{{url('restuser')}}/"+$(this).attr('id'),
          success: function (res) {
             
        

                $.each(allUsers, function(i,v) {
                  if(v.id==1002){
                   console.log(res['members']);
                   console.log(v.id);
                   console.log(res['members'].indexOf(v.id));
                       }
                  if(res['members'].indexOf(String(v.id))>-1){
                   
                    $('#first1').append($("<option selected></option>")
                  .attr("value",v.id)
                  .text(v.name));
                    console.log("v.name");
                  }else{
          
                     $('#first1').append($("<option></option>")
                  .attr("value",v.id)
                  .text(v.name)); 
                  }
               

                 $('#first1').trigger("chosen:updated");
            });
           
          },
          error:function(e){
            console.log("Failed to get stores list",e);
          }
      });
      $(".multipleSelect").trigger("chosen:updated");
      
     });
  $("#myInput").on("keyup", function() {
    var p = $('.nav-pills').find('.active').find('a');
     var t = p.attr('href');
    var m  = t.split("#");

    var value = $(this).val().toLowerCase();
    $(".usename").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
 <script src="{{ asset('js/firebase.js') }}"></script>
 <script src="{{ asset('js/notify.min.js') }}"></script>

   <!--  <script src="https://www.gstatic.com/firebasejs/4.5.0/firebase.js"></script> -->
    <script>
      
        // Initialize Firebase

  var firebaseConfig = {
    apiKey: "{{envfile('FIREBASE_APIKEY')}}",
    authDomain: "{{envfile('FIREBASE_AUTHDOMAIN')}}",
    databaseURL: "{{envfile('FIREBASE_DATABASEURL')}}",
    projectId: "{{envfile('FIREBASE_PROJECTID')}}",
    storageBucket: "{{envfile('FIREBASE_STORAGEBUCKET')}}",
    messagingSenderId: "{{envfile('FIREBASE_MESSAGINGSENDERID')}}",
    appId: "{{envfile('FIREBASE_APPID')}}"
  };



        firebase.initializeApp(firebaseConfig);

        const database = firebase.database();
          const messaging = firebase.messaging();
        messaging
            .requestPermission()
            .then(async function () {
                
                console.log("Notification permission granted.");
                console.info('token: ', messaging);
                // get the token in the form of promise
                return messaging.getToken()
            })
            .then(function(token) {
            
                    $.ajax({
                    type: 'POST',
                   data:{
                  notify_token: token,
                       },
                    url : "{{url('notify_token')}}",
                    success: function (res) {

                     
                      
                    },
                    })
              
            })
            .catch(function (err) {
               
                
            });

  messaging.onMessage(function(payload) {

      console.log("Message received. ", payload);
      getConversationUser();
      getAjaxGroups();
      var notify =  JSON.parse(payload.data.notification);
      pushNoti({name: 'CJC Live', text: notify.body})

      $.notify.addStyle('happyblue', {
        html: "<div><img src='"+APP_URL+"/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png' style='width:30px;margin-right:5px;'><span data-notify-text/></div><br>",
        classes: {
          base: {
          
            "background-color": "#f7f7f7",
            "padding": "10px 10px",
            "max-width":"250px",
            "width":"100%",
            "font-size":"12px",
              "box-shadow":"2px 3px 4px rgab(0,0,0,0.10)",
              "border":"1px solid #ccc",
              "border-radius":"5px",
              "text-transform":"capitalize"
          
          },
          superblue: {
            "color": "#333",
            "font-weight":"bold",
            "background-color": "#f7f7f7",
            "font-size":"12px",
            "box-shadow":"2px 3px 4px rgab(0,0,0,0.10)",
            "border":"1px solid #ccc"

          }
        }
      });
      $.notify(notify.body, {
        style: 'happyblue',
        className: 'superblue'
      });
      console.log("Message received. ", notify.body);
            
  });

  function groupchatNotification() {
    var group_name = "{{ @$chat->group_name}}";
    var username = "{{Auth::user()->name}}";
    var user = "{{Auth::user()->id}}";
    var app = <?php echo json_encode(@$groupMembers); ?>;
    
    // var obj = json_decode(res);
    // var obj1 = JSON.stringify(res);
    // console.log(JSON.parse(obj1.replace(/&quot;/g,'"')););
       $.each(app, function( index, value ) {
       
      var notify_token =  value;
   // console.log(notify_token['notify_token']);
            var noti_data = {"data": {
    "notification": {
        "title": "Group",
        "body": username+" send a new message in "+group_name+" group",
        "icon": "{{url('/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png')}}",
       }}, "to":notify_token['notify_token']
      };
     notify_user(noti_data);
    
   });
   
  }
 function notify_user(data){
       var mydta=data;
      var datauser =  mydta;
        
      $.ajax({

    // headers: {
    //     'Authorization': 'key=AAAAOCPhAlg:APA91bGjmVsqcl2k4R7272ZgwPilZDUeYbANek0WIFkiX3qYwx5HcfaQi7zD1P-c1fDNeDfzERZuw_T5ApiRwllu63oVha3glVAXeGyNQone2DO0tkIT64ZtyCA0Sw5Qm0PKXTnDFIIo',

    //  },
      type: 'POST',
      data:datauser,
     url : "{{url('notify_curl')}}",
    
      success: function (res) {
      console.log("notification sent"); 
           }
   });
    }
    </script>
    <script>
    function resetChatReadBy(chatId) {
        $.post(APP_URL+"/reasetchatreadby",
        {chatId},
        function(data, status){
            if(status == 'success'){
              getConversationUser();
              getAjaxGroups();
                console.info("Read status updated successfully")
            } else {
                console.info("Failed to update Read status")
            }
        });
    }

    // setInterval(() => {
    //   getConversationUser();
    //   getAjaxGroups();
    // }, 15000);
    var chatRoute = "<?php echo route('chat');?>";
    var receptorUser = "<?php echo @$receptorUser->id;?>";

    function getConversationUser() {
      $.get(APP_URL+"/get_ajax_conversation_user",
        function(data, status){
            if(status == 'success') {
                console.info("Read status updated successfully")
                var html1 = '<ul class="contnt_user">';
                data.forEach( function($user) {
                  if($user.id == receptorUser) {
                    html1 += '<li class="activeChat">';
                  } else {
                    if($user.new_message == 1)
                      var $cls = 'usename newmsg';
                    else
                      var $cls = 'usename';

                    html1 += '<li class="'+$cls+'">';
                  }
                  if($user.picture)
                    html1 += '<img src="'+$user.picture+'">';
                  else
                    html1 += '<img src="'+APP_URL+'/placeholder.png">';
  
                  html1 += '<a href="'+chatRoute+'/'+$user.id+'" > <h4>';
                  html1 +=  $user.name;

                  if($user.online == true)
                    html1 += '<span class="online"><i class="fa fa-circle online "></i></span>';
                  else 
                    html1 += '<span class="offline"><i class="fa fa-circle online"></i></span>';

                  html1 += '</h4></a>';

                  if($user.new_message == 1 && $user.id != receptorUser)
                      html1 += '<img class="new_message" src="'+APP_URL+'/New-icon.png">';

                  html1 += '</li>';

                });
                       
                html1 += '</ul>';
                $('#conver').html(html1);
            } else 
                console.info("Failed to update Read status")
        });
    }

    function getAjaxGroups() {
      $.get(APP_URL+"/get_ajax_groups",
        function(data, status){
            if(status == 'success') {
                var html1 = '';
                data.forEach( function($group) {
                  if($group.new_message == 1)
                    var $cls = 'usename newmsg';
                  else
                    var $cls = 'usename';
                  
                  if($group.type == 'group' && $group.id == "<?php echo @$groupadmin->id;?>")
                    html1 += '<li class="active '+$cls+'">';
                  else
                    html1 += '<li class="'+$cls+'">';

                    html1 += '<img src="'+APP_URL+'/placeholder.png">';
  
                  html1 += '<a href="'+APP_URL+'/group/'+$group.id+'" > <h4>';
                  html1 +=  $group.group_name;
                  html1 += '</h4></a>';

                  if($group.new_message == 1 && $group.id != receptorUser)
                      html1 += '<img class="new_message" src="'+APP_URL+'/New-icon.png">';

                  html1 += '</li>';

                });

                $('#groupList').html(html1);
            } else 
                console.info("Failed to update Read status")
        });
    }

    function updateChatReadBy(chatId) {
      $.post(APP_URL+"/updateChatReadBy",
        {chatId},
        function(data, status){
            if(status == 'success')
                console.info("Read status updated successfully")
            else 
                console.info("Failed to update Read status")
        });
    }

    function shareconfirm(url) {
      var msg = "I have shared my screen. Please visit to "+url;
      var chatId = "<?php echo @$chat->id; ?>";
      var userId = "<?php echo Auth::id();?>";

      database.ref("/chats/" + chatId).push({
                        userId: userId,
                        text: msg,
                        type: "text",
                        date: moment().format()
                    }).then(function() {
                        resetChatReadBy(chatId);
                        chatNotification();
                    })
                    
      $("#screenshare_link").modal('hide');
      window.open(url);
    }
    
    function getURLFromText(text)
    {
      let Arr = [];
     var url = text.match(/\bhttps?:\/\/\S+/gi);
     if(url && url[0]) {
      var txt = text.split(url[0]);
      if(txt && txt[0]) {
        Arr.push(txt[0]);
      } else {
        Arr.push("");
      }
      Arr.push(url[0]);

     }
     return Arr
    }
  
    function isLink(text)
    {
      var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
      var text1=text.replace(exp, "<a target='_blank' href='$1'>$1</a>");
      var exp2 =/(^|[^\/])(www\.[\S]+(\b|$))/gim;
      var convertedText = text1.replace(exp2, '$1<a target="_blank" href="http://$2">$2</a>');
      if(convertedText == text) {
        return false;
      } else {
        return true;
      }
    }
    </script>

<script src="{{ asset('js/myapp.js') }}"></script>

<script>
  function pushNoti(e) {
    pushjs.create(e.name, {
        body: e.text,
      timeout: 4e3,
      onClick: function() {
          window.focus(), this.close()
      }

    })
  }
</script>
@endsection