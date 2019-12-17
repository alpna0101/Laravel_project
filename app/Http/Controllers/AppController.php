<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Chat;
use App\GroupMember;
use Auth;
use App\Notification;

class AppController extends Controller
{
      public function __construct()
    {
    
       $this->middleware(['auth']);
      
    }
    public function index()
    {
      
        $users = User::where('id', '!=', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(8);
        return view('app.inicio', compact('users'));
    }

    public function chatUsers() {
      
         $chatusers1 = DB::table('chats as c')
                    ->join('users as u','u.id','=','c.user_id2')
                    ->where('c.user_id1','=', Auth::user()->id)
                    ->where('c.type','user')
                    ->where('c.user_id2','!=',Auth::user()->id)
                    ->select('u.*','c.updated_at as message_at',DB::raw("(CASE WHEN (find_in_set(".Auth::user()->id.",c.read_by)) THEN 0 ELSE 1 END) as new_message")); 
        $chatusers = DB::table('chats as c')
                    ->join('users as u','u.id','=','c.user_id1')
                    ->where('c.user_id2','=', Auth::user()->id)
                    ->where('c.user_id1','!=', Auth::user()->id)
                    ->where('c.type','user')
                    ->where('c.user_id1','!=',Auth::user()->id)
                    ->select('u.*','c.updated_at as message_at',DB::raw("(CASE WHEN (find_in_set(".Auth::user()->id.",c.read_by)) THEN 0 ELSE 1 END) as new_message"))
                    ->union($chatusers1)
                    ->orderBy('message_at','DESC')
                    ->get(); 
        return $chatusers;
    }
    

    public function usersChat($userName="")
    {
        if($userName == Auth::id()) {
            return redirect(route('chat'));
        }
        $groups = $this->getUsersGroup($userName);
        $receptorUser = User::where('id', '=', $userName)->first();
        $users = User::where('id', '!=', Auth::user()->id)->orderBy('online','desc')->get();
     
        if($receptorUser == null) {
            $chatusers = $this->chatUsers();
            return view('app.chat', compact('receptorUser', 'chat', 'users','chatusers','groups'));
        }else {
        
            $chat = $this->hasChatWith($receptorUser->id); 
            if($chat) {
                if($chat->read_by) {
                    $arr = explode(',',$chat->read_by);
                    if(!in_array(Auth::id(),$arr)) {
                        DB::table('chats')->where('id',$chat->id)->update(['read_by'=>$chat->read_by.",".Auth::id()]);
                    } 
                } else {
                    DB::table('chats')->where('id',$chat->id)->update(['read_by'=>Auth::id()]);
                }
            }
            $chatusers = $this->chatUsers();
         
            return view('app.chat', compact('receptorUser', 'chat', 'users','chatusers','groups','groupMembers'));
        }
    }

    public function hasChatWith($userId)
    {
        $chat = Chat::where('user_id1', Auth::user()->id)
            ->where('user_id2', $userId)
            ->orWhere('user_id1', $userId)
            ->where('user_id2', Auth::user()->id)
            ->get();
        if(!$chat->isEmpty()){

            return $chat->first();
        }else{
            return $this->createChat(Auth::user()->id, $userId);;
        }
    }

    public function createChat($userId1, $userId2)
    {

        $chat = Chat::create([
            'user_id1' => $userId1,
            'user_id2' => $userId2,
            'type'     => 'user',
            'read_by'  => $userId1
        ]);
        return $chat;
    }


    public function getUsersGroup() {

        $groups = DB::table('chats as c')
                ->join('group_members as gm','gm.group_id','=','c.id')
                ->where('type', 'group')
                ->where('gm.status',1)
                ->where('gm.user_id',Auth::id())
                ->select('c.id','c.user_id1 as owner','c.type','c.group_name','c.created_at','c.updated_at',DB::raw("(CASE WHEN (find_in_set(".Auth::user()->id.",c.read_by)) THEN 0 ELSE 1 END) as new_message"))
                ->groupBy('c.id')
                ->orderBy('c.updated_at','DESC')
                ->get();
        return $groups;
    }

    public function getGroupMembers($groupId) {
        $members = DB::table('group_members as gm')
                    ->join('users as u','u.id','=','gm.user_id')
                    ->where('gm.group_id',$groupId)
                    //->where('gm.status',1)
                    ->select('u.id','u.name','u.picture','u.notify_token')
                    ->get();
        $data = [];
        if($members) {
            foreach($members as $member) {
                $data[$member->id] = $member;
            }
        }
        return $data;
    }
   public function restuser($groupId){
     $members = $this->getGroupMembers($groupId);
     //$chat = Chat::find($groupId);
      $data = [];
        if($members) {
            foreach($members as $member) {

                $data['members'][] = $member->id;
            }
        }
        // $data['users'] = User::select('id', 'name')
             
        //      ->orderBy('name')
        //        ->get();
        return $data;
   }
    public function groupChat($groupId="")
    {

        $groupadmin = Chat::where('id', '=', $groupId)->first();
        $receptorUser = User::where('id', '=', Auth::id())->first();
        $groupMembers = $this->getGroupMembers($groupId);

        $users = User::where('id', '!=', Auth::user()->id)->orderBy('online','desc')->get();
        $chatusers = $this->chatUsers();
        $chat = $this->hasGroupChatWith($groupId,Auth::id()); 
 
        if($chat) {
            if($chat->read_by) {
                $arr = explode(',',$chat->read_by);
                if(!in_array(Auth::id(),$arr)) {
                    DB::table('chats')->where('id',$chat->id)->update(['read_by'=>$chat->read_by.",".Auth::id()]);
                } 
            } else {
                DB::table('chats')->where('id',$chat->id)->update(['read_by'=>Auth::id()]);
            }
        }
        $groups = $this->getUsersGroup();
   
         return view('app.chat', compact('receptorUser','chat', 'users','chatusers','groups','groupMembers','groupadmin'));
        
    }
       public function get_notify_token(Request $request){
     
        // print_r($request->all());die;
        // $request->user_ids1 = $request->user_ids1.",".Auth::user()->id;
        $groupId =      $request->group_id;
       $members = $this->restuser($groupId);
      $allmember = $request->user_ids1;
       foreach($members['members'] as $m1){
         if (!in_array($m1, $allmember)) {
               if($m1!=Auth::user()->id){
                GroupMember::where('group_id',$request->group_id)->where('user_id',$m1)->delete();
                 }
            }
       }
    
     foreach($allmember as $m){
        if (!in_array($m, $members['members'])) {
        if($m>0){
         $gmember = new GroupMember;
         $gmember->user_id = $m;
         $gmember->group_id = $request->group_id;
         $gmember->save();
     }
    }
 }
  $users = DB::table('users')->select("notify_token")
             ->whereIn('id', $request->user_ids)
             ->get();
    return $users;
    }
//   public function update_group(Request $request){

//       $request->user_ids1 = $request->user_ids1.",".Auth::user()->id;
//         $groupId =      $request->group_id;
//     $members = $this->restuser($groupId);
//       $allmember =  explode(",",$request->user_ids1);
//        foreach($members['members'] as $m1){
//          if (!in_array($m1, $allmember)) {
               
//                 GroupMember::where('group_id',$request->group_id)->where('user_id',$m1)->delete();
//             }
//        }
    
//      foreach($allmember as $m){
//         if (!in_array($m, $members['members'])) {
//         if($m>0){
//          $gmember = new GroupMember;
//          $gmember->user_id = $m;
//          $gmember->group_id = $request->group_id;
//          $gmember->save();
//      }
//     }
//  }
//   return redirect(route('group',$request->group_id));
// }
   public function leave_group($id) { 
     GroupMember::where('group_id',$id)->where('user_id',Auth::user()->id)->delete();
    return redirect(route('chat'));
   }
    public function save_group(Request $request){

        $chat = Chat::create([
            'user_id1' => Auth::user()->id,
            'user_id2' =>0,
            'type' =>'group',
            'group_name' =>$request->name,
        ]);
        $gmember = new GroupMember;
         $gmember->user_id =  Auth::user()->id;
         $gmember->group_id = $chat->id;
         $gmember->save();
         $allmember =  explode(",",$request->user_ids);

       foreach($allmember as $m){
        if($m>0){
         $gmember = new GroupMember;
         $gmember->user_id = $m;
         $gmember->group_id = $chat->id;
         $gmember->save();
     }
         }
        return redirect(route('group',$chat->id));
        
    }
    public function hasGroupChatWith($groupId,$userId)
    {
     
        $chat = Chat::join('group_members as gm','gm.group_id','=','chats.id')
            ->where('chats.id', $groupId)
            ->where('gm.user_id', $userId)
            ->select('chats.*')
            ->first();
     
            return $chat;    

    }

    // public function createGroupChat($groupId, $userId)
    // {
    //     $member = DB::table('group_members')->where('group_id', $groupId)->where('user_id', $userId)->first();
    //     if($member) {
    //         DB::table('group_members')->where('id', $member->id)->update(['status'=>1]);
    //         $gm = DB::table('group_members')->where('id', $member->id)->first();
    //     } else {
    //         $gm = DB::table('group_members')->insert(['group_id'=>$groupId, 'user_id'=>$userId]);
    //     }

    //     return $gm;
    // }

    public function reasetChatReadBy(Request $req) {
        if(Auth::id() && @$req->chatId) {
            return DB::table('chats')->where('id',$req->chatId)->update(['read_by'=>Auth::id(),'updated_at'=>date('Y-m-d H:i:s')]);
        }
    }

    public function updateChatReadBy(Request $req) {
        if(Auth::id() && @$req->chatId) {
            $chat = DB::table('chats')->where('id',$req->chatId)->first();
            if($chat) {
                if($chat->read_by) {
                    $arr = explode(',',$chat->read_by);
                    if(!in_array(Auth::id(),$arr)) {
                        DB::table('chats')->where('id',$chat->id)->update(['read_by'=>$chat->read_by.",".Auth::id()]);
                    } 
                } else {
                    DB::table('chats')->where('id',$chat->id)->update(['read_by'=>Auth::id()]);
                }
            }
        }
    }

    public function screenShare($room) {
        //echo base64_encode('user_722');
        $roomArr = explode("_",base64_decode($room));
        //print_r($roomArr);
        if($roomArr && count($roomArr) == 2) {
           $adminId = $roomArr[1];
            
            if($adminId == Auth::id()) {
                $isAdmin = true;
            } else {
                $isAdmin = false;
            }
            return view('app.screenshare', compact('room','adminId','isAdmin'));
        }
    }

    public function notify_token(Request $request) {
        $user = Auth::user();
        $user->notify_token = $request->notify_token;
        $user->save();
  }
   public function notify_curl(Request $request){
       $req = $request->all();
       $reciever = DB::table('users')->where('notify_token',$req['to'])->first();

        $someJSON = json_encode($request->all());
         $server_key = envfile('SERVER_APIKEY');
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $someJSON);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: key='.$server_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            if($reciever) {
                $data = array('sender_id'=>Auth::id(),'reciever_id'=>$reciever->id,'label'=>'New Message','message'=>$req['data']['notification']['body'],'type'=>'chat');
                $this->saveNotification($data);
            }
        }
        curl_close($ch);

    }

    function saveNotification($data) {
        Notification::where('sender_id', $data['sender_id'])->where('reciever_id',$data['reciever_id'])->where('type',$data['type'])->where('label',$data['label'])->delete();

        $notification =  new Notification;
        $notification->sender_id = $data['sender_id'];
        $notification->reciever_id = $data['reciever_id'];
        $notification->transaction_id = NULL;
        $notification->label = $data['label'];
        $notification->message =   $data['message'];
        $notification->type =   $data['type'];
        $notification->save();
    }

}
