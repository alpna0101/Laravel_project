<?php

namespace App\Http\Controllers;

use App\Repositories\VideoTapeRepository as VideoRepo;

use App\Repositories\CommonRepository as CommonRepo;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use App\Repositories\PaymentRepository as PaymentRepo;

use App\Helpers\AppJwt;

use App\Jobs\sendPushNotification;

use App\Jobs\UserPointJob;

use Log;

use Hash;

use Validator;

use File;

use DB;

use Auth;

use Setting;

use App\Flag;

use App\User;

use App\UserRating;

use App\Wishlist;

use App\UserHistory;

use App\ChannelSubscription;

use App\Page;

use App\Jobs\NormalPushNotification;

use App\VideoTape;

use App\Redeem;

use App\RedeemRequest;

use App\Channel;

use App\LikeDislikeVideo;

use App\Card;
use App\UserPoint;

use App\Subscription;

use App\UserPayment;

use App\LiveVideo;

use App\LiveVideoPayment;

use App\ChatMessage;

use App\Viewer;

use Exception;

use App\PayPerView;

use App\Category;

use App\Tag;

use App\VideoTapeTag;

use App\Coupon;

use App\UserCoupon;

use App\CustomLiveVideo;

use App\UserReferrer;

use App\Referral;

use App\UserSubscription;

use App\UserSubscriptionPayment;
use App\UserToken;
use App\transaction;
use App\SellerToken;

use App\CoinCurrency;
use App\PaymentMethod;
use Mail;
use App\Order;
use App\Notification;

class UserApiController extends Controller {

    public function __construct(Request $request) {

         $this->middleware('UserApiVal' , array('except' => [
                'register' , 
                'login' ,
                'subsciption_update_payment_status', 
                'order_payment_status',
                'add_update_coin_market',
                'forgot_password',
                'search_video' , 
                'privacy',
                'about' , 
                'terms',
                'contact', 
                'home', 
                'trending' , 
                'getSingleVideo', 
                'get_channel_videos' ,  
                'help', 
                'single_video', 
                'reasons' ,
                'search_video', 
                'video_detail',
                'categories_videos',
                'tags_videos',
                'categories_channels_list',
                'get_live_url',
                'live_videos',
                'oldvideo_points',
                'sponsored_videos',
                'makingMoney'
                ]));

    }

    
    public function broadcast(Request $request) {
        
        $validator = Validator::make($request->all(),array(
                'title' => 'required',
                'amount' => 'numeric',
                'payment_status'=>'required',
               // 'type' => 'required',
                'description'=>'required',
                'channel_id'=>'required|exists:channels,id',
                'user_id'=>'required|exists:users,id',
            )
        );
        
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => 001];

        } else {


            $this->erase_streaming($request);

            $model = LiveVideo::where('user_id', $request->id)->where('status', DEFAULT_FALSE)->first();

            if(!$model) {

                $model = new LiveVideo;
                $model->title = $request->title;
                $model->payment_status = $request->payment_status;
                $model->type = $request->type ? $request->type : TYPE_PUBLIC;
                $model->channel_id = $request->channel_id;
                $model->amount = 0;
                $model->browser_name = $request->browser ? $request->browser : '';
                if($request->payment_status) {

                    $model->amount = ($request->amount > 0) ? $request->amount : 1;

                }
                $model->description = ($request->has('description')) ? $request->description : null;
                $model->is_streaming = DEFAULT_TRUE;
                $model->status = DEFAULT_FALSE;
                $model->user_id = $request->user_id;
                $model->virtual_id = md5(time());
                $model->unique_id = $model->title;
                $model->snapshot = asset('images/live_stream.jpg');
                $model->save();

                /*// $usrModel

                $userModel = User::find($request->id);


                $appSettings = json_encode([
                    'SOCKET_URL' => Setting::get('SOCKET_URL'),
                    'CHAT_ROOM_ID' => isset($model) ? $model->id : null,
                    'BASE_URL' => Setting::get('BASE_URL'),
                    'TURN_CONFIG' => [],
                    'TOKEN' => $request->token,
                    'USER_PICTURE'=>$userModel->chat_picture,
                    'NAME'=>$userModel->name,
                    'CLASS'=>'left',
                    'USER' => ['id' => $request->id, 'role' => "model"],
                    'VIDEO_PAYMENT'=>null,
                ]);*/

                if ($model) {


                    $destination_ip = Setting::get('wowza_ip_address');

                    if (Setting::get('kurento_socket_url') && $destination_ip) {

                        $streamer_file = $model->user_id.'-'.$model->id.'.sdp';  

                        $last = LiveVideo::orderBy('port_no', 'desc')->first();

                        $destination_port = 44104;

                        if ($last) {

                            if ($last->port_no) {

                                $destination_port = $last->port_no + 2;

                            }

                        }

                        $model->port_no = $destination_port;


                        File::isDirectory(public_path().'/uploads/sdp_files') or File::makeDirectory(public_path().'/uploads/sdp_files', 0777, true, true);

                        if (!file_exists(public_path()."/uploads/sdp_files/".$model->user_id.'-'.$model->id.".sdp")) {

                            $myfile = fopen(public_path()."/uploads/sdp_files/".$model->user_id.'-'.$model->id.".sdp", "w") or die("Unable to open file!");

                            $data = "v=0\n"
                                    ."o=- 0 0 IN IP4 " . $destination_ip . "\n"
                                    . "s=Kurento\n"
                                    . "c=IN IP4 " . $destination_ip . "\n"
                                    . "t=0 0\n"
                                    . "m=video " . $destination_port . " RTP/AVP 100\n"
                                    . "a=rtpmap:100 H264/90000\n";

                            fwrite($myfile, $data);

                            fclose($myfile);

                            $filepath = public_path()."/uploads/sdp_files/".$model->user_id.'-'.$model->id.".sdp";

                            shell_exec("mv $filepath /usr/local/WowzaStreamingEngine/content/");

                            $this->connectStream($model->user_id.'-'.$model->id);

                        }

                    } else {

                        $streamer_file = "";
                    }


                    Log::info("device type ".$request->device_type);

                    if ($request->device_type == DEVICE_WEB) {

                        // $model->video_url = $streamer_file ? 'http://'.Setting::get('cross_platform_url').'/'.Setting::get('wowza_app_name').'/'.$streamer_file.'/playlist.m3u8';

                    } else if($request->device_type == DEVICE_IOS){

                        // $model->video_url = 'http://'.Setting::get('cross_platform_url').'/'.Setting::get('wowza_app_name').'/'.$streamer_file.'/playlist.m3u8';

                        $model->browser_name = $request->device_type;

                    }

                    $model->video_url = $streamer_file;


                   // $model->video_url = Setting::get('mobile_rtsp').$user->id.'_'.$model->id;

                    $model->save();

                    $response_array = [
                        'success' => true , 

                        'data' => $model, 

                        /*'appSettings'=> $appSettings, */

                        'message'=>tr('video_broadcating_success')
                    ];

                    
                } else {
                    $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(003) , 'error_code' => 003];
                }

            } else {

                $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(170), 'error_code'=>170];
            }
        }
        return response()->json($response_array,200);

    }

    /**** Live Videos Api *************/

    public function live_videos(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'skip'=>'required|numeric',
                'browser'=>'required',
                'device_type'=>'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS.','.DEVICE_WEB,
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false, 'error_messages' => $errors , 'error_messages' => $errors,'error_code' => 101];

        } else {

                $query = LiveVideo::where('is_streaming', DEFAULT_TRUE)
                        ->where('live_videos.status', DEFAULT_FALSE)
                        ->videoResponse()
                        ->leftJoin('users' , 'users.id' ,'=' , 'live_videos.user_id')
                        ->leftJoin('channels' , 'channels.id' ,'=' , 'live_videos.channel_id')
                        ->orderBy('live_videos.created_at', 'desc')
                        ->skip($request->skip)
                        ->take(Setting::get('admin_take_count' ,12));

                if($request->id) {

                    $query->whereNotIn('live_videos.user_id', [$request->id]);
                }

                $model = $query->get();

                $values = [];


                foreach ($model as $key => $value) {

                    $videopayment = LiveVideoPayment::where('live_video_id', $value->video_id)
                        ->where('live_video_viewer_id', $request->id)
                        ->where('status',DEFAULT_TRUE)->first();

                        // dd($value);

                    $null_safe_value = [
                        "video_image"=> $value->snapshot,
                        "channel_image"=> $value->channel_image ? $value->channel_image : '',
                        "title"=> $value->title,
                        "channel_name"=> $value->channel_name ? $value->channel_name : '',
                        "watch_count"=> $value->viewers,
                      // "video"=> $value->video_url ? $value->video_url : VideoRepo::getUrl($value, $request),
                        "video_tape_id"=>$value->video_id,
                        "channel_id"=>$value->channel_id,
                        "description"=> $value->description,
                        "user_id"=>$value->id,
                        "name"=> $value->name,
                        "email"=> $value->email,
                        "user_picture"=> $value->chat_picture,
                        'payment_status' => $value->payment_status ? $value->payment_status : 0,
                        "amount"=> $value->amount,
                        "publish_time"=> $value->date,
                        'currency'=> Setting::get('currency'),
                        "share_link"=>route('user.live_video.start_broadcasting', array('id'=>$value->unique_id,'c_id'=>$value->channel_id)),
                        'video_stopped_status'=>$value->video_stopped_status,
                        'video_payment_status'=> $videopayment ? DEFAULT_TRUE : DEFAULT_FALSE,
                        'redirect_web_url'=>route('user.android.video',['u_id'=>$value->unique_id,
                            'id'=>$request->id, 'c_id'=>$value->channel_id]),
                    ];

                    $values[] = $null_safe_value;
                }

                $response_array = ['success'=>true, 'data'=>$values];

        }

        return response()->json($response_array, 200);


    }   

    public function save_live_video(Request $request) {

        $validator = Validator::make($request->all(),array(
                'title' => 'required',
                'amount' => 'required|numeric',
                'payment_status'=>'required|numeric',
                'channel_id'=>'required|exists:channels,id',
               // 'video_url'=>'required',
            )
        );
        
        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());

            $response_array = ['success' => false , 'error_messages' => $errors, 'error' => $errors , 'error_code' => 001];
        } else {

            $user = User::find($request->id);

            if ($user) {

                if ($user->user_type) {


                    $model = LiveVideo::where('user_id', $request->id)->where('status', DEFAULT_FALSE)->first();


                    if(!$model) {

                        $model = new LiveVideo;
                        $model->title = $request->title;
                        $model->channel_id = $request->channel_id;
                        $model->payment_status = $request->payment_status;
                        $model->type = TYPE_PUBLIC;
                        $model->amount = ($request->payment_status) ? (($request->has('amount')) ? $request->amount : 0 ): 0;

                        $model->description = ($request->has('description')) ? $request->description : null;
                        $model->is_streaming = DEFAULT_TRUE;
                        $model->status = DEFAULT_FALSE;
                        $model->user_id = $request->id;
                        $model->virtual_id = md5(time());
                        $model->unique_id = $model->title;
                        $model->browser_name = $request->browser ? $request->browser : '';
                        $model->snapshot = asset("/images/live_stream.jpg");
                        $model->start_time = getUserTime(date('H:i:s'), ($user) ? $user->timezone : '', "H:i:s");

                        // $model->video_url = 'rtsp://104.236.1.170:1935/live/'.$user->id.'_'.$model->id;
                        // $model->video_url = $request->video_url;

                        $model->save();

                        if ($model) {

                            $destination_ip = Setting::get('wowza_ip_address');

                            if ($request->device_type == DEVICE_WEB || $request->device_type == DEVICE_ANDROID) {

                                if (Setting::get('kurento_socket_url') && $destination_ip) {

                                    $streamer_file = $user->id.'-'.$model->id.'.sdp';  

                                } else {

                                    $streamer_file = "";
                                }

                            } else {

                                $streamer_file = $user->id.'_'.$model->id;  

                            }

                            Log::info("device type ".$request->device_type == DEVICE_IOS);

                            if ($request->device_type == DEVICE_WEB) {

                                // $model->video_url = $streamer_file ? 'http://'.Setting::get('cross_platform_url').'/'.Setting::get('wowza_app_name').'/'.$streamer_file.'/playlist.m3u8';

                            } else if($request->device_type == DEVICE_IOS){

                                // $model->video_url = 'http://'.Setting::get('cross_platform_url').'/'.Setting::get('wowza_app_name').'/'.$streamer_file.'/playlist.m3u8';

                                $model->browser_name = $request->device_type;

                            }

                            $model->video_url = $streamer_file;


                           // $model->video_url = Setting::get('mobile_rtsp').$user->id.'_'.$model->id;

                            $model->save();

                            $response_array = [
                                'success' => true , 
                                "video_image"=> $model->snapshot,
                                "channel_image"=> $model->channel ? $model->channel->picture : '',
                                "title"=> $model->title,
                                "channel_name"=> $model->channel ? $model->channel->name : '',
                                "watch_count"=> $model->viewer_cnt ? $model->viewer_cnt : 0,
                               //  "video"=>$model->video_url,
                                "video_tape_id"=>$model->id,
                                "channel_id"=>$model->channel_id,
                                'unique_id'=>$model->unique_id,
                                "description"=> $model->description,
                                "user_id"=>$model->user ? $model->user->id : '',
                                "name"=> $model->user->name,
                                "email"=> $model->user->email,
                                "user_picture"=> $model->user->chat_picture,
                                'payment_status' => $model->payment_status ? $model->payment_status : 0,
                                "amount"=> $model->amount,
                                'currency'=> Setting::get('currency'),
                                "share_link"=>route('user.live_video.start_broadcasting', array('id'=>$model->unique_id,'c_id'=>$model->channel_id)),
                                'is_streaming'=>$model->is_streaming,
                                'redirect_web_url'=>route('user.android.video',['u_id'=>$model->unique_id, 'id'=>$request->id, 'c_id'=>$model->channel_id]),
                                'hostAddress'=>Setting::get('wowza_ip_address'),
                                'portNumber'=>Setting::get('wowza_port_number'),
                                'applicationName'=>Setting::get('wowza_app_name'),
                                'streamName'=>$streamer_file,
                                'wowzaUsername'=>Setting::get('wowza_username'),
                                'wowzaPassword'=>Setting::get('wowza_password'),
                                'wowzaLicenseKey'=>Setting::get('wowza_license_key'),
                                'video_url'=>$model->video_url,
                            ];
                        } else {
                            $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(003) , 'error_code' => 003];
                        }

                    } else {

                        $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(170), 'error_code'=>170];
                    }

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(167), 'error_code'=>167];

                }
            } else {

                $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(166), 'error_code'=>166];
            }
        }
        return response()->json($response_array,200);

    } 


    public function live_video(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array(
                'browser'=>'required',
                'device_type'=>'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS.','.DEVICE_WEB,
                'video_tape_id'=>'required|exists:live_videos,id',
            ));

        if ($validator->fails()) {

            // Error messages added in response for debugging

            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $model = LiveVideo::where('id',$request->video_tape_id)->first();

            if ($model) {

                if ($model->is_streaming) {

                    if(!$model->status) {

                        $user = User::find($model->user_id);

                        if ($user) {

                            // Load Based on id
                            $chat = ChatMessage::where('live_video_id', $model->id)->get();

                            $messages = [];

                            if(count($chat) > 0) {

                                foreach ($chat as $key => $value) {
                                    
                                    $messages[] = Helper::null_safe([
                                        // 'id' => $value->id, 
                                        'user_id' => ($value->getUser)? $value->user_id : $value->live_video_viewer_id, 
                                        'username' => ($value->getUser) ? $value->getUser->name : (($value->getViewUser) ? $value->getViewUser->name : ""),

                                        'picture'=> ($value->getUser) ? $value->getUser->chat_picture : (($value->getViewUser) ? $value->getViewUser->chat_picture : ""),
                                       // 'live_video_id'=>$value->live_video_id, 
                                        'comment'=>$value->message, 
                                        'diff_human_time'=>$value->created_at->diffForHumans()]);

                                }
                                
                            }

                            $videopayment = LiveVideoPayment::where('live_video_id', $model->id)
                                ->where('live_video_viewer_id', $request->id)
                                ->where('status',DEFAULT_TRUE)->first();

                            $suggestions = [];

                            $is_streamer = $model->user_id == $request->id ? DEFAULT_TRUE : DEFAULT_FALSE;

                            if (!$is_streamer) {

                                $video_url = "";

                                if ($model->unique_id == 'sample') {

                                    $video_url = $model->video_url;

                                } else {

                                    if ($model->video_url) {

                                        if ($request->device_type == DEVICE_IOS) {

                                            $video_url = CommonRepo::iosUrl($model);

                                        } else if($model->browser_name == DEVICE_IOS){

                                           $video_url = CommonRepo::rtmpUrl($model);

                                        }

                                        if (($request->browser == IOS_BROWSER || $request->browser == WEB_SAFARI) && ($model->browser_name == DEVICE_IOS)) {

                                            $video_url = CommonRepo::iosUrl($model);

                                        }

                                    } else {

                                        $video_url = "";

                                    }

                                }

                            } else {

                                $video_url = "";
                            }


                            $data = [
                                "video_image"=> $model->snapshot,
                                "channel_image"=> $model->channel?$model->channel->picture: '',
                                "title"=> $model->title,
                                "channel_name"=> $model->channel ? $model->channel->name : '',
                                "watch_count"=> $model->viewer_cnt ? $model->viewer_cnt : 0,
                                // "video"=> $model->video_url ? VideoRepo::rtmpUrl($model) : VideoRepo::getUrl($model, $request),
                                'unique_id'=>$model->unique_id,
                                "video_tape_id"=>$model->id,
                                "channel_id"=>$model->channel_id,
                                "description"=> $model->description,
                                "user_id"=>$model->user ? $model->user->id : '',
                                "name"=> $model->user ? $model->user->name : '',
                                "email"=> $model->user ? $model->user->email : '',
                                "user_picture"=> $model->user ? $model->user->chat_picture : '',
                                'payment_status' => $model->payment_status ? $model->payment_status : 0,
                                "amount"=> $model->amount,
                                "publish_time"=> $model->date,
                                'currency'=> Setting::get('currency'),
                                "share_link"=>route('user.live_video.start_broadcasting', array('id'=>$model->unique_id,'c_id'=>$model->channel_id)),
                                'video_stopped_status'=>$model->video_stopped_status,
                                'video_payment_status'=> $videopayment ? DEFAULT_TRUE : DEFAULT_FALSE,
                                'comments'=>$messages,  
                                'suggestions'=>$suggestions,
                                'redirect_web_url'=>route('user.android.video',['u_id'=>$model->unique_id, 'id'=>$request->id, 'c_id'=>$model->channel_id]),
                                'is_streamer'=>$is_streamer,
                                'video_url'=>$video_url,
                            ];

                            $response_array = ['success'=>true, 'data'=>$data];

                       }  else {

                            $response_array = ['success'=>false, 'error'=>Helper::get_error_message(166), 'error_code'=>150];

                       }

                    } else {

                        $response_array = ['success'=>false, 'error'=>Helper::get_error_message(163), 'error_code'=>163];

                    }

                } else {

                    $response_array = ['success'=>false, 'error'=>Helper::get_error_message(164), 'error_code'=>164];

                }

            } else {

                $response_array = ['success'=>false, 'error'=>Helper::get_error_message(165), 'error_code'=>165];

            }
        }

        return response()->json($response_array, 200);

    }


    public function save_chat(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id'=>'required|exists:live_videos,id',
                'viewer_id'=>'required|exists:users,id',
                'message'=>'required',
                'type'=>'required|in:uv,vu',
                'delivered'=>'required',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $model = new ChatMessage;

            $model->live_video_id = $request->video_tape_id;

            $model->user_id = $request->id;

            $model->live_video_viewer_id = $request->viewer_id;

            $model->message = $request->message;

            $model->type = $request->type;

            $model->delivered = $request->delivered;

            $model->save();

            Log::info("saving Data");

            Log::info(print_r("Data".$model, true));

            $response_array = ['success'=>true, 'data'=>$model];
        }

        return response()->json($response_array, 200);
    }


    public function video_subscription(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id'=>'required|exists:live_videos,id',
                'coupon_code'=>'exists:coupons,coupon_code',
                'payment_id'=>'required',

            ), array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'video_tape_id.exists' => tr('livevideo_not_exists'),
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $live_video = LiveVideo::find($request->video_tape_id);

            $viewerModel = User::find($request->id);

            if ($live_video->status) {

               $response_array = ['success'=>false, 'error_messages'=>tr('stream_stopped')]; 

            } else {

                $total = $live_video->amount;

                $coupon_amount = 0;

                $coupon_reason = '';

                $is_coupon_applied = COUPON_NOT_APPLIED;

                if ($request->coupon_code) {

                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                    if ($coupon) {
                        
                        if ($coupon->status == COUPON_INACTIVE) {

                            $coupon_reason = tr('coupon_inactive_reason');

                        } else {

                            $check_coupon = $this->check_coupon_applicable_to_user($viewerModel, $coupon)->getData();

                            if ($check_coupon->success) {

                                $is_coupon_applied = COUPON_APPLIED;

                                $amount_convertion = $coupon->amount;

                                if ($coupon->amount_type == PERCENTAGE) {

                                    $amount_convertion = round(amount_convertion($coupon->amount, $live_video->amount), 2);

                                }

                                if ($amount_convertion < $live_video->amount) {

                                    $total = $live_video->amount - $amount_convertion;

                                    $coupon_amount = $amount_convertion;

                                } else {

                                    // throw new Exception(Helper::get_error_message(156),156);

                                    $total = 0;

                                    $coupon_amount = $amount_convertion;
                                    
                                }

                                // Create user applied coupon

                                if($check_coupon->code == 2002) {

                                    $user_coupon = UserCoupon::where('user_id', $viewerModel->id)
                                            ->where('coupon_code', $request->coupon_code)
                                            ->first();

                                    // If user coupon not exists, create a new row

                                    if ($user_coupon) {

                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                            $user_coupon->no_of_times_used += 1;

                                            $user_coupon->save();

                                        }

                                    }

                                } else {

                                    $user_coupon = new UserCoupon;

                                    $user_coupon->user_id = $viewerModel->id;

                                    $user_coupon->coupon_code = $request->coupon_code;

                                    $user_coupon->no_of_times_used = 1;

                                    $user_coupon->save();

                                }

                            } else {

                                $coupon_reason = $check_coupon->error_messages;
                                
                            }

                        }

                    } else {

                        $coupon_reason = tr('coupon_delete_reason');
                    }
                
                }

                $user_payment = new LiveVideoPayment;

                $check_live_video_payment = LiveVideoPayment::where('live_video_viewer_id' , $request->id)->where('live_video_id' , $request->video_id)->first();

                if($check_live_video_payment) {
                    $user_payment = $check_live_video_payment;
                }

                $user_payment->payment_id  = $request->payment_id;
                $user_payment->live_video_viewer_id = $request->id;
                $user_payment->live_video_id = $request->video_tape_id;
                $user_payment->user_id = $live_video->user_id;
                $user_payment->status = DEFAULT_TRUE;
                $user_payment->payment_mode = PAYPAL;
                $user_payment->currency = Setting::get('currency');

                 // Coupon details

                $user_payment->is_coupon_applied = $is_coupon_applied;

                $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                $user_payment->coupon_amount = $coupon_amount;

                $user_payment->live_video_amount = $live_video->amount;

                $user_payment->amount = $total;

                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                if($user_payment->save()) {

                    // Commission Spilit 

                    $admin_commission = Setting::get('admin_commission')/100;

                    $admin_amount = $total * $admin_commission;

                    $user_amount = $total - $admin_amount;

                    $user_payment->admin_amount = $admin_amount;

                    $user_payment->user_amount = $user_amount;

                    $user_payment->save();

                    // Commission Spilit Completed

                    if($user = User::find($user_payment->user_id)) {

                        $user->total_admin_amount = $user->total_admin_amount + $admin_amount;

                        $user->total_user_amount = $user->total_user_amount + $user_amount;

                        $user->remaining_amount = $user->remaining_amount + $user_amount;

                        $user->total_amount = $user->total + $total;

                        $user->save();

                        add_to_redeem($user->id , $user_amount);
                    
                    }

                }

                $response_array = ['success'=>true, 'message'=>tr('payment_success'), 
                            'data'=>['id'=>$request->id,
                                     'token'=>$viewerModel ? $viewerModel->token : '']];
           
            }

        }

        return response()->json($response_array, 200);

    }

    public function get_viewers(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id'=>'required|exists:live_videos,id',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $viewer_cnt = 0;

            $live_video = LiveVideo::find($request->video_tape_id);

            Log::info("Live Video");

            Log::info(print_r($request->all(), true));

            if ($live_video) {

                if ($live_video->user_id != $request->id) {

                    // Load Viewers model

                    $model = Viewer::where('video_id', $request->video_tape_id)->where('user_id', $request->id)->first();

                    $new_user = 0;

                    if(!$model) {

                        $new_user = 1;

                        $model = new Viewer;

                        $model->video_id = $request->video_tape_id;

                        $model->user_id = $request->id;

                    }

                    $model->count = ($model->count) ? $model->count + 1 : 1;

                    $model->save();

                    Log::info("new_user ".$new_user);

                    if ($new_user) {

                        if ($live_video) {

                            Log::info("test");

                            $live_video->viewer_cnt += 1;

                            $live_video->save();
                            
                        }

                    }

                }

                $viewer_cnt = $live_video->viewer_cnt ? $live_video->viewer_cnt : 0;

            }


            Log::info("viewer_cnt ".$viewer_cnt);
            
            $response_array  = ['success'=>true, 
                'viewer_cnt'=> (int) $viewer_cnt];

        }

        return response()->json($response_array);
    }

    public function peerProfile(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'peer_id'=>'required|exists:users,id',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $user = User::find($request->peer_id);


            $response_array = Helper::null_safe(array(
                'success' => true,
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'gender' => $user->gender,
                'email' => $user->email,
                'picture' => $user->picture,
                'chat_picture' => $user->chat_picture,
                'description'=>$user->description,
                'token' => $user->token,
                'token_expiry' => $user->token_expiry,
                'login_by' => $user->login_by,
                'social_unique_id' => $user->social_unique_id,
            ));

            $response_array = response()->json(Helper::null_safe($response_array), 200);

        }

    
        return $response_array;

    }

    public function close_streaming(Request $request) {

        $validator = Validator::make(
            $request->all(), array(
                'video_tape_id'=>'required|exists:live_videos,id',
        ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            // Load Model
            $model = LiveVideo::find($request->video_tape_id);

            $model->status = DEFAULT_TRUE;

            $model->end_time = getUserTime(date('H:i:s'), ($model->user) ? $model->user->timezone : '', "H:i:s");

            $model->no_of_minutes = getMinutesBetweenTime($model->start_time, $model->end_time);

            if ($model->save()) {

                if ($request->device_type == DEVICE_WEB) {

                    if ($model->user_id == $request->id) {  

                        if (Setting::get('wowza_server_url')) {

                            $this->disConnectStream($model->user_id.'-'.$model->id);

                        }

                    }

                }

                $response_array = ['success'=>true, 'message'=>tr('streaming_stopped')];
            }
        }

        return response()->json($response_array,200);
    }


    public function checkVideoStreaming(Request $request) {

        $validator = Validator::make(
            $request->all(), array(
                'video_tape_id'=>'required|exists:live_videos,id',
        ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $video = LiveVideo::find($request->video_tape_id);


            $user = User::find($request->id);

            $status = false;

            if ($user) {

                if ($user->token == $request->token) {

                    $status = false;

                    $token = $user->token;

                } else {

                    $status = true;

                    $token = $user->token;

                }
            }

            if ($video) {

                if($video->is_streaming) {

                    if (!$video->status) {

                        $response_array = ['success'=> true, 
                            'message'=>tr('video_streaming'), 
                            'viewer_cnt'=>$video->viewer_cnt ? $video->viewer_cnt : 0,
                            'data'=> ['status'=>$status, 'token'=>$token]];


                    } else {

                        $response_array = ['success'=> false, 'message'=>tr('streaming_stopped'), 'error_code'=>550];


                    }

                } else {

                    $response_array = ['success'=> false, 'message'=>tr('no_streaming_video_present')];

                }

            } else {

                $response_array = ['success'=> false, 'message'=>tr('no_live_video_present')];

            }
           

            return response()->json($response_array,200);

        }
    }


   /* public function stripe_payment_video(Request $request) {

        $userModel = User::find($request->id);

        if ($userModel->card_id) {

            $user_card = Card::find($userModel->card_id);

            if ($user_card && $user_card->is_default) {

                $video = LiveVideo::find($request->video_tape_id);

                if($video && !$video->status && $video->is_streaming) {

                    $total = $video->amount;

                    // Get the key from settings table
                    $stripe_secret_key = Setting::get('stripe_secret_key');

                    $customer_id = $user_card->customer_id;
                    
                    if($stripe_secret_key) {

                        \Stripe\Stripe::setApiKey($stripe_secret_key);
                    } else {

                        $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                        return response()->json($response_array , 200);

                        // return back()->with('flash_error', Helper::get_error_message(902));
                    }

                    try {

                       $user_charge =  \Stripe\Charge::create(array(
                          "amount" => $total * 100,
                          "currency" => "usd",
                          "customer" => $customer_id,
                        ));

                       $payment_id = $user_charge->id;
                       $amount = $user_charge->amount/100;
                       $paid_status = $user_charge->paid;

                       if($paid_status) {

                            $user_payment = new LiveVideoPayment;
                            $user_payment->payment_id  = $payment_id;
                            $user_payment->live_video_viewer_id = $request->id;
                            $user_payment->user_id = $video->user_id;
                            $user_payment->live_video_id = $video->id;
                            $user_payment->status = 1;
                            $user_payment->amount = $amount;

                            // Commission Spilit 

                            $admin_commission = Setting::get('admin_commission')/100;

                            $admin_amount = $amount * $admin_commission;

                            $user_amount = $amount - $admin_amount;

                            $user_payment->admin_amount = $admin_amount;

                            $user_payment->user_amount = $user_amount;

                            $user_payment->save();

                            // Commission Spilit Completed

                            if($user = User::find($user_payment->user_id)) {

                                $user->total_admin_amount = $user->total_admin_amount + $admin_amount;

                                $user->total_user_amount = $user->total_user_amount + $user_amount;

                                $user->remaining_amount = $user->remaining_amount + $user_amount;

                                $user->total_amount = $user->total_amount + $total;

                                $user->save();

                                add_to_redeem($user->id, $user_amount, $admin_amount);
                            
                            }

                            $data = ['id'=> $request->id, 'token'=> $user->token , 'payment_id' => $payment_id];

                            $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                        } else {

                            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                        }
                    
                    } catch (\Stripe\StripeInvalidRequestError $e) {

                        Log::info(print_r($e,true));

                        $response_array = array('success' => false , 'error_messages' => Helper::get_error_message(903) ,'error_code' => 903);


                       return response()->json($response_array , 200);
                    
                    }

                
                } else {

                    $response_array = array('success' => false , 'error_messages' => tr('no_live_video_found'));
                    
                }


            } else {

                // return back()->with('flash_error', tr('no_default_card_available'));

                $response_array = array('success' => false , 'error_messages' => tr('no_default_card_available'));

            }

        } else {

            // return back()->with('flash_error', tr('no_default_card_available'));

            $response_array = array('success' => false , 'error_messages' => tr('no_default_card_available'));

        }


        return response()->json($response_array,200);
        

    } */




    /**
     * Function Name : stripe_live_ppv()
     * 
     * Pay the payment for Pay per view through stripe
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function stripe_live_ppv(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), 
                array(
                    'video_id' => 'required|exists:live_videos,id,status,'.VIDEO_STREAMING_ONGOING,
                    'coupon_code'=>'exists:coupons,coupon_code,status,'.COUPON_ACTIVE,
                  //  'total_amount'=>'numeric',
                ), array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'video_id.exists' => tr('livevideo_not_exists'),
            ));

            if($validator->fails()) {

                $errors = implode(',', $validator->messages()->all());
                
                $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

                throw new Exception($errors);

            } else {

                $userModel = User::find($request->id);

                if ($userModel) {

                    if ($userModel->card_id) {

                        $user_card = Card::find($userModel->card_id);

                        if ($user_card && $user_card->is_default) {

                            $video = LiveVideo::find($request->video_id);

                            if($video) {

                                $total = $video->amount;

                                $coupon_amount = 0;

                                $coupon_reason = '';

                                $is_coupon_applied = COUPON_NOT_APPLIED;

                                if ($request->coupon_code) {

                                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                                    if ($coupon) {
                                        
                                        if ($coupon->status == COUPON_INACTIVE) {

                                            $coupon_reason = tr('coupon_inactive_reason');

                                        } else {

                                            $check_coupon = $this->check_coupon_applicable_to_user($userModel, $coupon)->getData();

                                            if ($check_coupon->success) {

                                                $is_coupon_applied = COUPON_APPLIED;

                                                $amount_convertion = $coupon->amount;

                                                if ($coupon->amount_type == PERCENTAGE) {

                                                    $amount_convertion = round(amount_convertion($coupon->amount, $video->amount), 2);

                                                }


                                                if ($amount_convertion < $video->amount) {

                                                    $total = $video->amount - $amount_convertion;

                                                    $coupon_amount = $amount_convertion;

                                                } else {

                                                    // throw new Exception(Helper::get_error_message(156),156);

                                                    $total = 0;

                                                    $coupon_amount = $amount_convertion;
                                                    
                                                }

                                                // Create user applied coupon

                                                if($check_coupon->code == 2002) {

                                                    $user_coupon = UserCoupon::where('user_id', $userModel->id)
                                                            ->where('coupon_code', $request->coupon_code)
                                                            ->first();

                                                    // If user coupon not exists, create a new row

                                                    if ($user_coupon) {

                                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                            $user_coupon->no_of_times_used += 1;

                                                            $user_coupon->save();

                                                        }

                                                    }

                                                } else {

                                                    $user_coupon = new UserCoupon;

                                                    $user_coupon->user_id = $userModel->id;

                                                    $user_coupon->coupon_code = $request->coupon_code;

                                                    $user_coupon->no_of_times_used = 1;

                                                    $user_coupon->save();

                                                }

                                            } else {

                                                $coupon_reason = $check_coupon->error_messages;
                                                
                                            }
                                        }

                                    } else {

                                        $coupon_reason = tr('coupon_delete_reason');
                                    }
                                
                                }

                                if ($total <= 0) {

                                    $user_payment = new LiveVideoPayment;
                                    $user_payment->payment_id = $is_coupon_applied ? 'COUPON-DISCOUNT' : FREE_PLAN;
                                    $user_payment->user_id = $video->user_id;
                                    $user_payment->live_video_viewer_id = $request->id;
                                    $user_payment->live_video_id = $request->video_id;
                                    $user_payment->status = PAID_STATUS;
                                  
                                    $user_payment->admin_amount = 0;

                                    $user_payment->user_amount = 0;

                                    $user_payment->payment_mode = CARD;

                                    $user_payment->currency = Setting::get('currency');

                                    // Coupon details

                                    $user_payment->is_coupon_applied = $is_coupon_applied;

                                    $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                    $user_payment->coupon_amount = $coupon_amount;

                                    $user_payment->live_video_amount = $video->amount;

                                    $user_payment->amount = $total;

                                    $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                                    $user_payment->save();


                                    $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $user_payment->payment_id];

                                    $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                } else {

                                    // Get the key from settings table

                                    $stripe_secret_key = Setting::get('stripe_secret_key');

                                    $customer_id = $user_card->customer_id;
                                    
                                    if($stripe_secret_key) {

                                        \Stripe\Stripe::setApiKey($stripe_secret_key);

                                    } else {

                                        $response_array = array('success' => false, 'error_messages' => Helper::error_message(902) , 'error_code' => 902);

                                        throw new Exception(Helper::error_message(902));
                                        
                                    }

                                    try {

                                       $user_charge =  \Stripe\Charge::create(array(
                                          "amount" => $total * 100,
                                          "currency" => "usd",
                                          "customer" => $customer_id,
                                        ));

                                       $payment_id = $user_charge->id;
                                       $amount = $user_charge->amount/100;
                                       $paid_status = $user_charge->paid;
                                       
                                       if($paid_status) {

                                            $user_payment = new LiveVideoPayment;
                                            $user_payment->payment_id  = $payment_id;
                                            $user_payment->user_id = $video->user_id;
                                            $user_payment->live_video_viewer_id = $request->id;
                                            $user_payment->live_video_id = $request->video_id;
                                            $user_payment->status = PAID_STATUS;
                                            $user_payment->payment_mode = CARD;

                                            $user_payment->currency = Setting::get('currency');

                                             // Coupon details

                                            $user_payment->is_coupon_applied = $is_coupon_applied;

                                            $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                            $user_payment->coupon_amount = $coupon_amount;

                                            $user_payment->live_video_amount = $video->amount;

                                            $user_payment->amount = $total;

                                            $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;


                                            // Commission Spilit 

                                            $admin_commission = Setting::get('admin_commission')/100;

                                            $admin_amount = $total * $admin_commission;

                                            $user_amount = $total - $admin_amount;

                                            $user_payment->admin_amount = $admin_amount;

                                            $user_payment->user_amount = $user_amount;

                                            $user_payment->save();

                                            // Commission Spilit Completed

                                            if($user = User::find($user_payment->user_id)) {

                                                $user->total_admin_amount = $user->total_admin_amount + $admin_amount;

                                                $user->total_user_amount = $user->total_user_amount + $user_amount;

                                                $user->remaining_amount = $user->remaining_amount + $user_amount;

                                                $user->total_amount = $user->total + $total;

                                                $user->save();

                                                add_to_redeem($user->id , $user_amount);
                                            
                                            }

                                            $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $payment_id];

                                            $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                        } else {

                                            $response_array = array('success' => false, 'error_messages' => Helper::error_message(902) , 'error_code' => 902);

                                            throw new Exception(tr('no_vod_video_found'));

                                        }
                                    
                                    } catch(\Stripe\Error\RateLimit $e) {

                                        throw new Exception($e->getMessage(), 903);

                                    } catch(\Stripe\Error\Card $e) {

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\InvalidRequest $e) {
                                        // Invalid parameters were supplied to Stripe's API
                                       
                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\Authentication $e) {

                                        // Authentication with Stripe's API failed

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\ApiConnection $e) {

                                        // Network communication with Stripe failed

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\Base $e) {
                                      // Display a very generic error to the user, and maybe send
                                        
                                        throw new Exception($e->getMessage(), 903);

                                    } catch (Exception $e) {
                                        // Something else happened, completely unrelated to Stripe

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\StripeInvalidRequestError $e) {

                                            Log::info(print_r($e,true));

                                        throw new Exception($e->getMessage(), 903);
                                        
                                    
                                    }

                                }

                            
                            } else {

                                $response_array = array('success' => false , 'error_messages' => tr('no_vod_video_found'));

                                throw new Exception(tr('no_vod_video_found'));
                                
                            }

                        } else {

                            throw new Exception(tr('no_default_card_available'), 901);

                        }

                    } else {

                        throw new Exception(tr('no_default_card_available'), 901);

                    }

                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    

                }

            }

            DB::commit();

            return response()->json($response_array,200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$code];

            return response()->json($response_array);

        }
        
    }

    public function get_live_url(Request $request) {

        $id = $request->video_id;

        $device_type = $request->device_type;

        $browser = $request->browser;

        \Log::info("Live Video Id ".$id);

        $video = LiveVideo::where('id', $id)->first(); 

        if ($video) {

            if($video->is_streaming) {

                if (!$video->status) {


                    if ($video->video_url) {

                        $sdp = $video->user_id.'_'.$video->id;

                        $browser = $browser ? strtolower($browser) : get_browser();

                        if (strpos($browser, 'safari') !== false) {
                            
                            $url = "http://".Setting::get('cross_platform_url')."/live/".$sdp."/playlist.m3u8";  

                        } else {

                            $url = "rtmp://".Setting::get('cross_platform_url')."/live/".$sdp;
                        }

                    } else {

                        $sdp = $video->user_id.'-'.$video->id.'.sdp';

                        if ($device_type == DEVICE_ANDROID) {

                            $url = "rtsp://".Setting::get('cross_platform_url')."/live/".$sdp;

                        } else if($device_type == DEVICE_IOS) {

                            $url = "http://".Setting::get('cross_platform_url')."/live/".$sdp."/playlist.m3u8";

                        } else {

                            $browser = $browser ? strtolower($browser) : get_browser();

                            if (strpos($browser, 'safari') !== false) {
                                
                                $url = "http://".Setting::get('cross_platform_url')."/live/".$sdp."/playlist.m3u8";  

                            } else {

                                $url = "rtmp://".Setting::get('cross_platform_url')."/live/".$sdp;
                            }

                        }
                    }

                    $response_array = ['success'=> true, 'url'=>$url];

                } else {

                    $response_array = ['success'=> false, 'message'=>tr('stream_stopped')];

                }

            } else {

                $response_array = ['success'=> false, 'message'=>tr('no_streaming_video_present')];

            }

        } else {

            $response_array = ['success'=> false, 'message'=>tr('no_live_video_present')];

        }

        return response()->json($response_array);
 
    }


    public function save_vod(Request $request) {


        $data = explode(',', $request->video_blob);

        if ($data[1] != '') {

            $fileName = $request->id.'_'.$request->video_id.'.webm';

            file_put_contents(join(DIRECTORY_SEPARATOR, [public_path(), 'uploads', 'vod',$fileName]), base64_decode($data[1]));

            $live = LiveVideo::find($request->video_id);

            if ($live) {

                $model = new VideoTape;

                $model->channel_id = $live->channel_id;

                $model->unique_id = $live->title;

                $model->title = $live->title;

                $model->description = $live->description;

                $model->default_image = $live->snapshot;

                $model->video = asset('uploads/vod/'.$fileName);

                $model->status = DEFAULT_TRUE;

                $model->compress_status = DEFAULT_TRUE;

                $model->video_type = VIDEO_TYPE_LIVE;

                $model->save();

                $response_array = ['success'=>true, 'model'=>$model];

                return response()->json($response_array);

            } else{

                $response_array = ['success'=>false, 'error_message'=>tr('no_live_video_found')];

                return response()->json($response_array);

            }
        
            
        }

        $response_array = ['success'=>false, 'error_message'=>tr('no_live_video_found')];

        return response()->json($response_array);

    }

    /**
     * Function Name : update_profile()
     * 
     * @usage_place : MOBILE & WEB
     * 
     * Save any changes to the users profile.
     * 
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request) {
        
        $validator = Validator::make(
            $request->all(),
            array(
                'name' => 'required|max:255',
                'email' => 'email|unique:users,email,'.$request->id.'|max:255',
                'mobile' => 'digits_between:6,13',
                'picture' => 'mimes:jpeg,bmp,png',
                'gender' => 'in:male,female,others',
                'device_token' => '',
                'dob'=>'required',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $error_messages = implode(',',$validator->messages()->all());
            $response_array = array(
                    'success' => false,
                    'error' => Helper::get_error_message(101),
                    'error_code' => 101,
                    'error_messages' => $error_messages
            );
        } else {

            $user = User::find($request->id);

            if($user) {
                
                $user->name = $request->name ? $request->name : $user->name;
                
                if($request->has('email')) {
                    $user->email = $request->email;
                }

                $user->mobile = $request->mobile ? $request->mobile : $user->mobile;
                $user->gender = $request->gender ? $request->gender : $user->gender;
                $user->address = $request->address ? $request->address : $user->address;
                $user->description = $request->description ? $request->description : $user->address;
                $user->wallet_address = $request->wallet_address ?: $user->wallet_address;
                $user->coin_payment_pay_name = $request->coin_payment_pay_name ?: $user->coin_payment_pay_name;

                if ($request->dob) {

                    $user->dob = date('Y-m-d', strtotime($request->dob));

                }

                if ($user->dob) {

                    $from = new \DateTime($user->dob);
                    $to   = new \DateTime('today');

                    $user->age_limit = $from->diff($to)->y;

                }

                if ($user->age_limit < 10) {

                    $response_array = ['success' => false , 'error_messages' => tr('min_age_error')];

                    return response()->json($response_array , 200);

                }


                // Upload picture

                if ($request->hasFile('picture') != "") {

                    Helper::delete_picture($user->picture, "/uploads/images/"); // Delete the old pic

                    $user->picture = Helper::normal_upload_picture($request->file('picture'), "/uploads/images/", $user);
                }

                $user->save();
            }

            $payment_mode_status = $user->payment_mode ? $user->payment_mode : "";

            if (!empty($user->dob) && $user->dob != "0000-00-00") {

                $user->dob = date('d-m-Y', strtotime($user->dob));

            } else {

                $user->dob = "";
            }

            $response_array = array(
                'success' => true,
                'message' => tr('profile_updated'),
                'id' => $user->id,
                'name' => $user->name,
                'description' => $user->description,
                'mobile' => $user->mobile,
                'gender' => $user->gender,
                'email' => $user->email,
                'dob'=> $user->dob,
                'age'=>$user->age_limit,
                'picture' => $user->picture,
                'chat_picture' => $user->picture,
                'token' => $user->token,
                'token_expiry' => $user->token_expiry,
                'login_by' => $user->login_by,
                'social_unique_id' => $user->social_unique_id,
                'push_status' => $user->push_status,
                
            );

            $response_array = Helper::null_safe($response_array);
        
        }

        return response()->json($response_array, 200);
    
    }

    /**
     * Function Name : change_password
     *
     * @usage_place : MOBILE & WEB
     *
     * To change the password who has logged in user
     *
     * @param Object $request - User PAssword Details
     *
     * @return response of success/failure message
     */
    public function change_password(Request $request) {

        $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed',
                'old_password' => 'required',
            ]);

        if($validator->fails()) {
            
            $error_messages = implode(',',$validator->messages()->all());
           
            $response_array = array('success' => false, 'error' => tr('invalid_input'), 'error_code' => 101, 'error_messages' => $error_messages );
       
        } else {

            $user = User::find($request->id);

            if(Hash::check($request->old_password,$user->password)) {

                $user->password = \Hash::make($request->password);
                
                $user->save();

                $response_array = Helper::null_safe(array('success' => true , 'message' => Helper::get_error_message(102)));

            } else {

                $response_array = array('success' => false , 'error' => '','error_messages' => Helper::get_error_message(131) ,'error_code' => 131);
            }

        }

        $response = response()->json($response_array,200);

        return $response;

    }

    /**
     * Function Name : add_history()
     *
     * @usage_place : MOBILE & WEB
     *
     * To Add in history based on user, once he complete the video , the video will save
     *
     * @param Integer $request - Video Id
     *
     * @return response of Boolean with message
     */
    public function add_history(Request $request)  {

        Log::info("Adding History...!");

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' => 'required|integer|exists:video_tapes,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                'unique' => 'The :attribute already added in history.'
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);

        } else {

            $payperview = PayPerView::where('user_id', $request->id)
                            ->where('video_id',$request->video_tape_id)
                            ->where('is_watched', '!=', WATCHED)
                            ->orderBy('ppv_date', 'desc')
                            ->where('status', PAID_STATUS)
                            ->first();

            if ($payperview) {

                $payperview->is_watched = WATCHED;

                $payperview->save();

            }

            if($history = UserHistory::where('user_histories.user_id' , $request->id)->where('video_tape_id' ,$request->video_tape_id)->first()) {

                // $response_array = array('success' => true , 'error_messages' => Helper::get_error_message(145) , 'error_code' => 145);

            } else {

                // Save Wishlist

                if($request->id) {

                    $rev_user = new UserHistory();
                    $rev_user->user_id = $request->id;
                    $rev_user->video_tape_id = $request->video_tape_id;
                    $rev_user->status = DEFAULT_TRUE;
                    $rev_user->save();

                }

           
            }

            $video = VideoTape::find($request->video_tape_id);

            $navigateback = 0;

            if ($request->id != $video->user_id) {

                if ($video->type_of_subscription == RECURRING_PAYMENT) {

                    $navigateback = 1;

                }
            }

            // navigateback = used to handle the replay in mobile for recurring payments

            $response_array = array('success' => true , 'navigateback' => $navigateback);
           

        }
        return response()->json($response_array, 200);
    
    }

    /**
     * Function Name : delete_history()
     *
     * @usage_place : MOBILE & WEB
     *
     * To Delete a history based on user
     *
     * @param Integer $request - Video Id
     *
     * @return response of Boolean with message
     */
    public function delete_history(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' =>$request->has('status') ?  'integer|exists:video_tapes,id' : 'required|integer|exists:video_tapes,id'
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists please add to history',
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);

        } else {

            if($request->has('status')) {

                $history = UserHistory::where('user_id',$request->id)->delete();

            } else {

                $history = UserHistory::where('user_id',$request->id)->where('video_tape_id' , $request->video_tape_id)->delete();

            }

            $response_array = array('success' => true);
        }

        return response()->json($response_array, 200);
    
    }

    /**
     * Function Name : add_wishlist()
     *
     * @usage_place : MOBILE & WEB
     *
     * To add a wishlist based on user id
     *
     * @param Integer $request - Video Id
     *
     * @return response of Boolean with message
     */
    public function add_wishlist(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' => 'required|integer|exists:video_tapes,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                'unique' => 'The :attribute already added in wishlist.'
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);
        } else {

            $wishlist = Wishlist::where('user_id' , $request->id)->where('video_tape_id' , $request->video_tape_id)->first();

            if(count($wishlist) > 0) {

                if ($request->wishlist_id) {

                    if ($wishlist->id == $request->wishlist_id) {

                        $wishlist->delete();

                    }

                }

            } else {

                //Save Wishlist
                $wishlist = new Wishlist();
                $wishlist->user_id = $request->id;
                $wishlist->video_tape_id = $request->video_tape_id;
                $wishlist->status = DEFAULT_TRUE;
                $wishlist->save();
            }
           
            $message = "Added to wishlist";

            $response_array = array('success' => true ,'wishlist_id' => $wishlist->id , 'wishlist_status' => $wishlist->status,'message' => $message);
        }

        return response()->json($response_array, 200);
    
    }

    /**
     * Function Name : delete_wishlist()
     *
     * @usage_place : MOBILE & WEB
     *
     * To delete wishlist based on user id
     * 
     * @param intger $request - Video tape id
     *
     * @return response of success/failure message
     */
    public function delete_wishlist(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' => 'required|integer|exists:video_tapes,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists please add to wishlists',
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);

        } else {

            /** Clear All wishlist of the loggedin user */

            if($request->status == 1) {

                $wishlist = Wishlist::where('user_id',$request->id)->delete();

            } else {  /** Clear particularv wishlist of the loggedin user */


                $wishlist = Wishlist::where('user_id',$request->id)->where('video_tape_id' , $request->video_tape_id)->delete();
   
                
                
            }

            $response_array = array('success' => true);
        }

        return response()->json($response_array, 200);
    
    }

    /**
     * Function Name : add_comment()
     *
     * @usage_place : MOBILE & WEB
     * 
     * To Add comment based on single video
     *
     * @param integer $video_tape_id - Video Tape ID
     *
     * @return response of success/failure message
     */
    public function user_rating(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' => 'required|integer|exists:video_tapes,id',
                'ratings' => 'integer|in:'.RATINGS,
                'comments' => '',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                'unique' => 'The :attribute already rated.'
            )
        );

        if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            //Save Rating
            $rating = new UserRating();
            $rating->user_id = $request->id;
            $rating->video_tape_id = $request->video_tape_id;
            $rating->rating = $request->has('ratings') ? $request->ratings : 0;
            $rating->comment = $request->comments ? $request->comments: '';
            if($rating->save()) {
                $video_tape_details = VideoTape::find($request->video_tape_id);
                // ADD POINTS TO THE OWNER USER start
                $point_types = DB::table('point_types')->where("name","Comment")->first();
 
            $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","video")->orderBy('id','desc')->first();
                if($video_tape_details) {
                    if($video_tape_details->user_id != $request->id) {

                        $inputRequest = [];

                        $inputRequest['user_id'] = $request->id;

                        $inputRequest['owner_user_id'] = $video_tape_details->user_id;

                        $inputRequest['point_type'] = POINT_TYPE_COMMENT_VIDEO;

                        $inputRequest['video_tape_id'] = $request->video_tape_id;

                        // $inputRequest['points'] = Setting::get('user_points') ?: 0;
                        $inputRequest['points'] = $data->point ?: 0;

                        Log::info("UserPointJob - COMMENT ".print_r($inputRequest, true));
                        // dispatch(new UserPointJob($inputRequest));
                         $this->Credit_Points($inputRequest);
                        $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $request->id; 
                           $this->Credit_Points($inputRequest);
                        // dispatch(new UserPointJob($inputRequest));
                    }
                }
            }

            $ratings = UserRating::select(
                    'rating', 'video_tape_id',DB::raw('sum(rating) as total_rating'))
                    ->where('video_tape_id', $request->video_tape_id)
                    ->groupBy('video_tape_id')
                    ->avg('rating');

            if ($rating->adminVideo) {

                $rating->adminVideo->user_ratings = $ratings;

                $rating->adminVideo->save();

            }

            $response_array = array('success' => true , 'comment' => $rating->toArray() , 'date' => $rating->created_at->diffForHumans(),'message' => tr('comment_success') );
        }
 
        $user =  User::find($request->id);
        $response_array['total_points'] = $user->total_points;
        $response = response()->json($response_array, 200);
        return $response;
    
    }

    /**
     * Function Name : delete_account()
     *
     * To delete account , based on the user
     *
     * @param object $request - User Details
     *
     * @return response of success/failure message
     */
    public function delete_account(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'password' => '',
            ));

        if ($validator->fails()) {
            $error_messages = implode(',',$validator->messages()->all());
            $response_array = array('success' => false,'error' => Helper::get_error_message(101),'error_code' => 101,'error_messages' => $error_messages
            );
        } else {

            $user = User::find($request->id);

            if($user->login_by != 'manual') {
                $allow = 1;
            } else {

                if(Hash::check($request->password, $user->password)) {
                    $allow = 1;
                } else {
                    $allow = 0 ;

                    $response_array = array('success' => false , 'error_messages' => Helper::get_error_message(108) ,'error_code' => 108);
                }

            }

            if($allow) {

                $user = User::where('id',$request->id)->first();

                if($user) {
                    $user->delete();
                    $response_array = array('success' => true , 'message' => tr('user_account_delete_success'));
                } else {
                    $response_array = array('success' =>false , 'error_messages' => Helper::get_error_message(146), 'error_code' => 146);
                }

            }

        }

        return response()->json($response_array,200);

    }

    /**
     * User manual and social register save 
     *
     *
     */
    public function register(Request $request) {

        $response_array = array();
        $operation = false;
        $new_user = DEFAULT_TRUE;

        // validate basic field

        $basicValidator = Validator::make(
            $request->all(),
            array(
                'device_type' => 'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS,
                'device_token' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
            )
        );

        if($basicValidator->fails()) {

            $errors = implode(',', $basicValidator->messages()->all());
            
            $response_array = ['success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $errors];

            Log::info('Registration basic validation failed');

        } else {

            $login_by = $request->login_by;
            $allowedSocialLogin = array('facebook','google');

            // check login-by

            if(in_array($login_by,$allowedSocialLogin)) {

                // validate social registration fields

                $socialValidator = Validator::make(
                            $request->all(),
                            array(
                                'social_unique_id' => 'required',
                                'name' => 'required|max:255',
                                'email' => 'required|email|max:255',
                                'mobile' => 'digits_between:6,13',
                                'picture' => '',
                                'gender' => 'in:male,female,others',
                            )
                        );

                if($socialValidator->fails()) {

                    $error_messages = implode(',', $socialValidator->messages()->all());
                    $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $error_messages);

                    Log::info('Registration social validation failed');

                } else {

                    $check_social_user = User::where('email' , $request->email)->first();

                    if($check_social_user) {
                        $new_user = DEFAULT_FALSE;
                    }

                    Log::info('Registration passed social validation');
                    $operation = true;
               
                }

            } else {

                // Validate manual registration fields

                $manualValidator = Validator::make(
                    $request->all(),
                    array(
                        'name' => 'required|max:255',
                        'email' => 'required|email|max:255',
                        'mobile' => 'digits_between:6,13',
                        'password' => 'required|min:6',
                        'picture' => 'mimes:jpeg,jpg,bmp,png',
                    )
                );

                // validate email existence

                $emailValidator = Validator::make(
                    $request->all(),
                    array(
                        'email' => 'unique:users,email',
                    )
                );

                if($manualValidator->fails()) {

                    $errors = implode(',', $manualValidator->messages()->all());
                    
                    $response_array = ['success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $errors];

                    Log::info('Registration manual validation failed');

                } elseif($emailValidator->fails()) {

                    $errors = implode(',', $emailValidator->messages()->all());

                    $response_array = ['success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $errors];

                    Log::info('Registration manual email validation failed');

                } else {
                    Log::info('Registration passed manual validation');
                    $operation = true;
                }

            }

            if($operation) {

                // Creating the user
                if($new_user) {
                    $user = new User;
                    register_mobile($request->device_type);
                } else {
                    $user = $check_social_user;
                }

                if($request->has('name')) {
                    $user->name = $request->name;
                }

                if($request->has('email')) {
                    $user->email = $request->email;
                }

                if($request->has('dob')) {
                    $user->dob = date("Y-m-d" , strtotime($request->dob));
                }

                 if ($user->dob) {

                    if ($user->dob != '0000-00-00') {

                        $from = new \DateTime($user->dob);
                        $to   = new \DateTime('today');

                        $user->age_limit = $from->diff($to)->y;

                    }

                }

                if($request->has('mobile')) {
                    $user->mobile = $request->mobile;
                }

                if($request->has('password'))
                    $user->password = Hash::make($request->password);

                $user->gender = $request->has('gender') ? $request->gender : "male";

                $user->token_expiry = Helper::generate_token_expiry();

                $check_device_exist = User::where('device_token', $request->device_token)->first();

                if($check_device_exist){
                    $check_device_exist->device_token = "";
                    $check_device_exist->save();
                }

                Log::info("Device Token - ".$request->device_token);
                $user->device_token = $request->device_token;
                $user->device_type = $request->has('device_type') ? $request->device_type : "";
                $user->login_by = $request->has('login_by') ? $request->login_by : "";
                $user->social_unique_id = $request->has('social_unique_id') ? $request->social_unique_id : '';

                $user->picture = asset('placeholder.png');

                $user->chat_picture = $user->picture;
                  $user->is_verified = 1;
                // Upload picture
                if($request->login_by == "manual") {

                    if($request->hasFile('picture')) {
                        $user->picture = Helper::normal_upload_picture($request->file('picture'), "/uploads/images/", $user);
                    }
                } else {
                    if($request->has('picture')) {
                        $user->picture = $request->picture;
                    }

                    $user->is_verified = 1;
                }

                $user->chat_picture = $user->chat_picture ? $user->chat_picture : $user->picture;
        
                // $user->is_activated = 1;

                $user->save();

               // $user->token = AppJwt::create(['id' => $user->id, 'email' => $user->email, 'role' => "model"]);



               // $user->save();


                // Send welcome email to the new user:
                if($new_user) {
                    // Check the default subscription and save the user type 

                    user_type_check($user->id);

                    $subject = tr('user_welcome_title' , Setting::get('site_name'));
                    $email_data = $user;
                    $page = "emails.welcome";
                    $email = $user->email;
                    Helper::send_email($page,$subject,$email,$email_data);
                }

                if($user->is_verified == USER_EMAIL_NOT_VERIFIED) {

                    if(Setting::get('email_verify_control') && !in_array($user->login_by, ['facebook' , 'google'])) {

                        // Check the verification code expiry

                        Helper::check_email_verification("" , $user, $error, USER);
                    
                        $response = array('success' => false , 'error_messages' => Helper::get_error_message(503) , 'error_code' => 503);

                        return response()->json($response, 200);

                    }
                
                }

                if($user->status == USER_DECLINED) {
                    
                    $response = array('success' => false , 'error_messages' => Helper::get_error_message(502) , 'error_code' => 502);

                    return response()->json($response, 200);
                
                }

                // Response with registered user details:

                $response_array = array(
                    'success' => true,
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'gender' => $user->gender,
                    'email' => $user->email,
                    'picture' => $user->picture,
                    'token' => $user->token,
                    'token_expiry' => $user->token_expiry,
                    'login_by' => $user->login_by,
                    'user_type' => $user->user_type,
                    'social_unique_id' => $user->social_unique_id,
                    'push_status' => $user->push_status,
                    'payment_subscription' => Setting::get('ios_payment_subscription_status')

                );

                $response_array = Helper::null_safe($response_array);

                Log::info('Registration completed');

            }
        }

        return response()->json($response_array, 200);
    
    }

    /**
     * User manual and social login 
     *
     *
     */

    public function login(Request $request) {

        $response_array = [];

        $operation = false;

        $basicValidator = Validator::make(
            $request->all(),
            array(
                'device_token' => 'required',
                'device_type' => 'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS,
                'login_by' => 'required|in:manual,facebook,google',
            )
        );

        if($basicValidator->fails()){
            
            $errors = implode(',',$basicValidator->messages()->all());
            
            $response_array = ['success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $errors];
        
        } else {

            $login_by = $request->login_by;
            /*validate manual login fields*/
            $manualValidator = Validator::make(
                $request->all(),
                array(
                    'email' => 'required|email',
                    'password' => 'required',
                )
            );

            if ($manualValidator->fails()) {

                $errors = implode(',',$manualValidator->messages()->all());

                $response_array = ['success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $errors];
            
            } else {

                // Validate the user credentials

                if($user = User::where('email', '=', $request->email)->first()) {

                    if($user->is_verified == USER_EMAIL_NOT_VERIFIED) {

                        if(Setting::get('email_verify_control') && !in_array($user->login_by, ['facebook' , 'google'])) {

                            // Check the verification code expiry

                            Helper::check_email_verification("" , $user, $error, USER);
                        
                            $response = array('success' => false , 'error_messages' => Helper::get_error_message(503) , 'error_code' => 503);

                            return response()->json($response, 200);

                        }
                    
                    }

                    if($user->status == USER_DECLINED) {
                        
                        $response = array('success' => false , 'error_messages' => Helper::get_error_message(502) , 'error_code' => 502);

                        return response()->json($response, 200);
                    
                    }

                    if(Hash::check($request->password, $user->password)){

                        /* manual login success */
                        $operation = true;

                    } else {
                        $response_array = [ 'success' => false, 'error_messages' => Helper::get_error_message(105), 'error_code' => 105 ];
                    }
                    

                } else {
                    $response_array = [ 'success' => false, 'error_messages' => Helper::get_error_message(105), 'error_code' => 105 ];
                }
            
            }

            if($operation) {

                // Generate new tokens
               // $user->token = AppJwt::create(['id' => $user->id, 'email' => $user->email, 'role' => "model"]);
                
                $user->token = Helper::generate_token();

                $user->token_expiry = Helper::generate_token_expiry();

                // Save device details
                $user->device_token = $request->device_token;
                $user->device_type = $request->device_type;
                $user->login_by = $request->login_by;

                $user->save();

                $payment_mode_status = $user->payment_mode ? $user->payment_mode : 0;

                // Respond with user details

                $response_array = array(
                    'success' => true,
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'gender' => $user->gender,
                    'picture' => $user->picture,
                    'chat_picture' => $user->picture,
                    'token' => $user->token,
                    'token_expiry' => $user->token_expiry,
                    'login_by' => $user->login_by,
                    'user_type' => $user->user_type,
                    'social_unique_id' => $user->social_unique_id,
                    'push_status' => $user->push_status,
                    'dob'=> $user->dob,
                    'description'=> $user->description,
                    'payment_subscription' => Setting::get('ios_payment_subscription_status')

                );

            }

        }
        return response()->json($response_array,200);

    }

    public function forgot_password(Request $request) {

        $email =$request->email;
        // Validate the email field
        $validator = Validator::make(
            $request->all(),
            array(
                'email' => 'required|email|exists:users,email',
            )
        );

        if ($validator->fails()) {
            
            $error_messages = implode(',',$validator->messages()->all());
            
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=> $error_messages);
        
        } else {

            $user = User::where('email' , $email)->first();

            if($user) {

                if ($user->login_by == 'manual') {

                    $new_password = Helper::generate_password();
                    $user->password = \Hash::make($new_password);

                    $email_data = array();
                    $subject = tr('user_forgot_email_title');
                    $email = $user->email;
                    $email_data['user']  = $user;
                    $email_data['password'] = $new_password;
                    $page = "emails.forgot-password";
                    $email_send = Helper::send_email($page,$subject,$user->email,$email_data);

                    $response_array['success'] = true;
                    $response_array['message'] = Helper::get_message(106);
                    $user->save();

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('only_manual_can_access')];

                }

            }

        }

        $response = response()->json($response_array, 200);

        return $response;
    }



    public function user_details(Request $request) {

        $user = User::find($request->id);

        if (!empty($user->dob) && $user->dob != "0000-00-00") {

            $user->dob = date('d-m-Y', strtotime($user->dob));

        } else {

            $user->dob = "";
        }

        // $user->dob = date('d-m-Y', strtotime($user->dob));

        $response_array = array(
            'success' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'description'=>$user->description,
            'dob'=> $user->dob,
            'age'=>$user->age_limit,
            'picture' => $user->picture,
            'chat_picture' => $user->picture,
            'mobile' => $user->mobile,
            'gender' => $user->gender,
            'token' => $user->token,
            'token_expiry' => $user->token_expiry,
            'login_by' => $user->login_by,
            'social_unique_id' => $user->social_unique_id,
            'push_status' => $user->push_status,
            'user_type'=>$user->user_type ? $user->user_type : 0
        );
        $response = response()->json(Helper::null_safe($response_array), 200);
        return $response;
    }






    /**
     *
     * Get wishlists
     *
     */
    public function get_wishlist(Request $request)  {

        // Get wishlist 

        $video_tape_ids = Helper::wishlists($request->id);

        $total = get_wishlist_count($request->id);

        $data = [];

        if($video_tape_ids) {

            $base_query = VideoTape::whereIn('video_tapes.id' , $video_tape_ids)   
                                ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id') 
                                ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                                ->where('video_tapes.status' , 1)
                                ->where('video_tapes.publish_status' , 1)
                                ->where('video_tapes.is_approved' , 1)
                                ->where('channels.is_approved', 1)
                                ->where('channels.status', 1)
                                ->where('categories.status', CATEGORY_APPROVE_STATUS)
                               // ->orderby('video_tapes.publish_time' , 'desc')
                                ->videoResponse();

            if ($request->id) {

                // Check any flagged videos are present

                $flag_videos = flag_videos($request->id);

                if($flag_videos) {

                    $base_query->whereNotIn('video_tapes.id',$flag_videos);

                }
            
            }

            $base_query->where('video_tapes.age_limit','<=', checkAge($request));

            $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count' ,12))->get();

            if(count($videos) > 0) {

                foreach ($videos as $key => $value) {

                    $user_details = '';

                    $is_ppv_status = DEFAULT_TRUE;

                    if($request->id) {

                        if($user_details = User::find($request->id)) {

                            $value['user_type'] = $user_details->user_type;

                            $is_ppv_status = ($value->type_of_user == NORMAL_USER || $value->type_of_user == BOTH_USERS) ? ( ( $user_details->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                        }
                    }

                    $value['is_ppv_subscribe_page'] = $is_ppv_status;

                    $value['pay_per_view_status'] = VideoRepo::pay_per_views_status_check($user_details ? $user_details->id : '', $user_details ? $user_details->user_type : '', $value)->getData()->success;

                    $value['currency'] = Setting::get('currency');

                    $value['watch_count'] = number_format_short($value->watch_count);

                    $value['wishlist_status'] = $request->id ? (Helper::check_wishlist_status($request->id,$value->video_tape_id) ? DEFAULT_TRUE : DEFAULT_FALSE): 0;

                    $value['share_url'] = route('user.single' , $value->video_tape_id);

                    array_push($data, $value->toArray());
                }
            
            }

        }

        $response_array = array('success' => true, 'data' => $data , 'total' => $total);

        return response()->json($response_array, 200);
    
    }


    public function spam_videos($request, $count = null, $skip = 0) {

        $query = Flag::where('flags.user_id', $request->id)->select('flags.*')
                    ->where('flags.status', DEFAULT_TRUE)
                    ->leftJoin('video_tapes', 'flags.video_tape_id', '=', 'video_tapes.id')
                    ->where('video_tapes.is_approved' , 1)
                    ->where('video_tapes.status' , 1)
                    ->where('video_tapes.age_limit','<=', checkAge($request))
                    ->orderBy('flags.created_at', 'desc');

        if($count) {

            $paginate = $query->paginate($count);

            $model = array('data' => $paginate->items(), 'pagination' => (string) $paginate->links());


        } else if($skip) {

            $paginate = $query->skip($skip)->take(Setting::get('admin_take_count' ,12))->get();

            $model = array('data' => $paginate, 'pagination' => '');

        } else {

            $paginate = $query->get();

            $model = array('data' => $paginate, 'pagination' => '');

        }

        $items = [];

        foreach ($model['data'] as $key => $value) {
            
            $items[] = displayVideoDetails($value->videoTape, $request->id);

        }

        return response()->json(['items'=>$items, 'pagination'=>isset($model['pagination']) ? $model['pagination'] : 0]);
    }


    /**
     * Get History videos of the user
     *
     */

    public function get_history(Request $request) {

        // Get History 

        $video_tape_ids = Helper::history($request->id);

        $total = get_history_count($request->id);

        $data = [];

        if($video_tape_ids) {

            $base_query = VideoTape::whereIn('video_tapes.id' , $video_tape_ids)   
                                ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id') 
                                ->where('video_tapes.status' , 1)
                                ->where('video_tapes.publish_status' , 1)
                                ->where('video_tapes.is_approved' , 1)
                                ->orderby('video_tapes.publish_time' , 'desc')
                                ->videoResponse();

            if ($request->id) {

                // Check any flagged videos are present

                $flag_videos = flag_videos($request->id);

                if($flag_videos) {

                    $base_query->whereNotIn('video_tapes.id',$flag_videos);

                }
            
            }


            $base_query->where('video_tapes.age_limit','<=', checkAge($request));

            $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count' ,12))->get();

            if(count($videos) > 0) {

                foreach ($videos as $key => $value) {

                    $user_details = '';

                    $is_ppv_status = DEFAULT_TRUE;

                    if($request->id) {

                        if($user_details = User::find($request->id)) {

                            $value['user_type'] = $user_details->user_type;

                            $is_ppv_status = ($value->type_of_user == NORMAL_USER || $value->type_of_user == BOTH_USERS) ? ( ( $user_details->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                        }
                    }


                    $value['is_ppv_subscribe_page'] = $is_ppv_status;

                    $value['currency'] = Setting::get('currency');

                    $value['pay_per_view_status'] = VideoRepo::pay_per_views_status_check($user_details ? $user_details->id : '', $user_details ? $user_details->user_type : '', $value)->getData()->success;

                    $value['watch_count'] = number_format_short($value->watch_count);

                    $value['wishlist_status'] = $request->id ? (Helper::check_wishlist_status($request->id,$value->video_tape_id) ? DEFAULT_TRUE : DEFAULT_FALSE): 0;

                    $value['history_status'] = $request->id ? Helper::history_status($value->id,$value->video_tape_id) : 0;

                    $value['share_url'] = route('user.single' , $value->video_tape_id);

                    array_push($data, $value->toArray());
                }
            
            }

        }

        //get wishlist

        // $history = VideoRepo::watch_list($request,NULL,$request->skip);


        $response_array = array('success' => true, 'data' => $data , 'total' => $total);

        return response()->json($response_array, 200);
    
    }

    public function get_channels(Request $request) {

        $channels = getChannels();

        if($channels) {

            $response_array = array('success' => true , 'categories' => $channels->toArray());

        } else {
            $response_array = array('success' => false,'error_messages' => Helper::get_error_message(135),'error_code' => 135);
        }

        $response = response()->json($response_array, 200);
        return $response;
    }


    public function get_videos(Request $request) {

        $channels = VideoRepo::all_videos(WEB);

        if($channels) {

            $response_array = array('success' => true , 'channels' => $channels->toArray());

        } else {
            $response_array = array('success' => false,'error_messages' => Helper::get_error_message(135),'error_code' => 135);
        }

        $response = response()->json($response_array, 200);
        
        return $response;
    }

    /** 
     * home()
     *
     * return list of videos 
     */

    public function home(Request $request) {

        $data = [];

        $base_query = VideoTape::where('video_tapes.is_approved' , 1)   
                            ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id') 
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.publish_status' , 1)
                            ->orderby('video_tapes.created_at' , 'desc')
                            ->where('channels.is_approved', 1)
                            ->where('channels.status', 1)
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->videoResponse();

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }

        }

        $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count' ,12))->get();

        if(count($videos) > 0) {

            foreach ($videos as $key => $value) {

                $data[] = displayVideoDetails($value, $request->id);

            }
        }

        $response_array = array('success' => true , 'data' => $data);

        return response()->json($response_array , 200);

    }

    /** 
     * trending()
     *
     * return list of videos 
     */

    public function trending(Request $request) {

        $data = [];

        $base_query = VideoTape::where('video_tapes.is_approved' , 1)   
                            ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.publish_status' , 1)
                            ->orderby('video_tapes.watch_count' , 'desc')
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->videoResponse();

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }
        
        }

        $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count' ,12))->get();

        if(count($videos) > 0) {

            foreach ($videos as $key => $value) {

                $data[] = displayVideoDetails($value, $request->id);
                
            }
        }

        $response_array = array('success' => true , 'data' => $data);

        return response()->json($response_array , 200);

    }


    public function get_channel_videos(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'channel_id' => 'required|integer|exists:channels,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $data = array();

            $channels = Channel::where('status', 1)->where('id', $request->channel_id)->first();

            if($channels) {

                $videos = VideoRepo::channelVideos($request, $channels->id, '', $request->skip);

                if(count($videos) > 0) {

                    $data = $videos;
                }

                $channel_status = DEFAULT_FALSE;

                if($request->id) {

                    $channel_status = check_channel_status($request->id, $channels->id);

                }

                $subscriberscnt = subscriberscnt($channels->id);
                
            }

            $is_mychannel = DEFAULT_FALSE;

            $my_channel = Channel::where('user_id', $request->id)->where('id', $request->channel_id)->first();

            if ($my_channel) {

                $is_mychannel = DEFAULT_TRUE;

            }

            $response_array = array('success' => true, 'channel_id'=>$channels->id, 
                        'channel_name'=>$channels->name, 'channel_image'=>$channels->picture,
                        'channel_cover'=>$channels->cover, 
                        'channel_description'=>$channels->description,
                        'is_subscribed'=>$channel_status,
                        'subscribers_count'=>$subscriberscnt,
                        'is_mychannel'=>$is_mychannel,
                        'data' => $data);
        }

        $response = response()->json($response_array, 200);

        return $response;

    }

    /**
     * Function single_video()
     *
     * Return particular video details 
     *
     */

    public function single_video(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'video_tape_id' => 'required|integer|exists:video_tapes,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);

        } else {

            $login_by = $request->login_by ? $request->login_by : 'android';

            $data = array();

            // Check the video is in flg lists

            $check_flag_video = Flag::where('video_tape_id' , $request->video_tape_id)->where('user_id' ,$request->id)->count();

            if(!$check_flag_video) {

                $video_tape_details = VideoTape::find($request->video_tape_id);

                // Check the channel is private or public start

                $user_channel_subscriptions = UserSubscription::where('channel_id', $video_tape_details->channel_id)->where('status', APPROVED)->count();

                Log::info("user_channel_subscriptions".$user_channel_subscriptions);

                if($user_channel_subscriptions != 0) {

                    // Check the user subscribed for this channel 

                    $user_channel_subscription_payments  = UserSubscriptionPayment::where('user_id', $request->id)
                                                            ->where('channel_id', $video_tape_details->channel_id)
                                                            ->where('status', PAID_STATUS)
                                                            ->count();

                    if($user_channel_subscription_payments <= 0) {

                        $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(5001) , 'error_code' => 5001];

                        return response()->json($response_array, 200);

                    }

                }

                // Check the channel is private or public end

                $data = VideoRepo::single_response($request->video_tape_id , $request->id , $login_by);

                if(count($data) > 0) {

                    if($data['is_approved'] == ADMIN_VIDEO_DECLINED_STATUS || $data['status'] == USER_VIDEO_DECLINED_STATUS || $data['channel_approved_status'] == ADMIN_CHANNEL_DECLINED_STATUS || $data['channel_status'] == USER_CHANNEL_DECLINED_STATUS) {

                        return response()->json(['success'=>false, 'error_messages'=>tr('video_is_declined')]);

                    }

                    // Video if not published

                    if ($data['publish_status'] != PUBLISH_NOW) {

                        return response()->json(['success'=>false, 'error_messages'=>tr('video_not_yet_publish')]);
                    }


                    // Comments Section

                    $comments = [];

                    if($comments = Helper::video_ratings($request->video_tape_id,0)) {

                        $comments = $comments->toArray();

                    }

                    $data['comments'] = $comments;

                    $data['suggestions'] = VideoRepo::suggestions($request);
                    
                    $response_array = ['success' => true , 'data' => $data];

                } else {
                    $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(1001) , 'error_code' => 1001];
                }

            } else {

                $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(1000) ,  'error_code' => 1000];
            }

        }

        return response()->json($response_array, 200);

    }

    public function search_video(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key' => '',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {

            $error = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error_messages' => $error, 'error_code' => 101);

        } else {


            $data = [];

            $base_query = VideoTape::where('video_tapes.is_approved' , 1)   
                                ->videoResponse()
                                ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                                ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                                ->where('video_tapes.status' , 1)
                                ->where('video_tapes.publish_status' , 1)
                                ->where('channels.is_approved', 1)
                                ->where('channels.status', 1)
                                ->where('categories.status', CATEGORY_APPROVE_STATUS)
                                ->where('title', 'like', "%".$request->key."%")
                                ->orderby('video_tapes.watch_count' , 'desc');
                               //  ->select('video_tapes.id as video_tape_id' , 'video_tapes.title');
            $user_details = '';


            

            if ($request->id) {

                // Check any flagged videos are present

                $flag_videos = flag_videos($request->id);

                if($flag_videos) {

                    $base_query->whereNotIn('video_tapes.id',$flag_videos);

                }

                $user_details = User::find($request->id);

            
            }

            $base_query->where('video_tapes.age_limit','<=', checkAge($request));

            $data = $base_query->skip($request->skip)->take(Setting::get('admin_take_count' ,12))->get();

            $items = [];


            if (count($data) > 0) {

                foreach ($data as $key => $value) {

                    $is_ppv_status = DEFAULT_TRUE;

                    if ($request->id) {

                        $is_ppv_status = ($value->type_of_user == NORMAL_USER || $value->type_of_user == BOTH_USERS) ? ( ( $user_details->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                    }
                   
                    $currency = Setting::get('currency');

                    $is_ppv_subscribe_page = $is_ppv_status;

                    $pay_per_view_status = VideoRepo::pay_per_views_status_check($user_details ? $user_details->id : '', $user_details ? $user_details->user_type : '', $value)->getData()->success;

                    $amount = $value->ppv_amount;

                    $ppv_notes = !$pay_per_view_status ? ($value->type_of_user == 1 ? tr('normal_user_note') : tr('paid_user_note')) : ''; 

                    $items[] = [
                        'video_tape_id'=>$value->video_tape_id,
                            'title'=>$value->title,
                            'currency'=>$currency,
                            'is_ppv_subscribe_page'=>$is_ppv_subscribe_page,
                            'pay_per_view_status'=>$pay_per_view_status,
                            'ppv_amount'=>$amount,
                            'ppv_notes'=>$ppv_notes
                            ];


                }

            }


            $response_array = array('success' => true, 'data' => $items);
        }

        return response()->json($response_array, 200);

    }

    public function privacy(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'privacy')->first();

        if($page) {

            $page_data['type'] = "privacy";
            $page_data['heading'] = $page->heading;
            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);

        return response()->json($response_array,200);

    }

    public function about(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'about')->first();

        if($page) {

            $page_data['type'] = 'about';

            $page_data['heading'] = $page->heading;

            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);
        return response()->json($response_array,200);

    }

    public function terms(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'terms')->first();

        if($page) {

            $page_data['type'] = "Terms";

            $page_data['heading'] = $page->heading;

            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);
        return response()->json($response_array,200);

    }

    public function help(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'help')->first();

        if($page) {

            $page_data['type'] = "help";

            $page_data['heading'] = $page->heading;

            $page_data ['content'] = $page->description;
        }

        $response_array = ['success' => true , 'page' => $page_data];

        return response()->json($response_array,200);

    }

    public function settings(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'status' => 'required',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $user = User::find($request->id);
            $user->push_status = $request->status;
            $user->save();

            if($request->status) {
                $message = tr('push_notification_enable');
            } else {
                $message = tr('push_notification_disable');
            }

            $response_array = array('success' => true, 'message' => $message , 'push_status' => $user->push_status, 'data'=>['id'=>$user->id, 'token'=>$user->token]);
        }

        $response = response()->json($response_array, 200);
        return $response;
   
    }


    /** 
     *  Provider Send Redeem request to Admin
     *
     */

    public function send_redeem_request(Request $request) {

        if(Setting::get('redeem_control') == REDEEM_OPTION_ENABLED) {

            //  Get admin configured - Minimum Provider Credit

            $minimum_redeem = Setting::get('minimum_redeem' , 1);

            // Get Provider Remaining Credits 

            $redeem_details = Redeem::where('user_id' , $request->id)->first();

            if($redeem_details) {

                $remaining = $redeem_details->remaining;

                // check the provider have more than minimum credits

                if($remaining > $minimum_redeem) {

                    $redeem_amount = abs(intval($remaining - $minimum_redeem));

                    // Check the redeems is not empty

                    if($redeem_amount) {

                        // Save Redeem Request

                        $redeem_request = new RedeemRequest;

                        $redeem_request->user_id = $request->id;

                        $redeem_request->request_amount = $redeem_amount;

                        $redeem_request->status = false;

                        $redeem_request->save();

                        // Update Redeems details 

                        $redeem_details->remaining = abs($redeem_details->remaining-$redeem_amount);

                        $redeem_details->save();


                        $response_array = ['success' => true];

                    } else {

                        $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(149) , 'error_code' => 149];
                    }

                } else {
                    $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(148) ,'error_code' => 148];
                }

            } else {
                $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(151) , 'error_code' => 151];
            }
        } else {
            $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(147) , 'error_code' => 147];
        }

        return response()->json($response_array , 200);

    }

    /**
     * Get redeem requests
     * 
     *
     */

    public function redeems(Request $request) {

        if(Setting::get('redeem_control') == REDEEM_OPTION_ENABLED) {

            $data = Redeem::where('user_id' , $request->id)->select('total' , 'paid' , 'remaining' , 'status')->get()->toArray();

            $response_array = ['success' => true , 'data' => $data];

        } else {
            $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(147) , 'error_code' => 147];
        }

        return response()->json($response_array , 200);
    
    }

    public function redeem_request_cancel(Request $request) {

        $validator = Validator::make($request->all() , [
            'redeem_request_id' => 'required|exists:redeem_requests,id,user_id,'.$request->id,
            ]);

         if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            if($redeem_details = Redeem::where('user_id' , $request->id)->first()) {

                if($redeem_request_details = RedeemRequest::find($request->redeem_request_id)) {

                    // Check status to cancel the redeem request

                    if(in_array($redeem_request_details->status, [REDEEM_REQUEST_SENT , REDEEM_REQUEST_PROCESSING])) {
                        // Update the redeeem 

                        $redeem_details->remaining = $redeem_details->remaining + abs($redeem_request_details->request_amount);

                        $redeem_details->save();

                        // Update the redeeem request Status

                        $redeem_request_details->status = REDEEM_REQUEST_CANCEL;

                        $redeem_request_details->save();

                        $response_array = ['success' => true];


                    } else {
                        $response_array = ['success' => false ,  'error_messages' => Helper::get_error_message(150) , 'error_code' => 150];
                    }

                } else {
                    $response_array = ['success' => false ,  'error_messages' => Helper::get_error_message(151) , 'error_code' => 151];
                }

            } else {

                $response_array = ['success' => false ,  'error_messages' => Helper::get_error_message(151) , 'error_code' =>151 ];
            }

        }

        return response()->json($response_array , 200);

    }


    /**
     * Function Name : redeem_request_list()
     * 
     * List of redeem requests based on logged in user id 
     *
     * @param object $request - User id ,token
     *
     * @return redeem list wih boolean response
     */
    public function redeem_request_list(Request $request) {

        $currency = Setting::get('currency');

        $model = RedeemRequest::where('user_id' , $request->id)
                ->select('request_amount' , 
                     DB::raw("'$currency' as currency"),
                     DB::raw('DATE_FORMAT(created_at , "%e %b %y") as requested_date'),
                     'paid_amount',
                     DB::raw('DATE_FORMAT(updated_at , "%e %b %y") as paid_date'),
                     'status',
                     'id as redeem_request_id'
                 )
                ->orderBy('created_at', 'desc')
                ->get();

        $redeem_details = Redeem::where('user_id' , $request->id)
                ->select('total' , 'paid' , 'remaining' , 'status', DB::raw("'$currency' as currency"))
                ->first();

        if(!$redeem_details) {

            // To avoid <null> value (http://prntscr.com/jm33cq), created dummy object with empty values

            $redeem_details = new Redeem;

            $redeem_details->total = $redeem_details->paid = $redeem_details->remaining = 0;

            $redeem_details->status = 0;
            
            $redeem_details->currency = $currency;

            // NO NEED TO SAVE THE DETAILS
        }

        $data = [];

        foreach ($model as $key => $value) {

            $redeem_status = redeem_request_status($value->status);

            $redeem_cancel_status = in_array($value->status, [REDEEM_REQUEST_SENT , REDEEM_REQUEST_PROCESSING]) ? 1 : 0;
            
            $data[] = [
                    'redeem_request_id'=>$value->redeem_request_id,
                    'request_amount' => $value->request_amount,
                      'redeem_status'=>$redeem_status,
                      'currency'=>$value->currency,
                      'requested_date'=>$value->requested_date,
                      'paid_amount'=>$value->paid_amount,
                      'paid_date'=>$value->paid_date,
                      'redeem_cancel_status'=>$redeem_cancel_status,
                      'status'=>$value->status
            ];

        }

        $response_array = ['success' => true , 'data' => $data, 'redeem_amount'=> $redeem_details];

        return response()->json($response_array , 200);
    
    }
   

    public function user_channel_list(Request $request) {

        $age = 0;

        $channel_id = [];

        $query = Channel::select('channels.*', 'video_tapes.id as video_tape_id', 'video_tapes.is_approved',
                    'video_tapes.status', 'video_tapes.channel_id')
                ->leftJoin('video_tapes', 'video_tapes.channel_id', '=', 'channels.id')
                // ->where('channels.status', DEFAULT_TRUE)
                ->groupBy('channels.id')
                ->where('channels.user_id',$request->id);

        /*

        where('channels.is_approved', DEFAULT_TRUE)

        if($request->id) {

            $user = User::find($request->id);

            $age = $user->age_limit;

            $age = $age ? ($age >= Setting::get('age_limit') ? 1 : 0) : 0;

            if ($request->id) {

                $channel_id = ChannelSubscription::where('user_id', $request->id)->pluck('channel_id')->toArray();

                $query->whereIn('channels.id', $channel_id);
            }


            $query->where('video_tapes.age_limit','<=', $age);

        }*/

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $channels = $query->skip($request->skip)->take(Setting::get('admin_take_count', 12))
                ->get();


        } else {

            $channels = $query->paginate(16);

            $items = $channels->items();

        }   

        $lists = [];

        foreach ($channels as $key => $value) {
            $lists[] = ['channel_id'=>$value->id, 
                    'user_id'=>$value->user_id,
                    'picture'=> $value->picture, 
                    'title'=>$value->name,
                    'description'=>$value->description, 
                    'created_at'=>$value->created_at->diffForHumans(),
                    'no_of_videos'=>videos_count($value->id, MY_CHANNEL),
                    'subscribe_status'=>$request->id ? check_channel_status($request->id, $value->id) : '',
                    'no_of_subscribers'=>$value->getChannelSubscribers()->count(),
            ];

        }

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $response_array = ['success'=>true, 'data'=>$lists];

        } else {

            $pagination = (string) $channels->links();

            $response_array = ['success'=>true, 'channels'=>$lists, 'pagination'=>$pagination];
        }

        return response()->json($response_array);
    }

    /**
     * Like Videos
     *
     * @return JSON Response
     */

    public function likevideo(Request $request) {

        $validator = Validator::make($request->all() , [
            'video_tape_id' => 'required|exists:video_tapes,id',
        ]);

        if ($validator->fails()) {
            
            $error_messages = implode(',', $validator->messages()->all());
            
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 
                    'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $model = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                    ->where('user_id',$request->id)->first();

            $like_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            $video_tape_details = VideoTape::find($request->video_tape_id);

            $like_point_to_owner = NO;

            if (!$model) {

                $model = new LikeDislikeVideo;

                $model->video_tape_id = $request->video_tape_id;

                $model->user_id = $request->id;

                $model->like_status = DEFAULT_TRUE;

                $model->dislike_status = DEFAULT_FALSE;

                $model->save();

                $like_point_to_owner = YES;

                $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count+1), 'dislike_count'=>number_format_short($dislike_count)];

            } else {

                if($model->dislike_status) {

                    $model->like_status = DEFAULT_TRUE;

                    $model->dislike_status = DEFAULT_FALSE;

                    $model->save();

                    $like_point_to_owner = YES;

                    $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count+1), 'dislike_count'=>number_format_short($dislike_count-1)];

                } else {

                    // $model->delete();
                    // $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count-1), 'dislike_count'=>number_format_short($dislike_count)];

                    $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count), 'dislike_count'=>number_format_short($dislike_count)];

                }

            }

        }

        Log::info("like_point_to_owner - - - - - - - ".$like_point_to_owner);
        
        // ADD POINTS TO THE OWNER USER start
               $point_types = DB::table('point_types')->where("name","Like")->first();

            $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","video")->orderBy('id','desc')->first();
            // print_r($data->point);die;
        if($like_point_to_owner == YES && $video_tape_details) {

            if($video_tape_details->user_id != $request->id) {
                
                // $inputRequest = new \stdClass;

                $inputRequest = [];

                $inputRequest['user_id'] = $request->id;

                $inputRequest['owner_user_id'] = $video_tape_details->user_id;

                $inputRequest['point_type'] = POINT_TYPE_LIKE_VIDEO;

                $inputRequest['video_tape_id'] = $request->video_tape_id;

                // $inputRequest['points'] = Setting::get('user_points') ?: 0;
                $inputRequest['points'] = $data->point ?: 0;

                Log::info("UserPointJob - LIKE".print_r($inputRequest, true));

                 // dispatch(new UserPointJob($inputRequest));
                $this->Credit_Points($inputRequest);
                $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $request->id; 
                  $this->Credit_Points($inputRequest);
                // dispatch(new UserPointJob($inputRequest));

            }   

        }
          $user =  User::find($request->id);
        $response_array['total_points'] = $user->total_points;
        // ADD POINTS TO THE OWNER USER ends
            
        return response()->json($response_array);

    }
    public function Tipme($request){
              $video_tape_details = VideoTape::find($request->video_tape_id);
               $inputRequest = [];

                $inputRequest['user_id'] = $request->id;

                $inputRequest['owner_user_id'] = $video_tape_details->user_id;

                $inputRequest['point_type'] = POINT_TYPE_TIP_ME;

                $inputRequest['video_tape_id'] = $request->video_tape_id;

              $point_types = DB::table('point_types')->where("name","Tipme")->first();

               $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","tip_credit")->orderBy('id','desc')->first();
                $inputRequest['points'] = $data->point?: 0;
              $user = User::find($request->id);
        if($user->total_points==0.00 || $user->total_points=="0.00"){
          $response_array = ['success'=>false, 'message'=>"You do'nt have sufficiant balance"];
        }else{
                Log::info("UserPointJob - LIKE".print_r($inputRequest, true));
                  $this->Credit_Points($inputRequest);
                // dispatch(new UserPointJob($inputRequest));

                $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $request->id;
                $inputRequest['point_type'] = POINT_TYPE_TIP_CREDIT; 
                 $inputRequest['points'] = -1;
                  $this->Credit_Points($inputRequest);
                // dispatch(new UserPointJob($inputRequest));
                   $user = User::find($request->id);
                    $notification =  new Notification;
                    $notification->sender_id = $user->id;
                    $notification->reciever_id = $video_tape_details->user_id;
                    
                    $notification->label = "Tip Credit";
                    $notification->type = "tip_credit";
                    $notification->message =  $user->name. " Added Tip Credit to your video";
                    $notification->save();
                    $tipcount = UserPoint::where('video_tape_id',$request->video_tape_id) ->select(DB::raw("sum(points) as total_tip"))->where('point_type',POINT_TYPE_TIP_ME)->first();
                     $response_array = ['success'=>true, 'tip_count'=>$tipcount->total_tip, 'total_points'=>$user->total_points];
                 }
                return $response_array;


    }



       function product_tip($inputRequest){
          $this->inputRequest = $inputRequest;

// Log::info(print_r($this->inputRequest, true));
        Log::info("Product inputRequest".print_r($this->inputRequest, true));
       $video_tape_id = isset($this->inputRequest['video_tape_id']) ? $this->inputRequest['video_tape_id'] : 0;
       $channel_id = isset($this->inputRequest['channel_id']) ? $this->inputRequest['channel_id'] : 0;
       $product_id = isset($this->inputRequest['product_id']) ? $this->inputRequest['product_id'] : 0;
        $user_details = User::find($this->inputRequest['owner_user_id']);

        $userpoints =   UserPoint::where('user_id',$this->inputRequest['user_id'])->where('owner_user_id',$this->inputRequest['owner_user_id'])->where('point_type',$this->inputRequest['point_type']);
   
          if($video_tape_id && $video_tape_id != 0) {
            $userpoints->where('video_tape_id', $video_tape_id);
          } else if( $video_tape_id && $channel_id != 0) {
            $userpoints->where('channel_id', $channel_id);
          } else if($product_id && $product_id != 0){
            $userpoints->where('product_id', $product_id);
          }
      if($this->inputRequest['point_type'] == POINT_TYPE_TIP_CREDIT || $this->inputRequest['point_type'] == POINT_TYPE_TIP_ME){
           $userpointss = 0;
             Log::info("User POINT_TYPE_TIP_ME or POINT_TYPE_TIP_CREDIT");

        }else{
           $userpointss = $userpoints->count();
        }
       
        
        if($userpointss == 0){
        if($user_details) {

            $user_point_details = new UserPoint;
                                  
            $user_point_details->user_id = $this->inputRequest['user_id'];

            $user_point_details->owner_user_id = $this->inputRequest['owner_user_id'];

            $user_point_details->point_type = $this->inputRequest['point_type'];

            $user_point_details->video_tape_id = isset($this->inputRequest['video_tape_id']) ? $this->inputRequest['video_tape_id'] : 0;

            $user_point_details->channel_id = isset($this->inputRequest['channel_id']) ? $this->inputRequest['channel_id'] : 0;
           
            $user_point_details->product_id = isset($this->inputRequest['product_id']) ? $this->inputRequest['product_id'] : 0;
            
            $user_points =  $this->inputRequest['points']?: 0;

            if($this->inputRequest['point_type'] == POINT_TYPE_DISLIKE_VIDEO || $this->inputRequest['point_type'] == POINT_TYPE_TIP_CREDIT) {

                $user_points = -(int) $user_points;
                
            }



            $user_point_details->points = $user_points;

            $user_point_details->save();

            $user_details->total_points += $user_points;

            $user_details->save();

        } else {

            Log::info("User details not found");

        }
    }else{
         Log::info("User already gave points for this video");
    }
    }
    function oldvideo_points(){
        
         $like_video = LikeDislikeVideo::where('like_status', DEFAULT_TRUE)->get();
          foreach($like_video as $v){
            $video_tape_details = VideoTape::find($v->video_tape_id);
            
            if($video_tape_details){
              $inputRequest = [];

               $inputRequest['user_id'] = $v->user_id;

                $inputRequest['owner_user_id'] = $video_tape_details->user_id;

                $inputRequest['point_type'] = POINT_TYPE_LIKE_VIDEO;

                $inputRequest['video_tape_id'] = $v->video_tape_id;

                $inputRequest['points'] = Setting::get('user_points') ?: 0;

                Log::info("UserPointJob - LIKE".print_r($inputRequest, true));

                dispatch(new UserPointJob($inputRequest));

                $inputRequest['owner_user_id'] = $inputRequest['user_id'] =  $v->user_id;

                dispatch(new UserPointJob($inputRequest));
            }
            }
          $model = ChannelSubscription::get();
         
         foreach($model as $m){
            
              $channel_details = ChannelSubscription::find($m->id);
            
              if($channel_details) {

                    $inputRequest1 = [];

                    $inputRequest1['user_id'] = $m->user_id;

                    $inputRequest1['owner_user_id'] = $channel_details->user_id;

                    $inputRequest1['point_type'] = POINT_TYPE_SUBSCRIBE_VIDEO;

                    $inputRequest1['channel_id'] = $m->channel_id;

                    $inputRequest1['points'] = Setting::get('user_points') ?: 0;

                    Log::info("UserPointJob - Subscribe old channel".print_r($inputRequest1, true));

                    dispatch(new UserPointJob($inputRequest1));   

                    $inputRequest1['owner_user_id'] = $inputRequest1['user_id'] = $m->user_id; 

                    dispatch(new UserPointJob($inputRequest1)); 

                }
            }
                $rating =  UserRating::get();
          foreach($rating as $r){

             $video_tape_details = VideoTape::find($r->video_tape_id);
                // ADD POINTS TO THE OWNER USER start
                if($video_tape_details) {
                    if($video_tape_details->user_id != $r->user_id) {

                        $inputRequest2 = [];

                        $inputRequest2['user_id'] = $r->user_id;

                        $inputRequest2['owner_user_id'] = $video_tape_details->user_id;

                        $inputRequest2['point_type'] = POINT_TYPE_COMMENT_VIDEO;

                        $inputRequest2['video_tape_id'] = $r->video_tape_id;

                        $inputRequest2['points'] = Setting::get('user_points') ?: 0;

                        Log::info("UserPointJob - COMMENT ".print_r($inputRequest2, true));
                        dispatch(new UserPointJob($inputRequest2));

                        $inputRequest2['owner_user_id'] = $inputRequest2['user_id'] = $r->user_id; 

                        dispatch(new UserPointJob($inputRequest2));
                    }
                }
            }

    }

    /**
     * Dis Like Videos
     *
     * @return JSON Response
     */

    public function dislikevideo(Request $request) {

        $validator = Validator::make($request->all() , [
            'video_tape_id' => 'required|exists:video_tapes,id',
        ]);

        if ($validator->fails()) {
            
            $error_messages = implode(',', $validator->messages()->all());
            
            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 
                    'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $model = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                    ->where('user_id',$request->id)->first();

            $like_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            $video_tape_details = VideoTape::find($request->video_tape_id);

            $dis_like_point_to_owner = NO;

            if (!$model) {

                $model = new LikeDislikeVideo;

                $model->video_tape_id = $request->video_tape_id;

                $model->user_id = $request->id;

                $model->like_status = DEFAULT_FALSE;

                $model->dislike_status = DEFAULT_TRUE;

                $model->save();

                $dis_like_point_to_owner = YES;

                $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count), 'dislike_count'=>number_format_short($dislike_count+1)];

            } else {

                if($model->like_status) {

                    $model->like_status = DEFAULT_FALSE;

                    $model->dislike_status = DEFAULT_TRUE;

                    $model->save();

                    $dis_like_point_to_owner = YES;

                    $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count-1), 'dislike_count'=>number_format_short($dislike_count+1)];

                } else {

                    $model->delete();

                    $response_array = ['success'=>true, 'like_count'=>number_format_short($like_count), 'dislike_count'=>number_format_short($dislike_count-1)];

                }

            }

        }

        // ADD POINTS TO THE OWNER USER start

        if($dis_like_point_to_owner == YES && $video_tape_details) {

            if($video_tape_details->user_id != $request->id) {

                // $inputRequest = new \stdClass;

                $inputRequest = [];

                $inputRequest['user_id'] = $request->id;

                $inputRequest['owner_user_id'] = $video_tape_details->user_id;

                $inputRequest['point_type'] = POINT_TYPE_DISLIKE_VIDEO;

                $inputRequest['video_tape_id'] = $request->video_tape_id;

                $inputRequest['points'] = -(int)Setting::get('user_points') ?: 0;

                Log::info("UserPointJob - DISLIKE".print_r($inputRequest, true));

                dispatch(new UserPointJob($inputRequest));  

                $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $request->id; 

                dispatch(new UserPointJob($inputRequest));
            } 

        }

        // ADD POINTS TO THE OWNER USER ends

        return response()->json($response_array);

    }

    public function default_card(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$request->id,
            ),
            array(
                'exists' => 'The :attribute doesn\'t belong to user:'.$request->id
            )
        );

        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error_messages' => $error_messages, 'error_code' => 101);

        } else {

            $user = User::find($request->id);
            
            $old_default = Card::where('user_id' , $request->id)->where('is_default', DEFAULT_TRUE)->update(array('is_default' => DEFAULT_FALSE));

            $card = Card::where('id' , $request->card_id)->update(array('is_default' => DEFAULT_TRUE));

            if($card) {

                if($user) {
                    $user->card_id = $request->card_id;
                    $user->save();
                }

                $response_array = Helper::null_safe(array('success' => true, 'data'=>['id'=>$request->id,'token'=>$user->token]));

            } else {
                $response_array = array('success' => false , 'error_messages' => tr('something_error'));
            }
        }
        return response()->json($response_array , 200);
    
    }

    public function delete_card(Request $request) {
    
        $card_id = $request->card_id;

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$request->id,
            ),
            array(
                'exists' => 'The :attribute doesn\'t belong to user:'.$request->id
            )
        );

        if ($validator->fails()) {
            
            $error_messages = implode(',', $validator->messages()->all());
            
            $response_array = array('success' => false , 'error_messages' => $error_messages , 'error_code' => 101);
        
        } else {

            $user = User::find($request->id);

            if ($user->card_id == $card_id) {

                $response_array = array('success' => false, 'error_messages'=> tr('card_default_error'));

            } else {

                Card::where('id',$card_id)->delete();

                if($user) {

                    // if($user->payment_mode = CARD) {

                        // Check he added any other card
                        
                        if($check_card = Card::where('user_id' , $request->id)->first()) {

                            $check_card->is_default =  DEFAULT_TRUE;

                            $user->card_id = $check_card->id;

                            $check_card->save();

                        } else { 

                            $user->payment_mode = COD;
                            $user->card_id = DEFAULT_FALSE;
                        }
                    // }
                    
                    $user->save();
                }

                $response_array = array('success' => true, 'message'=>tr('card_deleted'), 'data'=> ['id'=>$request->id,'token'=>$user->token]);

            }
            
        }
    
        return response()->json($response_array , 200);
    }

    public function subscription_plans(Request $request) {

        $query = Subscription::select('id as subscription_id',
                'title', 'description', 'plan','amount', 'status', 'created_at' , DB::raw("'$' as currency"))
                ->where('status' , DEFAULT_TRUE);

        if ($request->id) {

            $user = User::find($request->id);

            if ($user) {

               if ($user->zero_subscription_status == DEFAULT_TRUE) {

                   $query->where('amount','>', 0);

               }

            } 

        }

        $model = $query->orderBy('amount' , 'asc')->get();

        $response_array = ['success'=>true, 'data'=>$model];

        return response()->json($response_array, 200);

    }

    public function pay_now(Request $request) {

        Log::info("Pay Now");
        
        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'subscription_id'=>'required|exists:subscriptions,id',
                    'payment_id'=>'required',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ), array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
            ));

            if ($validator->fails()) {
                // Error messages added in response for debugging
                $errors = implode(',',$validator->messages()->all());

                throw new Exception($errors, 101);

            } else {

                $user = User::find($request->id);

                $subscription = Subscription::find($request->subscription_id);

                $total = $subscription->amount;

                $coupon_amount = 0;

                $coupon_reason = '';

                $is_coupon_applied = COUPON_NOT_APPLIED;

                if ($request->coupon_code) {

                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                    if ($coupon) {
                        
                        if ($coupon->status == COUPON_INACTIVE) {

                            $coupon_reason = tr('coupon_inactive_reason');

                        } else {

                            $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                            if ($check_coupon->success) {

                                $is_coupon_applied = COUPON_APPLIED;

                                $amount_convertion = $coupon->amount;

                                if ($coupon->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);

                                }


                                if ($amount_convertion < $subscription->amount) {

                                    $total = $subscription->amount - $amount_convertion;

                                    $coupon_amount = $amount_convertion;

                                } else {

                                    // throw new Exception(Helper::get_error_message(156),156);

                                    $total = 0;

                                    $coupon_amount = $amount_convertion;
                                    
                                }

                                // Create user applied coupon

                                if($check_coupon->code == 2002) {

                                    $user_coupon = UserCoupon::where('user_id', $user->id)
                                            ->where('coupon_code', $request->coupon_code)
                                            ->first();

                                    // If user coupon not exists, create a new row

                                    if ($user_coupon) {

                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                            $user_coupon->no_of_times_used += 1;

                                            $user_coupon->save();

                                        }

                                    }

                                } else {

                                    $user_coupon = new UserCoupon;

                                    $user_coupon->user_id = $user->id;

                                    $user_coupon->coupon_code = $request->coupon_code;

                                    $user_coupon->no_of_times_used = 1;

                                    $user_coupon->save();

                                }

                            } else {

                                $coupon_reason = $check_coupon->error_messages;
                                
                            }

                        }

                    } else {

                        $coupon_reason = tr('coupon_delete_reason');
                    }
                }

                $model = UserPayment::where('user_id' , $request->id)
                            ->where('status', DEFAULT_TRUE)
                            ->orderBy('id', 'desc')->first();

                $user_payment = new UserPayment();

                if ($model) {

                    if (strtotime($model->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                         $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($model->expiry_date)));

                    } else {

                        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                    }

                } else {

                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                }

                $user_payment->payment_id  = $request->payment_id;
                $user_payment->user_id = $request->id;
                $user_payment->subscription_id = $request->subscription_id;

                $user_payment->status = PAID_STATUS;


                $user_payment->payment_mode = PAYPAL;

                // Coupon details

                $user_payment->is_coupon_applied = $is_coupon_applied;

                $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                $user_payment->coupon_amount = $coupon_amount;

                $user_payment->subscription_amount = $subscription->amount;

                $user_payment->amount = $total;

                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;
 
                if($user_payment->save()) {

                    if ($user) {

                        $user->user_type = 1;

                        /*$user->amount_paid += $total;*/

                        $user->expiry_date = $user_payment->expiry_date;

                        /*$now = time(); // or your date as well

                        $end_date = strtotime($user->expiry_date);

                        $datediff =  $end_date - $now;

                        $user->no_of_days = ($user->expiry_date) ? floor($datediff / (60 * 60 * 24)) + 1 : 0;*/

                        if ($user_payment->amount <= 0) {

                            $user->zero_subscription_status = 1;

                        } else {

                            // Referral Commission

                            $referral_commission = Setting::get('referral_commission')/100;
                            $referral_commission2 = Setting::get('referral_commission2')/100;

                            $referral_amount = $user_payment->amount * $referral_commission;
                            $referral_amount2 = $user_payment->amount * $referral_commission2;

                            $subscription_amount_after_referral_split = $paid_amount - $referral_amount;
                            $subscription_amount_after_referral_split2 = $paid_amount - $referral_amount2;

                            $user_payment->referral_commission = $subscription_amount_after_referral_split ?: "";
                            $user_payment->referral_commission2 = $subscription_amount_after_referral_split2 ?: "";

                            Log::info("Amount ".$user_payment->amount);

                            $user_payment->save();

                            PaymentRepo::referral_amount_update($user_payment->user_id, $user_payment->referral_commission);
                            PaymentRepo::tier2_referral_amount_update($user_payment->user_id, $user_payment->referral_commission2);
                        }

                        if ($user->save()) {

                            $response_array = ['success'=>true, 
                                    'message'=>tr('payment_success'), 
                                    'data'=>[
                                        'id'=>$request->id,
                                        'token'=>$user_payment->user ? $user_payment->user->token : '',
                                ]];

                        } else {


                            throw new Exception(tr('user_details_not_saved'));
                            
                        }

                    } else {

                        throw new Exception(tr('user_not_found'));
                        
                    }
                }

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$code];

            return response()->json($response_array);

        }

    }

    public function subscribedPlans(Request $request){

        $validator = Validator::make(
            $request->all(),
            array(
                'skip'=>($request->device_type == DEVICE_WEB) ? '' : 'required|numeric',
            ));

        if ($validator->fails()) {

            // Error messages added in response for debugging
            
            $errors = implode(',',$validator->messages()->all());

            $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

        } else {

            $query = UserPayment::where('user_id' , $request->id)
                        ->leftJoin('subscriptions', 'subscriptions.id', '=', 'subscription_id')
                        ->select('user_id as id',
                                'subscription_id',
                                'user_payments.id as user_subscription_id',
                                \DB::raw('IFNULL(subscriptions.title,"") as title'),
                                \DB::raw('IFNULL(subscriptions.description,"") as description'),
                                \DB::raw('IFNULL(subscriptions.plan,"") as plan'),
                                'subscriptions.amount as current_subscription_amount',
                                'user_payments.amount as amount',
                                'user_payments.status as status',
                                // 'user_payments.expiry_date as expiry_date',
                                \DB::raw('DATE_FORMAT(user_payments.expiry_date , "%e %b %Y") as expiry_date'),
                                'user_payments.created_at as created_at',
                                DB::raw("'$' as currency"),
                                'user_payments.payment_mode',
                                'user_payments.is_coupon_applied',
                                'user_payments.coupon_code',
                                'user_payments.coupon_amount',
                                'user_payments.subscription_amount',
                                'user_payments.coupon_reason',
                                'user_payments.is_cancelled',
                                'user_payments.payment_id',
                                'user_payments.cancel_reason')
                        ->orderBy('user_payments.updated_at', 'desc');
                        
            if ($request->device_type == DEVICE_WEB) {

                $model = $query->paginate(16);

                $response_array = array('success'=>true, 'data' => $model->items(), 'pagination' => (string) $model->links());

            } else {

                $model = $query->skip($request->skip)
                        ->take(Setting::get('admin_take_count' ,12))
                        ->get();

                $data = [];

                foreach ($model as $key => $value) { 

                    $data[] = [

                        'id'=>$value->id,
                        'subscription_id'=>$value->subscription_id,
                        'user_subscription_id'=>$value->user_subscription_id,
                        'title'=>$value->title,
                        'description'=>$value->description,
                        'plan'=>$value->plan,
                        'amount'=>$value->amount,
                        'status'=>$value->status,
                        'expiry_date'=>$value->expiry_date,
                        'created_at'=>$value->created_at->diffForHumans(),
                        'currency'=>$value->currency,
                        'payment_mode'=>$value->payment_mode,
                        'is_coupon_applied'=>$value->is_coupon_applied,
                        'coupon_code'=>$value->coupon_code,
                        'coupon_amount'=>$value->coupon_amount,
                        'subscription_amount'=>$value->subscription_amount,
                        'coupon_reason'=>$value->coupon_reason,
                        'is_cancelled'=>$value->is_cancelled,
                        'payment_id'=>$value->payment_id,
                        'cancel_reason'=>$value->cancel_reason,
                        'active_plan'=>($key == 0 && $value->status) ? ACTIVE_PLAN : NOT_ACTIVE_PLAN,
                    ];


                }

                $response_array = ['success'=>true, 'data'=>$data];
            }

        }

        return response()->json($response_array);

    }


    public function card_details(Request $request) {

        $cards = Card::select('user_id as id','id as card_id','customer_id',
                'last_four', 'card_token', 'is_default', 
            \DB::raw('DATE_FORMAT(created_at , "%e %b %y") as created_date'))->where('user_id', $request->id)->get();

        $response_array = ['success'=>true, 'data'=>$cards];

        return response()->json($response_array, 200);
    }

    /**
     * Show the payment methods.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_card_add(Request $request) {

        $validator = Validator::make($request->all(), 
            array(
                'number' => 'required|numeric',
                'card_token'=>'required',
               // 'month'=>'required',
               // 'year'=>'required',
               // 'cvv'=>'required',
                'card_name'=>'required',
            )
            );

        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());
            
            $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

            return response()->json($response_array);

        } else {

            $userModel = User::find($request->id);

            $last_four = substr($request->number, -4);

            $stripe_secret_key = Setting::get('stripe_secret_key');

            $response = json_decode('{}');

            if($stripe_secret_key) {

                \Stripe\Stripe::setApiKey($stripe_secret_key);

            } else {

                $response_array = ['success'=>false, 'error_messages'=>tr('add_card_is_not_enabled')];

                return response()->json($response_array);
            }

            try {

                // Get the key from settings table
                
                $customer = \Stripe\Customer::create([
                        "card" => $request->card_token,
                        "email" => $userModel->email
                    ]);

                if($customer) {

                    $customer_id = $customer->id;

                    $cards = new Card;
                    $cards->user_id = $userModel->id;
                    $cards->customer_id = $customer_id;
                    $cards->last_four = $last_four;
                    $cards->card_token = $customer->sources->data ? $customer->sources->data[0]->id : "";

                    // $cards->cvv = $request->cvv;

                    $cards->card_name = $request->card_name;

                    // $cards->month = $request->month;

                    // $cards->year = $request->year;

                    // Check is any default is available
                    $check_card = Card::where('user_id', $userModel->id)->first();

                    if($check_card)
                        $cards->is_default = 0;
                    else
                        $cards->is_default = 1;
                    
                    $cards->save();

                    if($userModel && $cards->is_default) {

                        $userModel->payment_mode = 'card';

                        $userModel->card_id = $cards->id;

                        $userModel->save();
                    }

                    $data = [
                            'user_id'=>$request->id, 
                            'id'=>$request->id, 
                            'token'=>$userModel->token,
                            'card_id'=>$cards->id,
                            'customer_id'=>$cards->customer_id,
                            'last_four'=>$cards->last_four, 
                            'card_token'=>$cards->card_token, 
                            'is_default'=>$cards->is_default
                            ];

                    $response_array = array('success' => true,'message'=>tr('add_card_success'), 
                        'data'=> $data);

                    return response()->json($response_array);

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('Could not create client ID')];

                    return response()->json($response_array);

                }
            
            } catch(Exception $e) {

                $response_array = ['success'=>false, 'error_messages'=>$e->getMessage()];

                return response()->json($response_array);

            }

        }

    }    


    public function my_channels(Request $request) {

       $model = Channel::select('id as channel_id', 'name as channel_name')->where('is_approved', DEFAULT_TRUE)->where('status', DEFAULT_TRUE)
            ->where('user_id', $request->id)->get();

        if($model) {

            $response_array = array('success' => true , 'data' => $model);

        } else {
            $response_array = array('success' => false,'error_messages' => Helper::get_error_message(135),'error_code' => 135);
        }

        $response = response()->json($response_array, 200);
        
        return $response;
    }
   function coinpayments_api_transaction($cmd, $amount,$mail,$req = array()) {
       $public_key = config('app.coin_public_key');
    
                   $req = array();
                        $req['version'] = 1;
                        $req['cmd'] = $cmd;
                        $req['key'] = $public_key;
                        $req['format'] = 'json'; //supported values are json and xml
                        $req['amount'] = $amount; //supported values are json and xml
                        $req['buyer_email'] = $mail; //supported values are json and xml
                        $req['currency1'] = 'USD'; //supported values are json and xml
                        $req['currency2'] = 'BTC'; //supported values are json and xml
                        $req['success_url']="https://www.cjclive.com/";
       $post_data = http_build_query($req, '', '&');
       $hmac =   $this->coinpayments_api($cmd,$req);
  
    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://www.coinpayments.net/api.php');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    
    // Execute the call and close cURL handle     
    $data = curl_exec($ch);

    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {

            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {

            $dec = json_decode($data, TRUE);

        }
        if ($dec !== NULL && count($dec)) {
            return $dec;
        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}

function coinpayments_api_status($cmd,$txid, $req = array()) {
        $public_key = config('app.coin_public_key');
    $private_key = config('app.coin_private_key');

                   $req = array();
                        $req['version'] = 1;
                        $req['cmd'] = $cmd;
                        $req['key'] = $public_key;
                        $req['format'] = 'json'; //supported values are json and xml
                        $req['txid'] = $txid; //supported values are json and xml
                        $req['full'] = 1; //supported values are json and xml

      $post_data = http_build_query($req, '', '&');
         $hmac =   $this->coinpayments_api($cmd,$req);
        
    
    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://www.coinpayments.net/api.php');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    
    // Execute the call and close cURL handle     
    $data = curl_exec($ch);                
    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {
            return $dec;
        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}
  function coinpayments_api($cmd,$req) {
    // Fill these in from your API Keys page
    
    $private_key = config('app.coin_private_key');
    
    // Set the API command and required fields
    
    // Generate the query string
    $post_data = http_build_query($req, '', '&');
    // $post_data = "cmd=create_transaction&amount=1&currency1=USD&currency2=BTC&buyer_email=paypal366@gmail.com&version=1&key=f5bf2fb054da50065a6b14caa15de923b5d775564d654d5def2a81f39150ca98";
 
    // Calculate the HMAC signature on the POST data
     return $hmac = hash_hmac('sha512', $post_data, $private_key);
    
    // Create cURL handle and initialize (if needed)
   
   }
        public function stripe_payment(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), 
                array(
                    'subscription_id' => 'required|exists:subscriptions,id',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ), array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
            ));

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                $subscription = Subscription::find($request->subscription_id);

                $user = User::find($request->id);

                if ($subscription) {

                    $total = $subscription->amount;

                    $coupon_amount = 0;

                    $coupon_reason = '';

                    $is_coupon_applied = COUPON_NOT_APPLIED;

                    if ($request->coupon_code) {

                        $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                        if ($coupon) {
                            
                            if ($coupon->status == COUPON_INACTIVE) {

                                $coupon_reason = tr('coupon_inactive_reason');


                            } else {

                                $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                                if ($check_coupon->success) {

                                    $is_coupon_applied = COUPON_APPLIED;

                                    $amount_convertion = $coupon->amount;

                                    if ($coupon->amount_type == PERCENTAGE) {

                                        $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);

                                    }


                                    if ($amount_convertion < $subscription->amount) {

                                        $total = $subscription->amount - $amount_convertion;

                                        $coupon_amount = $amount_convertion;

                                    } else {

                                        // throw new Exception(Helper::get_error_message(156),156);

                                        $total = 0;

                                        $coupon_amount = $amount_convertion;
                                        
                                    }

                                    // Create user applied coupon

                                    if($check_coupon->code == 2002) {

                                        $user_coupon = UserCoupon::where('user_id', $user->id)
                                                ->where('coupon_code', $request->coupon_code)
                                                ->first();

                                        // If user coupon not exists, create a new row

                                        if ($user_coupon) {

                                            if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                $user_coupon->no_of_times_used += 1;

                                                $user_coupon->save();

                                            }

                                        }

                                    } else {

                                        $user_coupon = new UserCoupon;

                                        $user_coupon->user_id = $user->id;

                                        $user_coupon->coupon_code = $request->coupon_code;

                                        $user_coupon->no_of_times_used = 1;

                                        $user_coupon->save();

                                    }

                                } else {

                                    $coupon_reason = $check_coupon->error_messages;
                                    
                                }

                            }

                        } else {

                            $coupon_reason = tr('coupon_delete_reason');
                        }
                    }

                    if ($user) {

                        $check_card_exists = User::where('users.id' , $request->id)
                                        ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                        ->where('cards.id' , $user->card_id)
                                        ->where('cards.is_default' , DEFAULT_TRUE);

                        if($check_card_exists->count() != 0) {

                            $user_card = $check_card_exists->first();

                            if ($total <= 0) {

                                
                                $previous_payment = UserPayment::where('user_id' , $request->id)
                                            ->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();


                                $user_payment = new UserPayment;

                                if($previous_payment) {

                                    if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                                     $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($previous_payment->expiry_date)));

                                    } else {

                                        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                                    }

                                } else {
                                   
                                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                                }

                                $user_payment->payment_id = "free plan";

                                $user_payment->user_id = $request->id;

                                $user_payment->subscription_id = $request->subscription_id;

                                $user_payment->status = 1;

                                $user_payment->amount = $total;

                                $user_payment->payment_mode = CARD;

                                // Coupon details

                                $user_payment->is_coupon_applied = $is_coupon_applied;

                                $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                                $user_payment->coupon_amount = $coupon_amount;

                                $user_payment->subscription_amount = $subscription->amount;

                                $user_payment->amount = $total;

                                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;


                                if ($user_payment->save()) {

                                
                                    if ($user) {

                                        $user->user_type = 1;

                                        // $user->amount_paid += $total;

                                        $user->expiry_date = $user_payment->expiry_date;

                                        /*$now = time(); // or your date as well

                                        $end_date = strtotime($user->expiry_date);

                                        $datediff =  $end_date - $now;

                                        $user->no_of_days = ($user->expiry_date) ? floor($datediff / (60 * 60 * 24)) + 1 : 0;*/

                                        if ($user_payment->amount <= 0) {

                                            $user->zero_subscription_status = 1;

                                        } else {

                                            // Referral Commission

                                            $referral_commission = Setting::get('referral_commission')/100;
                                            $referral_commission2 = Setting::get('referral_commission2')/100;
                                            
                                            $referral_amount = $user_payment->amount * $referral_commission;
                                            $referral_amount2 = $user_payment->amount * $referral_commission2;

                                            $subscription_amount_after_referral_split = $user_payment->amount - $referral_amount;
                                            $subscription_amount_after_referral_split2 = $user_payment->amount - $referral_amount2;

                                            $user_payment->referral_commission = $subscription_amount_after_referral_split ?: "";
                                            $user_payment->referral_commission2 = $subscription_amount_after_referral_split2 ?: "";

                                            Log::info("Amount --- ".$user_payment->amount);
                                            Log::info($user_payment);

                                            $user_payment->save();

                                            PaymentRepo::referral_amount_update($user_payment->user_id, $user_payment->referral_commission);
                                            PaymentRepo::tier2_referral_amount_update($user_payment->user_id, $user_payment->referral_commission2);
                                        }

                                        if ($user->save()) {

                                             $data = ['id' => $user->id , 'token' => $user->token, 'payment_id' => $user_payment->payment_id];

                                            $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                                        } else {


                                            throw new Exception(tr('user_details_not_saved'));
                                            
                                        }

                                    } else {

                                        throw new Exception(tr('user_not_found'));
                                        
                                    }
                                    
                                   
                                } else {

                                    throw new Exception(tr(Helper::get_error_message(902)), 902);

                                }


                            } else {

                                $stripe_secret_key = Setting::get('stripe_secret_key');

                                $customer_id = $user_card->customer_id;

                                if($stripe_secret_key) {

                                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                                } else {

                                    throw new Exception(Helper::get_error_message(902), 902);

                                }

                                try{

                                   $user_charge =  \Stripe\Charge::create(array(
                                      "amount" => $total * 100,
                                      "currency" => "usd",
                                      "customer" => $customer_id,
                                    ));
    
                                   $payment_id = $user_charge->id;
                                   $amount = $user_charge->amount/100;
                                   $paid_status = $user_charge->paid;
                                     
                                    if($paid_status) {
                                         $user_token = new UserToken;
                                         if($total == "0.99" || $total == 0.99 || $total == ".99"){
                                           
                                          $user_token->token = 1;
                                          $user_token->user_id = $request->id;
                                           $token = $user_token->save();

                                         }elseif($total == "99" || $total == 99){
                                            $user_token->token = 3;
                                          $user_token->user_id = $request->id;
                                           $token = $user_token->save();


                                          }elseif($total == "299" || $total == 299){
                                            $user_token->token = 5;
                                          $user_token->user_id = $request->id;
                                           $token = $user_token->save();


                                          }
                                           
                                        $previous_payment = UserPayment::where('user_id' , $request->id)
                                            ->where('status', PAID_STATUS)->orderBy('created_at', 'desc')->first();

                                        $user_payment = new UserPayment;

                                        if($previous_payment) {

                                            $expiry_date = $previous_payment->expiry_date;
                                            $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_date. "+".$subscription->plan." months"));

                                        } else {
                                            
                                            $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                                        }


                                        $user_payment->payment_id  = $payment_id;

                                        $user_payment->user_id = $request->id;

                                        $user_payment->subscription_id = $request->subscription_id;

                                        $user_payment->status = PAID_STATUS;

                                        $user_payment->payment_mode = CARD;


                                        // Coupon details

                                        $user_payment->is_coupon_applied = $is_coupon_applied;

                                        $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                                        $user_payment->coupon_amount = $coupon_amount;

                                        $user_payment->subscription_amount = $subscription->amount;

                                        $user_payment->amount = $total;

                                        $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;


                                        if ($user_payment->save()) {

                                            if ($user) {

                                                $user->user_type = SUBSCRIBED_USER;

                                                $user->expiry_date = $user_payment->expiry_date;


                                                if ($user_payment->amount <= 0) {

                                                    $user->zero_subscription_status = 1;

                                                }  else {
                                                
                                                    // Referral Commission

                                                    $referral_commission = Setting::get('referral_commission')/100;
                                                    $referral_commission2 = Setting::get('referral_commission2')/100;
                                                    
                                                    $subscription_amount_after_referral_split = $user_payment->amount * $referral_commission;
                                                    $subscription_amount_after_referral_split2 = $user_payment->amount * $referral_commission2;

                                                    $user_payment->referral_commission = $subscription_amount_after_referral_split ?: "";
                                                    $user_payment->referral_commission2 = $subscription_amount_after_referral_split2 ?: "";

                                                    Log::info("Amounts ".$user_payment->amount);

                                                    $user_payment->save();
                                                   PaymentRepo::referral_token_update($user_payment->user_id);
                                                    PaymentRepo::referral_amount_update($user_payment->user_id, $user_payment->referral_commission);
                                                    PaymentRepo::tier2_referral_amount_update($user_payment->user_id, $user_payment->referral_commission2);
                                                }

                                                if ($user->save()) {

                                                     $data = ['id' => $user->id , 'token' => $user->token,'payment_id' => $user_payment->payment_id];

                                                    $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                                                } else {


                                                    throw new Exception(tr('user_details_not_saved'));
                                                    
                                                }

                                            } else {

                                                throw new Exception(tr('user_not_found'));
                                                
                                            }

                                        

                                        } else {

                                             throw new Exception(tr(Helper::get_error_message(902)), 902);

                                        }


                                    } else {

                                        $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(903) , 'error_code' => 903);

                                        throw new Exception(Helper::get_error_message(903), 903);

                                    }

                                
                                } catch(\Stripe\Error\RateLimit $e) {

                                    throw new Exception($e->getMessage(), 903);

                                } catch(\Stripe\Error\Card $e) {

                                    throw new Exception($e->getMessage(), 903);

                                } catch (\Stripe\Error\InvalidRequest $e) {
                                    // Invalid parameters were supplied to Stripe's API
                                   
                                    throw new Exception($e->getMessage(), 903);

                                } catch (\Stripe\Error\Authentication $e) {

                                    // Authentication with Stripe's API failed

                                    throw new Exception($e->getMessage(), 903);

                                } catch (\Stripe\Error\ApiConnection $e) {

                                    // Network communication with Stripe failed

                                    throw new Exception($e->getMessage(), 903);

                                } catch (\Stripe\Error\Base $e) {
                                  // Display a very generic error to the user, and maybe send
                                    
                                    throw new Exception($e->getMessage(), 903);

                                } catch (Exception $e) {
                                    // Something else happened, completely unrelated to Stripe

                                    throw new Exception($e->getMessage(), 903);

                                } catch (\Stripe\StripeInvalidRequestError $e) {

                                        Log::info(print_r($e,true));

                                    throw new Exception($e->getMessage(), 903);
                                    
                                
                                }


                            }

                        } else {
     
                            throw new Exception(Helper::get_error_message(901), 901);
                            
                        }

                    } else {

                        throw new Exception(tr('no_user_detail_found'));
                        
                    }

                } else {

                    throw new Exception(Helper::get_error_message(901), 901);

                }         

                
            }

            DB::commit();

            return response()->json($response_array , 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }
    }

    public function coin_payment(Request $request) {

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), 
                array(
                    'subscription_id' => 'required|exists:subscriptions,id',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ), array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
            ));

            if($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                throw new Exception($error_messages, 101);
            }else{
                $subscription = Subscription::find($request->subscription_id);
                $user = User::find($request->id);

                if ($subscription) {
                    $total = $subscription->amount;
                    $coupon_amount = 0;
                    $coupon_reason = '';
                    $is_coupon_applied = COUPON_NOT_APPLIED;
                    if ($request->coupon_code) {
                        $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();
                        if ($coupon) {
                            if ($coupon->status == COUPON_INACTIVE) {
                                $coupon_reason = tr('coupon_inactive_reason');
                            } else {
                                $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();
                                if ($check_coupon->success) {
                                    $is_coupon_applied = COUPON_APPLIED;
                                    $amount_convertion = $coupon->amount;
                                    if ($coupon->amount_type == PERCENTAGE) {
                                        $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);
                                    }
                                    if ($amount_convertion < $subscription->amount) {
                                        $total = $subscription->amount - $amount_convertion;
                                        $coupon_amount = $amount_convertion;
                                    } else {
                                        // throw new Exception(Helper::get_error_message(156),156);
                                        $total = 0;
                                        $coupon_amount = $amount_convertion;
                                    }
                                    // Create user applied coupon
                                    if($check_coupon->code == 2002) {
                                        $user_coupon = UserCoupon::where('user_id', $user->id)->where('coupon_code', $request->coupon_code)->first();
                                        // If user coupon not exists, create a new row
                                        if ($user_coupon) {
                                            if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {
                                                $user_coupon->no_of_times_used += 1;
                                                $user_coupon->save();
                                            }
                                        }
                                    } else {
                                        $user_coupon = new UserCoupon;
                                        $user_coupon->user_id = $user->id;
                                        $user_coupon->coupon_code = $request->coupon_code;
                                        $user_coupon->no_of_times_used = 1;
                                        $user_coupon->save();
                                    }
                                } else {
                                    $coupon_reason = $check_coupon->error_messages;
                                }
                            }

                        } else {
                            $coupon_reason = tr('coupon_delete_reason');
                        }
                    } //coupn request end

                    if ($user) {
                        if ($total <= 0) {
                            $previous_payment = UserPayment::where('user_id' , $request->id)->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();
                            $user_payment = new UserPayment;
                            if($previous_payment) {
                                if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {
                                    $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($previous_payment->expiry_date)));
                                } else {
                                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));
                                }

                            } else {
                                $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                            }

                            $user_payment->payment_id = "free plan";
                            $user_payment->user_id = $request->id;
                            $user_payment->subscription_id = $request->subscription_id;
                            $user_payment->status = 1;
                            $user_payment->amount = $total;
                            $user_payment->payment_mode = CARD;
                            // Coupon details
                            $user_payment->is_coupon_applied = $is_coupon_applied;
                            $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';
                            $user_payment->coupon_amount = $coupon_amount;
                            $user_payment->subscription_amount = $subscription->amount;
                            $user_payment->amount = $total;
                            $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;
                            if ($user_payment->save()) {
                                if ($user) {
                                    $user->user_type = 1;
                                    $user->expiry_date = $user_payment->expiry_date;
                                    if ($user_payment->amount <= 0) {
                                        $user->zero_subscription_status = 1;
                                    } else {
                                        // Referral Commission
                                        // $referral_commission = Setting::get('referral_commission')/100;
                                        // $referral_amount = $user_payment->amount * $referral_commission;
                                        // $subscription_amount_after_referral_split = $user_payment->amount - $referral_amount;
                                        // $user_payment->referral_commission = $subscription_amount_after_referral_split ?: "";

                                        // $referral_commission2 = Setting::get('referral_commission2')/100;
                                        // $referral_amount2 = $user_payment->amount * $referral_commission2;
                                        // $subscription_amount_after_referral_split2 = $user_payment->amount - $referral_amount2;
                                        // $user_payment->referral_commission2 = $subscription_amount_after_referral_split2 ?: "";
                                        // Log::info("Amount ".$user_payment->amount);
                                        // $user_payment->save();
                                        // PaymentRepo::referral_amount_update($user_payment->user_id, $user_payment->referral_commission);
                                        // PaymentRepo::tier2_referral_amount_update($user_payment->user_id, $user_payment->referral_commission2);
                                    }
                                    if ($user->save()) {
                                        $data = ['id' => $user->id , 'token' => $user->token, 'payment_id' => $user_payment->payment_id];
                                        $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];
                                    } else {
                                        throw new Exception(tr('user_details_not_saved'));
                                    }
                                } else {
                                   throw new Exception(tr('user_not_found'));
                                }
                            } else {
                                throw new Exception(tr(Helper::get_error_message(902)), 902);
                            }
                        } else {//total end 
                            try{
                                $data =  $this->coinpayments_api_transaction('create_transaction',$total,$user->email);
                                // echo '<pre>'; print_r( $data );die;
                                $user_payment1 = UserPayment::where('user_id' , $request->id) ->where('status', DEFAULT_FALSE)->first();

                                if($user_payment1){
                                   $user_payment = UserPayment::find($user_payment1->id);   
                                }else{
                                    $user_payment = new UserPayment;
                                }

                                $user_payment->payment_mode = COIN;
                                $user_payment->is_coupon_applied = $is_coupon_applied;
                                $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';
                                $user_payment->coupon_amount = $coupon_amount;
                                $user_payment->subscription_amount = $subscription->amount;
                                $user_payment->amount = $total;
                                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;
                                $payment_id = $data['result']['txn_id'];
                                $user_payment->payment_id  = $payment_id;
                                $user_payment->user_id = $request->id;
                                $user_payment->subscription_id = $request->subscription_id;
                                // $user_payment->status = UNPAID;

                                if($user_payment->save()){
                                    $user_payment2 = UserPayment::find($user_payment->id);
                                }
                                DB::commit();
                                header("Location: ".$data['result']['checkout_url']."");die();
                                $data1 =  $this->coinpayments_api_status('get_tx_info',$data['result']['txn_id']);
                                
                            } catch(\Stripe\Error\RateLimit $e) { //try 5208 
                                    throw new Exception($e->getMessage(), 903);
                            } catch(\Stripe\Error\Card $e) {//catch 5347 
                                    throw new Exception($e->getMessage(), 903);
                            } catch (\Stripe\Error\InvalidRequest $e) {//catch 5351 
                                // Invalid parameters were supplied to Stripe's API
                                throw new Exception($e->getMessage(), 903);
                            } catch (\Stripe\Error\Authentication $e) {//catch 5355
                                // Authentication with Stripe's API failed
                                throw new Exception($e->getMessage(), 903);
                            } catch (\Stripe\Error\ApiConnection $e) { //catch 5359 
                                // Network communication with Stripe failed
                                throw new Exception($e->getMessage(), 903);
                            } catch (\Stripe\Error\Base $e) { //catch 5366 
                                // Display a very generic error to the user, and maybe send    
                                throw new Exception($e->getMessage(), 903);
                            } catch (Exception $e) { //catch 5372
                                // Something else happened, completely unrelated to Stripe
                                throw new Exception($e->getMessage(), 903);
                            } catch (\Stripe\StripeInvalidRequestError $e) {//catch 5377 
                                Log::info(print_r($e,true));
                                throw new Exception($e->getMessage(), 903);
                            }//catch 5382  

                        }

                        // } else {    exist card comment
     
                        //     throw new Exception(Helper::get_error_message(901), 901);
                            
                        // }//catch 5394  

                    } else {

                        throw new Exception(tr('no_user_detail_found'));
                        
                    }//catch 5400  else

                } else {

                    throw new Exception(Helper::get_error_message(901), 901);

                }//catch 5406   else     

                
            }

         
            return response()->json($response_array , 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }//catch 5419
    }
   function order_payment_status(){
     $pending_payment  = transaction::where('status', DEFAULT_FALSE)->where('gateway_type', 'coin')->get();
     $order = Order::where('user_id',Auth::id())->where('status',false)->first();
       if($pending_payment){
         foreach ($pending_payment as $key => $value) {
              $data1 =  $this->coinpayments_api_status('get_tx_info',$value->gateway_id);
          
              
               if($data1['result']){
            if ($data1['result']['status'] == 100){
             $transaction = $value;
             $transaction->status = true;
             $transaction->total_price = $data1['result']['checkout']['amountf'];
             $transaction->save();
              $order->status = true;
              $order->payment_status = true;
              $order->save();
                $products = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.token',"carts.quantity")->where('orders.id',$order->id)->where('type','seller_token')->get();
                   foreach($products as $ps){
                    for($i=1;$i<=$ps->quantity;$i++){
                       $token = new SellerToken;
                      $token->token = $ps->token;
                      $token->user_id = $user->id;
                      $token->save();  
                    }
                   
                  }
               DB::table('carts')->where('order_id',$order->id)->update(["status"=>true]);
             Log::info("order payment  status for order_id".$order->id);
             }
            }
          }
       }else{
         Log::info("order payment  no data found");
       }

      
   }
    ///check subscription payment status
function subsciption_update_payment_status(){
      // $data = array("name" => "a@yomail.com");

      //  Helper::send_email('emails.admin_subscription',"New Subscription","phpteam366@gmail.com",$data);
        
        $pending_payment  = UserPayment::where('status', DEFAULT_FALSE)->where('payment_mode', COIN)->get();
         if($pending_payment){
          foreach ($pending_payment as $key => $value) {
              $data1 =  $this->coinpayments_api_status('get_tx_info',$value->payment_id);
           if($data1['result']){
            if ($data1['result']['status'] == 100){
                 $id = $value->user_id;
                 $request = UserPayment::where('user_id' , $id)->where('status', DEFAULT_FALSE)->orderBy('id', 'desc')->first();
               
                $user_payment = UserPayment::find($request->id);
                $user = User::find($id);
                $subscription = Subscription::find($user_payment->subscription_id);
                  $user_token = new UserToken;
                 
                   if($subscription->gift_token){
               
                    $user_token->token = $subscription->gift_token;
                    $user_token->user_id = $id;
                    $token = $user_token->save();
                 }

      

                $user_payment->status = PAID_STATUS;

                $previous_payment = UserPayment::where('user_id' , $id)
                                    ->where('status', DEFAULT_TRUE)
                                    ->orderBy('created_at', 'desc')
                                    ->first();

                                                
                if($previous_payment) {
                    if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {
                        $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($previous_payment->expiry_date)));
                    } else {
                        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));
                    }

                } else {
                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                }
     }
    
     if($data1['result']['status']==-1){
       Log::info("user_payments id deleted".$value->id);
       DB::table('user_payments')->where('id', '>', $value->id)->delete();
        }
    }
      }

        if (isset($user)) {
            $user->user_type = SUBSCRIBED_USER;
            $user->expiry_date = $user_payment->expiry_date;
         
            if ($user_payment->amount <= 0) {
                $user->zero_subscription_status = 1;
            }  else {
                // Referral Commission
                $referral_commission = Setting::get('referral_commission')/100;
                $referral_commission2 = Setting::get('referral_commission2')/100;

                $subscription_amount_after_referral_split = $user_payment->amount * $referral_commission;
                $subscription_amount_after_referral_split2 = $user_payment->amount * $referral_commission2;

                $user_payment->referral_commission = $subscription_amount_after_referral_split ?: "";
                $user_payment->referral_commission2 = $subscription_amount_after_referral_split2 ?: "";

                Log::info("Amounts ".$user_payment->amount);
                      
                $user_payment->save();
               PaymentRepo::referral_token_update($user_payment->user_id);
                PaymentRepo::referral_amount_update($user_payment->user_id, $user_payment->referral_commission);
                PaymentRepo::tier2_referral_amount_update($user_payment->user_id, $user_payment->referral_commission2);
            }

            if ($user->save()) {


                // $message =' A cjc test.';
                // $hired_user_email= $user->email;
                // $content = "Congratulations!! Your subscription has been confirmed.";
                // Mail::send(array(), array(), function ($message) use ($content,$hired_user_email) 
                // {
                // $email =  $hired_user_email;
                // $from  = 'cjclivemail@gmail.com';

                // $message->to($email ,'invite')
                // ->from('cjclivemail@gmail.com')
                // ->subject('Subscription successfull')
                // ->setBody($content, 'text/html');
                // }); 

                Helper::send_email('emails.user_subscription',"Subscription Confirmed",$user->email,array("name"=>$user->name));
                Helper::send_email('emails.admin_subscription',"New Subscription",config('app.admin_email'),array("name"=>$user->email));
                 DB::commit();
           } else {


                throw new Exception(tr('user_details_not_saved'));
                
            }

        } 
        // else {

        //     throw new Exception(tr('user_not_found'));
            
        // }
    }
    }
       
    public function subscribe_channel(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'channel_id'     => 'required|exists:channels,id,status,'.DEFAULT_TRUE.',is_approved,'.DEFAULT_TRUE,
                ));


        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $model = ChannelSubscription::where('user_id', $request->id)
            ->where('channel_id',$request->channel_id)
            ->first();

            if (!$model) {

                $model = new ChannelSubscription;

                $model->user_id = $request->id;

                $model->channel_id = $request->channel_id;

                $model->status = DEFAULT_TRUE;

                $model->save();

                $channel_details = Channel::find($request->channel_id);

                // ADD POINTS TO THE OWNER USER start

                if($channel_details) {

                    $inputRequest = [];

                    $inputRequest['user_id'] = $request->id;

                    $inputRequest['owner_user_id'] = $channel_details->user_id;

                    $inputRequest['point_type'] = POINT_TYPE_SUBSCRIBE_VIDEO;

                    $inputRequest['channel_id'] = $request->channel_id;

                    $inputRequest['points'] = Setting::get('user_points') ?: 0;

                    Log::info("UserPointJob - Subscribe channel".print_r($inputRequest, true));

                    dispatch(new UserPointJob($inputRequest));   

                    $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $request->id; 

                    dispatch(new UserPointJob($inputRequest)); 

                }

                // ADD POINTS TO THE OWNER USER ends

                $response_array = ['success'=>true, 'message'=>tr('channel_subscribed')];

            } else {

                $response_array = ['success'=>false, 'message'=>tr('already_channel_subscribed')];

            }
        }

        return response()->json($response_array);
   
    }

    public function unsubscribe_channel(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'channel_id'     => 'required|exists:channels,id',
                ));


        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $model = ChannelSubscription::where('user_id', $request->id)->where('channel_id',$request->channel_id)->first();

            if ($model) {

                $model->delete();

                $response_array = ['success'=>true, 'message'=>tr('channel_unsubscribed')];

            } else {

                $response_array = ['success'=>false, 'message'=>tr('not_found')];

            }
        }

        return response()->json($response_array);

    }


    public function singleVideoResponse($request) {

        $data = [];

        $video_tape_details = VideoTape::where('video_tapes.id' , $request->video_tape_id)
                                    ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id') 
                                    ->where('video_tapes.status' , 1)
                                    ->where('video_tapes.publish_status' , 1)
                                    ->where('video_tapes.is_approved' , 1)
                                    ->videoResponse()
                                    ->first();
        if($video_tape_details) {

            $data = $video_tape_details->toArray();

            $data['wishlist_status'] = $data['history_status'] = $data['is_subscribed'] = $data['is_liked'] = $data['pay_per_view_status'] = $data['user_type'] = $data['flaggedVideo'] = 0;

            $data['comment_rating_status'] = 1;

            if($request->id) {

                $data['wishlist_status'] = Helper::check_wishlist_status($request->id,$request->video_tape_id) ? 1 : 0;

                $data['history_status'] = count(Helper::history_status($request->id,$request->video_tape_id)) > 0? 1 : 0;

                $data['is_subscribed'] = check_channel_status($request->id, $video_tape_details->channel_id);

                $data['is_liked'] = Helper::like_status($request->id,$request->video_tape_id);

                $mycomment = UserRating::where('user_id', $request->id)->where('video_tape_id', $request->video_tape_id)->first();

                if ($mycomment) {

                    $data['comment_rating_status'] = DEFAULT_FALSE;
                }

                $user_details = '';

                $is_ppv_status = DEFAULT_TRUE;

                if($user_details = User::find($request->id)) {

                    $data['user_type'] = $user_details->user_type;

                    $is_ppv_status = ($video_tape_details->type_of_user == NORMAL_USER || $video_tape_details->type_of_user == BOTH_USERS) ? ( ( $user_details->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                }

                $data['is_ppv_subscribe_page'] = $is_ppv_status;
                
                $data['pay_per_view_status'] = VideoRepo::pay_per_views_status_check($user_details ? $user_details->id : '', $user_details ? $user_details->user_type : '', $video_tape_details)->getData()->success;


            }

            $data['currency'] = Setting::get('currency');

            $data['subscriberscnt'] = subscriberscnt($video_tape_details->channel_id);

            $data['share_url'] = route('user.single' , $request->video_tape_id);

            $data['embed_link'] = route('embed_video', array('u_id'=>$video_tape_details->unique_id));

            $video_url = $video_tape_details->video;

            if($request->login_by == DEVICE_ANDROID) {

                $video_url = Setting::get('streaming_url') ? Setting::get('streaming_url').get_video_end($data['video']) : $video_url;

            }

            if($request->login_by == DEVICE_IOS) {

                $video_url = Setting::get('HLS_STREAMING_URL') ? Setting::get('HLS_STREAMING_URL').get_video_end($data['video']) : $video_url;

            }

            $data['video'] = $video_url;


        }

        // Comments Section

        $comments = [];

        if($comments = Helper::video_ratings($request->video_tape_id,0)) {

            $comments = $comments->toArray();

        }

        $data['comments'] = $comments;

        // $data['suggestions'] = VideoRepo::suggestions($request);
        
        return $data;
    }

    public function spam_videos_list(Request $request) {

        // Load Flag videos based on logged in user id
        $model = Flag::where('flags.user_id', $request->id)
            ->leftJoin('video_tapes' , 'flags.video_tape_id' , '=' , 'video_tapes.id')
            ->where('video_tapes.is_approved' , 1)
            ->where('video_tapes.status' , 1)
            ->get();

        $flag_video = [];

        foreach ($model as $key => $value) {

            $request->request->add(['video_tape_id'=>$value->video_tape_id, 'login_by'=>DEVICE_ANDROID]);
            
            $flag_video[] = $this->singleVideoResponse($request);

        }

        $response_array = ['success'=>true, 'data'=>$flag_video];
        

        return response()->json($response_array);

    }

    public function add_spam(Request $request) {

        $validator = Validator::make($request->all(), [
            'video_tape_id' => 'required|exists:video_tapes,id',
            'reason' => 'required',
        ]);
        // If validator Fails, redirect same with error values
        if ($validator->fails()) {
             //throw new Exception("error", tr('admin_published_video_failure'));

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json(['success'=>false , 'message'=>$error_messages]);
        }
        // Assign Post request values into Data variable
        $data = $request->all();

        // include user_id index into the data varaible  "Auth::user()->id" -> Logged In user id
        $data['user_id'] = $request->id;
        $data['video_id'] =$request->video_tape_id;

        $data['status'] = DEFAULT_TRUE;

        // Save the values in DB
        if (Flag::create($data)) {
            return response()->json(['success'=>true, 'message'=>tr('report_video_success_msg')]);
        } else {
            //throw new Exception("error", tr('admin_published_video_failure'));
            return response()->json(['success'=>true, 'message'=>tr('admin_published_video_failure')]);
        }
    }

    public function reasons() {

        $reasons = getReportVideoTypes();

        return response()->json(['success'=>true, 'data'=>$reasons]);
    }


    public function remove_spam(Request $request) {

        $validator = Validator::make($request->all(), [
            'video_tape_id' => $request->status ? '' : 'required|exists:video_tapes,id',
        ]);

        // If validator Fails, redirect same with error values

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array , 200);

            // COMMANDED BY VIDHYA. why we need message key in error response

            // return response()->json(['success'=>false , 'message'=>$error_messages]);
        }

        if ($request->status) {

            Flag::where('user_id', $request->id)->delete();

            return response()->json(['success'=>true, 'message'=>tr('unmark_report_video_success_msg')]);

        } else {
            
            // Load Spam Video from flag section
            $model = Flag::where('user_id', $request->id)
                ->where('video_tape_id', $request->video_tape_id)
                ->first();

            if ($model) {

                $model->delete();

                return response()->json(['success'=>true, 'message'=>tr('unmark_report_video_success_msg')]);
            } else {
                // throw new Exception("error", tr('admin_published_video_failure'));
                return response()->json(['success'=>true, 'message'=>tr('admin_published_video_failure')]);
            }

        }
    }


    /******************************** API's ******************************/


    public function pay_per_videos(Request $request) {

                // Load all the paper view videos based on logged in user id
        $model = PayPerView::where('pay_per_views.user_id', $request->id)
        ->select('pay_per_views.id as id', 'pay_per_views.video_id', 'pay_per_views.amount as pay_per_view_amount',
            'video_tapes.*', 'pay_per_views.created_at')
             ->leftJoin('video_tapes' ,'pay_per_views.video_id' , '=' , 'video_tapes.id')
            ->where('video_tapes.is_approved' , 1)
            ->where('video_tapes.status' , 1)
            ->where('pay_per_views.amount', '>', 0)
            ->where('video_tapes.age_limit','<=', checkAge($request))
            ->orderby('pay_per_views.created_at' , 'desc')
            ->paginate(16);

        $video = array('data' => $model->items(), 'pagination' => (string) $model->links());
      
        $items = [];

        foreach ($video['data'] as $key => $value) {

        
            $items[] = displayVideoDetails($value->videoTapeResponse, $request->id);

            $items[$key]['paid_amount'] = $value->pay_per_view_amount;


        }

        return response()->json(['items'=>$items, 'pagination'=>isset($model['pagination']) ? $model['pagination'] : 0]);
    }

    public function search_list($request,$key,$web = NULL,$skip = 0) {

        $base_query = VideoTape::where('video_tapes.is_approved' ,'=', 1)
                    ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                    ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                    ->where('title','like', '%'.$key.'%')
                    ->where('video_tapes.status' , 1)
                    ->where('video_tapes.publish_status' , 1)
                    ->videoResponse()
                    ->where('channels.is_approved', 1)
                    ->where('channels.status', 1)
                    ->where('video_tapes.age_limit','<=', checkAge($request))
                    ->where('categories.status', CATEGORY_APPROVE_STATUS)
                    ->orderBy('video_tapes.created_at' , 'desc');
        if($web) {

            $videos = $base_query->paginate(16);

            $model = array('data' => $videos->items(), 'pagination' => (string) $videos->links());


        } else {

            $videos = $base_query->skip($skip)->take(Setting::get('admin_take_count' ,12))->get();

            $model = ['data'=>$videos];

        }

        $items = [];

        foreach ($model['data'] as $key => $value) {
            
            $items[] = displayVideoDetails($value, $request->id);

        }

        return response()->json(['items'=>$items, 'pagination'=>isset($model['pagination']) ? $model['pagination'] : 0]);

    }


    /**
     * Function Name : search_channels_list
     *
     * @usage_place : WEB
     *
     * To list out all the channels which based on search
     *
     * @param Object $request - USer Details
     *
     * @return array of channel list
     */
    public function search_channels_list(Request $request) {

        $age = 0;

        $channel_id = [];

        $query = Channel::where('channels.is_approved', DEFAULT_TRUE)
                ->select('channels.*', 'video_tapes.id as video_tape_id', 'video_tapes.is_approved',
                    'video_tapes.status', 'video_tapes.channel_id')
                ->leftJoin('video_tapes', 'video_tapes.channel_id', '=', 'channels.id')
                ->where('channels.status', DEFAULT_TRUE)
                ->where('name','like', '%'.$request->key.'%')
                ->where('video_tapes.is_approved', DEFAULT_TRUE)
                ->where('video_tapes.status', DEFAULT_TRUE)
                ->groupBy('video_tapes.channel_id');

        if($request->id) {

            $user = User::find($request->id);

            $age = $user->age_limit;

            $age = $age ? ($age >= Setting::get('age_limit') ? 1 : 0) : 0;

            if ($request->has('channel_id')) {

                $query->whereIn('channels.id', $request->channel_id);
            }


            $query->where('video_tapes.age_limit','<=', $age);

        }

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $channels = $query->skip($request->skip)->take(Setting::get('admin_take_count', 12))->get();

        } else {

            $channels = $query->paginate(6);

        }

        $lists = [];

        $pagination = 0;

        if(count($channels) > 0) {

            foreach ($channels as $key => $value) {
                $lists[] = ['channel_id'=>$value->id, 
                        'user_id'=>$value->user_id,
                        'picture'=> $value->picture, 
                        'title'=>$value->name,
                        'description'=>$value->description, 
                        'created_at'=>$value->created_at->diffForHumans(),
                        'no_of_videos'=>videos_count($value->id),
                        'subscribe_status'=>$request->id ? check_channel_status($request->id, $value->id) : '',
                        'no_of_subscribers'=>$value->getChannelSubscribers()->count(),
                ];

            }

            if ($request->device_type != DEVICE_ANDROID && $request->device_type != DEVICE_IOS) {

                $pagination = (string) $channels->links();

            }

        }

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $response_array = ['success'=>true, 'data'=>$lists];

        } else {

            $response_array = ['success'=>true, 'channels'=>$lists, 'pagination'=>$pagination];

        }

        return response()->json($response_array);
    }


    /**
     * Function Name : stripe_ppv()
     * 
     * Pay the payment for Pay per view through stripe
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function stripe_ppv(Request $request) {

        try {

            DB::beginTransaction();

             $validator = Validator::make($request->all(), [
                'coupon_code' => 'exists:coupons,coupon_code,status,'.COUPON_ACTIVE,  
                'video_tape_id'=>'required|exists:video_tapes,id,publish_status,'.VIDEO_PUBLISHED.',is_approved,'.ADMIN_VIDEO_APPROVED_STATUS.',status,'.USER_VIDEO_APPROVED_STATUS          
            ], array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'video_id.exists' => tr('video_not_exists'),
                ));

            if($validator->fails()) {

                $errors = implode(',', $validator->messages()->all());
                
                $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

                throw new Exception($errors);

            } else {

                $userModel = User::find($request->id);

                if ($userModel) {

                    if ($userModel->card_id) {

                        $user_card = Card::find($userModel->card_id);

                        if ($user_card && $user_card->is_default) {

                            $video = VideoTape::find($request->video_tape_id);

                            if($video) {

                                $total = $video->ppv_amount;

                                $coupon_amount = 0;

                                $coupon_reason = '';

                                $is_coupon_applied = COUPON_NOT_APPLIED;

                                if ($request->coupon_code) {

                                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                                    if ($coupon) {
                                        
                                        if ($coupon->status == COUPON_INACTIVE) {

                                            $coupon_reason = tr('coupon_inactive_reason');

                                        } else {

                                            $check_coupon = $this->check_coupon_applicable_to_user($userModel, $coupon)->getData();

                                            if ($check_coupon->success) {

                                                $is_coupon_applied = COUPON_APPLIED;

                                                $amount_convertion = $coupon->amount;

                                                if ($coupon->amount_type == PERCENTAGE) {

                                                    $amount_convertion = amount_convertion($coupon->amount, $video->ppv_amount);

                                                }

                                                if ($amount_convertion < $video->ppv_amount  && $amount_convertion > 0) {

                                                    $total = $video->ppv_amount - $amount_convertion;

                                                    $coupon_amount = $amount_convertion;

                                                } else {

                                                    // throw new Exception(Helper::get_error_message(156),156);

                                                    $total = 0;

                                                    $coupon_amount = $amount_convertion;
                                                    
                                                }

                                                // Create user applied coupon

                                                if($check_coupon->code == 2002) {

                                                    $user_coupon = UserCoupon::where('user_id', $userModel->id)
                                                            ->where('coupon_code', $request->coupon_code)
                                                            ->first();

                                                    // If user coupon not exists, create a new row

                                                    if ($user_coupon) {

                                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                            $user_coupon->no_of_times_used += 1;

                                                            $user_coupon->save();

                                                        }

                                                    }

                                                } else {

                                                    $user_coupon = new UserCoupon;

                                                    $user_coupon->user_id = $userModel->id;

                                                    $user_coupon->coupon_code = $request->coupon_code;

                                                    $user_coupon->no_of_times_used = 1;

                                                    $user_coupon->save();

                                                }

                                            } else {

                                                $coupon_reason = $check_coupon->error_messages;
                                                
                                            }

                                        }

                                    } else {

                                        $coupon_reason = tr('coupon_delete_reason');
                                    }
                                
                                }

                                if ($total <= 0) {

                                    $user_payment = new PayPerView;

                                    $user_payment->payment_id = $is_coupon_applied ? 'COUPON-DISCOUNT' : FREE_PLAN;

                                    $user_payment->user_id = $request->id;
                                    $user_payment->video_id = $request->video_tape_id;

                                    $user_payment->status = PAID_STATUS;

                                    $user_payment->is_watched = NOT_YET_WATCHED;

                                    $user_payment->ppv_date = date('Y-m-d H:i:s');

                                    if ($video->type_of_user == NORMAL_USER) {

                                        $user_payment->type_of_user = tr('normal_users');

                                    } else if($video->type_of_user == PAID_USER) {

                                        $user_payment->type_of_user = tr('paid_users');

                                    } else if($video->type_of_user == BOTH_USERS) {

                                        $user_payment->type_of_user = tr('both_users');
                                    }


                                    if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                                        $user_payment->type_of_subscription = tr('one_time_payment');

                                    } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                                        $user_payment->type_of_subscription = tr('recurring_payment');

                                    }

                                    $user_payment->payment_mode = CARD;

                                    // Coupon details

                                    $user_payment->is_coupon_applied = $is_coupon_applied;

                                    $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                    $user_payment->coupon_amount = $coupon_amount;

                                    $user_payment->ppv_amount = $video->ppv_amount;

                                    $user_payment->amount = $total;

                                    $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                                    $user_payment->save();

                                    // Commission Spilit 

                                    if($video->amount > 0) { 

                                        // Do Commission spilit  and redeems for moderator

                                        Log::info("ppv_commission_spilit started");

                                        UserRepo::ppv_commission_split($video->id , $user_payment->id , "");

                                        Log::info("ppv_commission_spilit END"); 
                                        
                                    }

                                    \Log::info("ADD History - add_to_redeem");

                                    $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $user_payment->payment_id];

                                    $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                } else {

                                    // Get the key from settings table

                                    $stripe_secret_key = Setting::get('stripe_secret_key');

                                    $customer_id = $user_card->customer_id;
                                    
                                    if($stripe_secret_key) {

                                        \Stripe\Stripe::setApiKey($stripe_secret_key);

                                    } else {

                                        $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                                        throw new Exception(Helper::get_error_message(902));
                                        
                                    }

                                    try {

                                       $user_charge =  \Stripe\Charge::create(array(
                                          "amount" => $total * 100,
                                          "currency" => "usd",
                                          "customer" => $customer_id,
                                        ));

                                       $payment_id = $user_charge->id;
                                       $amount = $user_charge->amount/100;
                                       $paid_status = $user_charge->paid;
                                       
                                       if($paid_status) {

                                            $user_payment = new PayPerView;
                                            $user_payment->payment_id  = $payment_id;
                                            $user_payment->user_id = $request->id;
                                            $user_payment->video_id = $request->video_tape_id;
                                            $user_payment->payment_mode = CARD;
                                        

                                            $user_payment->status = PAID_STATUS;

                                            $user_payment->is_watched = NOT_YET_WATCHED;

                                            $user_payment->ppv_date = date('Y-m-d H:i:s');

                                            if ($video->type_of_user == NORMAL_USER) {

                                                $user_payment->type_of_user = tr('normal_users');

                                            } else if($video->type_of_user == PAID_USER) {

                                                $user_payment->type_of_user = tr('paid_users');

                                            } else if($video->type_of_user == BOTH_USERS) {

                                                $user_payment->type_of_user = tr('both_users');
                                            }


                                            if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                                                $user_payment->type_of_subscription = tr('one_time_payment');

                                            } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                                                $user_payment->type_of_subscription = tr('recurring_payment');

                                            }

                                            // Coupon details

                                            $user_payment->is_coupon_applied = $is_coupon_applied;

                                            $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                            $user_payment->coupon_amount = $coupon_amount;

                                            $user_payment->ppv_amount = $video->ppv_amount;

                                            $user_payment->amount = $total;

                                            $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;
                                                                  
                                            $user_payment->save();

                                            // Commission Spilit 

                                            if($video->ppv_amount > 0) { 

                                                // Do Commission spilit  and redeems for moderator

                                                Log::info("ppv_commission_spilit started");

                                                PaymentRepo::ppv_commission_split($video->id , $user_payment->id , "");

                                                Log::info("ppv_commission_spilit END");
                                                
                                            }

                                        
                                            $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $payment_id];

                                            $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                        } else {

                                            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                                            throw new Exception(tr('no_vod_video_found'));

                                        }
                                    
                                    } catch(\Stripe\Error\RateLimit $e) {

                                        throw new Exception($e->getMessage(), 903);

                                    } catch(\Stripe\Error\Card $e) {

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\InvalidRequest $e) {
                                        // Invalid parameters were supplied to Stripe's API

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\Authentication $e) {

                                        // Authentication with Stripe's API failed


                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\ApiConnection $e) {

                                        // Network communication with Stripe failed

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\Error\Base $e) {
                                      // Display a very generic error to the user, and maybe send
                                        
                                        throw new Exception($e->getMessage(), 903);

                                    } catch (Exception $e) {
                                        // Something else happened, completely unrelated to Stripe

                                        throw new Exception($e->getMessage(), 903);

                                    } catch (\Stripe\StripeInvalidRequestError $e) {

                                            Log::info(print_r($e,true));

                                        throw new Exception($e->getMessage(), 903);
                                        
                                    
                                    }


                                }

                            
                            } else {

                                $response_array = array('success' => false , 'error_messages' => tr('no_video_found'));

                                throw new Exception(tr('no_video_found'));
                                
                            }

                        } else {

                        
                            throw new Exception(tr('no_default_card_available'), 901);

                        }

                    } else {


                        throw new Exception(tr('no_default_card_available'), 901);

                    }

                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    

                }

            }

            DB::commit();

            return response()->json($response_array,200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$code];

            return response()->json($response_array);

        }
        
    }


    /**
     * Function Name :  wishlist_list()
     * 
     * @usage_place : WEB
     * 
     * List of wishlist based on the logged in user
     *
     * @param object $request - User Details
     * 
     * @return response of wishlist
     */
    public function wishlist_list($request) {

        $base_query = Wishlist::where('wishlists.user_id' , $request->id)
                            ->leftJoin('video_tapes' ,'wishlists.video_tape_id' , '=' , 'video_tapes.id')
                            ->leftJoin('channels' ,'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                            ->where('video_tapes.is_approved' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('wishlists.status' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->select(
                                    'wishlists.id as wishlist_id',
                                    'video_tapes.id as video_tape_id' ,
                                    'video_tapes.title',
                                    'video_tapes.description' ,
                                    'video_tapes.ppv_amount',
                                    'channels.status as channel_status',
                                    'video_tapes.amount',
                                    'default_image',
                                    'video_tapes.watch_count',
                                    'video_tapes.ratings',
                                    'video_tapes.duration',
                                    'video_tapes.channel_id',
                                    'video_tapes.type_of_user',
                                    'channels.user_id as channel_created_by',
                                    'video_tapes.ad_status',
                                    DB::raw('DATE_FORMAT(video_tapes.publish_time , "%e %b %y") as publish_time') , 
                                    'channels.name as channel_name', 
                                    'video_tapes.type_of_subscription',
                                    'wishlists.created_at')
                            ->where('video_tapes.age_limit','<=', checkAge($request))
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->orderby('wishlists.created_at' , 'desc');

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }
        
        }

        $videos = $base_query->paginate(16);

        $items = [];

        $pagination = 0;

        if (count($videos) > 0) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination = (string) $videos->links();

        }

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }


    /**
     * Function Name : watch_list()
     * 
     * @usage_place : WEB
     *
     * User History - User watched videos display here
     *
     * @param Object $request - User Details
     *
     * @return response of videos list
     */
    public function watch_list($request) {

        $base_query = UserHistory::where('user_histories.user_id' , $request->id)
                            ->leftJoin('video_tapes' ,'user_histories.video_tape_id' , '=' , 'video_tapes.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->leftJoin('channels' ,'video_tapes.channel_id' , '=' , 'channels.id')
                            ->where('video_tapes.is_approved' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->select('user_histories.id as history_id',
                                    'video_tapes.id as video_tape_id' ,
                                    'video_tapes.title',
                                    'video_tapes.description' , 
                                    'video_tapes.duration',
                                    'default_image',
                                    'channels.status as channel_status',
                                    'video_tapes.watch_count',
                                    'video_tapes.ratings',
                                    'video_tapes.ppv_amount', 
                                    'video_tapes.amount',
                                    'video_tapes.type_of_user',
                                    'video_tapes.type_of_subscription',
                                    'channels.user_id as channel_created_by',
                                    DB::raw('DATE_FORMAT(video_tapes.publish_time , "%e %b %y") as publish_time'), 
                                    'video_tapes.channel_id',
                                    'channels.name as channel_name', 
                                    'user_histories.created_at')
                            ->where('video_tapes.age_limit','<=', checkAge($request))
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->orderby('user_histories.created_at' , 'desc');
        
        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }
        
        }


        $videos = $base_query->paginate(16);

        $model = array('data' => $videos->items(), 'pagination' => (string) $videos->links());

        $items = [];

        foreach ($model['data'] as $key => $value) {
            
            $items[] = displayVideoDetails($value, $request->id);

        }

        return response()->json(['items'=>$items, 'pagination'=>isset($model['pagination']) ? $model['pagination'] : 0]);

    }


    /**
     * Function Name : recently_added()
     *
     * @usage_place : WEB
     *
     * Displayed recently added videos by user/admin , the video displayed based on created date
     *
     * @param object $request - User Details
     *
     * @return list of videos
     */
    public function recently_added($request) {

        $base_query = VideoTape::where('video_tapes.is_approved' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.publish_status' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->orderby('video_tapes.created_at' , 'desc')
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->videoResponse();

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit', '=' , 0);
        }

    
        $videos = $base_query->paginate(100);

        $items = [];

        $pagination = 0;

        if (count($videos)) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination  = (string) $videos->links();

        }


        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }


    /**
     * Function Name : trending_list()
     *
     * @usage_place : WEB
     *
     * To display based on watch count, no of users seen videos
     *
     * @param object $request - User Details
     *
     * @return Response of videos list
     */
    public function trending_list($request) {

        $base_query = VideoTape::where('watch_count' , '>' , 0)
                        ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                        ->where('video_tapes.publish_status' , 1)
                        ->where('video_tapes.status' , 1)
                        ->where('video_tapes.is_approved' , 1)
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                        ->where('categories.status', CATEGORY_APPROVE_STATUS)
                        ->videoResponse()
                        
                        ->orderby('watch_count' , 'desc');

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {
                
                $base_query->whereNotIn('video_tapes.id',$flag_videos);
            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit','=', 0);
        }

        $videos = $base_query->paginate(16);

        $items = [];

        $pagination = 0;

        if (count($videos) > 0) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination = (string) $videos->links();

        }

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }
 public function training_list($request) {

        $base_query = VideoTape::where('video_tapes.is_approved' , 1)->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                        ->where('video_tapes.publish_status' , 1)
                        ->where('video_tapes.status' , 1)
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                        ->where('categories.status', CATEGORY_APPROVE_STATUS)
                        ->where('categories.unique_id','training')->where('video_tapes.uploaded_by','admin')
                        ->videoResponse()
                        
                        ->orderby('watch_count' , 'desc');

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {
                
                $base_query->whereNotIn('video_tapes.id',$flag_videos);
            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit','=', 0);
        }

        $videos = $base_query->paginate(16);

        $items = [];

        $pagination = 0;

        if (count($videos) > 0) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination = (string) $videos->links();

        }

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }


    /**
     * Function Name : suggestion_videos()
     *
     * @usage_place : WEB
     *
     * To get suggestion video to see the user
     *
     * @param object $request - User Details
     *
     * @return response of array videos
     */ 
    public function suggestion_videos($request) {

        $base_query = VideoTape::where('video_tapes.is_approved' , 1)   
                            ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.publish_status' , 1)
                            ->orderby('video_tapes.created_at' , 'desc')
                            ->videoResponse()
                            ->where('channels.is_approved', 1)
                            ->where('channels.status', 1)
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->orderByRaw('RAND()');

        if($request->video_tape_id) {

            $base_query->whereNotIn('video_tapes.id', [$request->video_tape_id]);
        }

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);
            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit','=', 0);
        }

    
        $videos = $base_query->paginate(16);
        
        $items = [];

        $pagination = 0;

        if (count($videos) > 0) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination = (string) $videos->links();

        }

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }


    public function sponsored_videos($request) {

       $base_query = VideoTape::where('video_tapes.is_approved' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.publish_status' , 1)
                            ->where('video_tapes.isSponsored' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                            ->orderby('video_tapes.created_at' , 'desc')
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->videoResponse();

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {

                $base_query->whereNotIn('video_tapes.id',$flag_videos);

            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit', '=' , 0);
        }

    
        $videos = $base_query->paginate(16);

        $items = [];

        $pagination = 0;

        if (count($videos)) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination  = (string) $videos->links();

        }

        if(count($items) > 0) {
        foreach ($items as $key => $item) {
            $request->video_tape_id = $item['video_tape_id'];
            $data = $this->video_detail($request)->getData();
            if($data && @$data->response_array) {
                $items[$key]['videoPath'] = $data->response_array->videoPath;
                $items[$key]['videoStreamUrl'] = $data->response_array->videoStreamUrl;
                $items[$key]['hls_video'] = $data->response_array->hls_video;
            } else {
                $items[$key]['videoPath'] = "";
                $items[$key]['videoStreamUrl'] = "";
                $items[$key]['hls_video'] = "";
            }
        }
      }
//print_r($items); die;

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }


    /**
     * Function Name : channel_list
     *
     * @usage_place : WEB
     *
     * To list out all the channels which is in active status
     *
     * @param Object $request - USer Details
     *
     * @return array of channel list
     */
    public function channel_list(Request $request) {

        $age = 0;

        $channel_id = [];

        $query = Channel::where('channels.is_approved', DEFAULT_TRUE)
                ->select('channels.*', 'video_tapes.id as video_tape_id', 'video_tapes.is_approved',
                    'video_tapes.status', 'video_tapes.channel_id')
                ->leftJoin('video_tapes', 'video_tapes.channel_id', '=', 'channels.id')
                ->where('channels.status', DEFAULT_TRUE)
                ->where('video_tapes.is_approved', DEFAULT_TRUE)
                ->where('video_tapes.status', DEFAULT_TRUE)
                ->groupBy('video_tapes.channel_id');

        if($request->id) {

            $user = User::find($request->id);

            $age = $user->age_limit;

            $age = $age ? ($age >= Setting::get('age_limit') ? 1 : 0) : 0;

            if ($request->has('channel_id')) {

                $query->whereIn('channels.id', $request->channel_id);
            }


            $query->where('video_tapes.age_limit','<=', $age);

        }

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $channels = $query->skip($request->skip)->take(Setting::get('admin_take_count', 12))->get();

        } else {

            $channels = $query->paginate(16);

        }

        $lists = [];

        $pagination = 0;

        if(count($channels) > 0) {

            foreach ($channels as $key => $value) {
                $lists[] = ['channel_id'=>$value->id, 
                        'user_id'=>$value->user_id,
                        'picture'=> $value->picture, 
                        'title'=>$value->name,
                        'description'=>$value->description, 
                        'created_at'=>$value->created_at->diffForHumans(),
                        'no_of_videos'=>videos_count($value->id),
                        'subscribe_status'=>$request->id ? check_channel_status($request->id, $value->id) : '',
                        'no_of_subscribers'=>$value->getChannelSubscribers()->count(),
                ];

            }

            if ($request->device_type != DEVICE_ANDROID && $request->device_type != DEVICE_IOS) {

                $pagination = (string) $channels->links();

            }

        }

        if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

            $response_array = ['success'=>true, 'data'=>$lists];

        } else {

            $response_array = ['success'=>true, 'channels'=>$lists, 'pagination'=>$pagination];

        }

        return response()->json($response_array);
    }


    /**
     * Function Name : channel_list
     *
     * @usage_place : MOBILE
     *
     * To list out all the channels which is subscribed the logged in user
     *
     * @param Object $request - Subscribed plan Details
     *
     * @return array of channel subscribed plans
     */
    public function subscribed_channels(Request $request) {

        $validator = Validator::make($request->all(), 
                array(
                    'skip' => 'required',
                ));

        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());
            
            $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];


        } else {

            if ($request->id) {

                $channel_id = ChannelSubscription::where('user_id', $request->id)->pluck('channel_id')->toArray();

                $request->request->add([ 
                    'channel_id' => $channel_id,
                ]);        
            }

            $response_array = $this->channel_list($request)->getData();


        }

        return response()->json($response_array);

    }
    /**
     * Function Name : channel_videos()
     *
     * @usage_place : WEB
     *
     * To list out all the videos based on the channel id
     *
     * @param integer $channel_id - Channel Id
     * 
     * @return list out all the videos, and status of the subscribers
     */
    public function channel_videos($channel_id, $skip , $request = null) {

        $videos_query = VideoTape::leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                    ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                    ->where('video_tapes.channel_id' , $channel_id)
                    ->videoResponse()
                    ->orderby('video_tapes.created_at' , 'desc');

        $u_id = $request->id;

        $channel = Channel::find($channel_id);

        if ($channel) {

            if ($u_id == $channel->user_id) {

                if ($u_id) {

                    $videos_query->where('video_tapes.age_limit','<=', checkAge($request)); 
                }

            } else {


                $videos_query->where('video_tapes.status' , USER_VIDEO_APPROVED_STATUS)
                    ->where('video_tapes.is_approved', ADMIN_VIDEO_APPROVED_STATUS)
                        ->where('video_tapes.publish_status' , 1)   
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->where('categories.status', CATEGORY_APPROVE_STATUS);

            }

        } else {

            $videos_query->where('video_tapes.status' , USER_VIDEO_APPROVED_STATUS)
                ->where('video_tapes.is_approved', ADMIN_VIDEO_APPROVED_STATUS)
                        ->where('video_tapes.publish_status' , 1)
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->where('categories.status', CATEGORY_APPROVE_STATUS);
            
        }

        if ($u_id) {

            // Check any flagged videos are present
            $flagVideos = getFlagVideos($u_id);

            if($flagVideos) {

                $videos_query->whereNotIn('video_tapes.id', $flagVideos);

            }

        }

        if ($skip >= 0) {

            //Setting::get('admin_take_count', 12)
            $videos = $videos_query->skip($skip)->take(Setting::get('admin_take_count', 12))->get();

        } else {

            $videos = $videos_query->paginate(16);
        }


        $items = [];

        if (count($videos) > 0) { 

            foreach ($videos as $key => $value) {
                
                $items[] = displayVideoDetails($value, $u_id);

            }

        }

        return response()->json($items);

    }

    /**
     * Function Name : channel_trending()
     *
     * @usage_place : WEB
     *
     * To list out channel trending videos 
     *
     * @param integer $id - Channel Id
     *
     * @return channel videos
     */
    public function channel_trending($id, $count = 5 , $channel_owner_id = "" , $request) {

        $items = [];

        if(!$id) {

            return response()->json($items , 200);

        }

        $base_query = VideoTape::where('watch_count' , '>' , 0)
                        ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                        ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                        ->videoResponse()
                        ->where('channel_id', $id)
                        ->orderby('watch_count' , 'desc');

        if(!$channel_owner_id) {

            $base_query = $base_query->where('video_tapes.status' , 1)
                        ->where('video_tapes.is_approved' , 1)
                        ->where('video_tapes.publish_status' , 1)
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->where('categories.status', CATEGORY_APPROVE_STATUS);

        }

        $u_id = "";

        if (Auth::check()) {

            // Check Age Limit 

            // Check any flagged videos are present

            $u_id = Auth::user()->id;
                            
            $base_query->where('video_tapes.age_limit','<=', checkAge($request));

            $flag_videos = flag_videos($u_id);

            if($flag_videos) {
                
                $base_query->whereNotIn('video_tapes.id',$flag_videos);
            }
        }

        if($count > 0){

            $videos = $base_query->skip(0)->take($count)->get();

        } else {

            $videos = $base_query->paginate(16);
            
        }


        if (count($videos) > 0) { 

            foreach ($videos as $key => $value) {
                
                $items[] = displayVideoDetails($value, $u_id);

            }

        }

        return response()->json($items);

    }

    /**
     * Function Name : payment_videos()
     *
     * @usage_place : WEB
     *
     * To list out payment videos 
     *
     * @param integer $id - Channel Id
     *
     * @return channel videos
     */
    public function payment_videos($id, $skip) {

        $u_id = Auth::check() ? Auth::user()->id : '';    

        $base_query = VideoTape::leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                        ->videoResponse()
                        ->orderby('amount' , 'desc')
                        ->whereRaw("channel_id = '{$id}' and channels.user_id = '{$u_id}' and (user_ppv_amount > 0 or amount > 0)");

        if($skip >= 0) {

            $videos = $base_query->skip($skip)->take(Setting::get('admin_take_count' ,12))->get();

        } else {

            $videos = $base_query->paginate(16);
            
        }

        $items = [];

        $billing_amt = 0;

        foreach ($videos as $key => $value) {

            $items[] = $video_detail = displayVideoDetails($value, $u_id);

            $billing_amt += ($video_detail['user_ppv_amount'] + $video_detail['amount']);
        }

        return response()->json(['data'=>$items, 'count'=>count($items),  'billing_amt'=>$billing_amt]);

    
    }

    /**
     * Function Name : single_video()
     *
     * @usage_place : WEB
     * 
     * To view single video based on video id
     *
     * @param integer $request - Video id
     *
     * @return based on video displayed all the details'
     */
    public function video_detail(Request $request) {

        $video = VideoTape::where('video_tapes.id' , $request->video_tape_id)
                    ->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                    ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id')
                    ->videoResponse()
                    // ->where('video_tapes.status' , 1)
                    // ->where('video_tapes.is_approved' , 1)
                    // ->where('video_tapes.publish_status' , 1)
                    // ->where('channels.is_approved', 1)
                    // ->where('channels.status', 1)
                    ->first();
                  
                   
        if ($video) {

            if ($request->id != $video->channel_created_by) {

                // Check the channel is private or public start

                $user_channel_subscriptions = UserSubscription::where('channel_id', $video->channel_id)->where('status', APPROVED)->count();

                Log::info("user_channel_subscriptions".$user_channel_subscriptions);

                if($user_channel_subscriptions != 0) {

                    // Check the user subscribed for this channel 

                    $user_channel_subscription_payments  = UserSubscriptionPayment::where('user_id', $request->id)
                                                            ->where('channel_id', $video->channel_id)
                                                            ->where('status', PAID_STATUS)
                                                            ->count();

                    if($user_channel_subscription_payments <= 0) {

                        $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(5001) , 'error_code' => 5001];

                        return response()->json($response_array, 200);

                    }

                }

                // Channel / video is declined by admin /user

                if($video->is_approved == ADMIN_VIDEO_DECLINED_STATUS || $video->status == USER_VIDEO_DECLINED_STATUS || $video->channel_approved_status == ADMIN_CHANNEL_DECLINED_STATUS || $video->channel_status == USER_CHANNEL_DECLINED_STATUS) {

                    return response()->json(['success'=>false, 'error_messages'=>tr('video_is_declined')]);

                }

                // Video if not published

                if ($video->publish_status != PUBLISH_NOW) {

                    return response()->json(['success'=>false, 'error_messages'=>tr('video_not_yet_publish')]);
                }

                if ($video->getCategory) {

                    if ($video->getCategory->status == CATEGORY_DECLINE_STATUS) {

                        return response()->json(['success'=>false, 'error_messages'=>tr('category_declined_by_admin')]);

                    } 
                }

            }

            if (Setting::get('is_payper_view')) {

                if ($request->id != $video->channel_created_by) {

                    $user = User::find($request->id);

                    if ($video->ppv_amount > 0) {

                        $ppv_status = $user ? VideoRepo::pay_per_views_status_check($user->id, $user->user_type, $video)->getData()->success : false;


                        if ($ppv_status) {
                            

                        } else {

                            if ($request->id) {

                                if ($user->user_type) {        
                                    
                                    return response()->json(['url'=>route('user.subscription.ppv_invoice', $video->video_tape_id)]);

                                } else {

                                    return response()->json(['url'=>route('user.subscription.pay_per_view', $video->video_tape_id)]);
                                }

                            } else {

                                return response()->json(['url'=>route('user.subscription.pay_per_view', $video->video_tape_id)]);

                            }

                      
                        }

                    }

                }

            } 

            if($request->id) {

                if ($video->getChannel->user_id != $request->id) {

                    $age = $request->age_limit ? ($request->age_limit >= Setting::get('age_limit') ? 1 : 0) : 0;

                    if ($video->age_limit > $age) {

                        return response()->json(['success'=>false, 'error_messages'=>tr('age_error')]);

                    }

                } 
            } else {

                if ($video->age_limit == 1) {

                    return response()->json(['success'=>false, 'error_messages'=>tr('age_error')]);

                }

            }

            if($comments = Helper::video_ratings($request->video_tape_id,0)) {
                $comments = $comments->toArray();
            }

            $ads = $video->getScopeVideoAds ? ($video->getScopeVideoAds->status ? $video->getScopeVideoAds  : '') : '';

            $channels = [];

            $suggestions = $this->suggestion_videos($request,'', '', $request->video_tape_id)->getData();

            $wishlist_status = $history_status = WISHLIST_EMPTY;

            $report_video = getReportVideoTypes();

             // Load the user flag

            $flaggedVideo = ($request->id) ? Flag::where('video_tape_id',$request->video_tape_id)->where('user_id', $request->id)->first() : '';

            $videoPath = $video_pixels = $videoStreamUrl = '';

            $hls_video = "";

            $main_video = $video->video; 

            if ($video->video_type == VIDEO_TYPE_UPLOAD) {

                if ($video->publish_status == 1) {

                    $hls_video = Helper::convert_hls_to_secure(get_video_end($video->video) , $video->video);


                    if (\Setting::get('streaming_url')) {

                        if ($video->is_approved == 1) {

                            if ($video->video_resolutions) {

                                $videoStreamUrl = Helper::web_url().'/uploads/smil/'.get_video_end_smil($video->video).'.smil';

                                \Log::info("video Stream url".$videoStreamUrl);

                                \Log::info("Empty Stream url".empty($videoStreamUrl));

                                \Log::info("File Exists Stream url".!file_exists($videoStreamUrl));

                                if(empty($videoStreamUrl) || !file_exists($videoStreamUrl)) {

                                    $videos = $video->video_path ? $video->video.','.$video->video_path : $video->video;

                                    $video_pixels = $video->video_resolutions ? 'original,'.$video->video_resolutions : 'original';

                                    $videoPath = [];

                                    $videos = $videos ? explode(',', $videos) : [];

                                    $video_pixels = $video_pixels ? explode(',', $video_pixels) : [];

                                    foreach ($videos as $key => $value) {

                                        $videoPath[] = ['file' => Helper::convert_rtmp_to_secure(get_video_end($value) , $value), 'label' => $video_pixels[$key]];

                                    }

                                    $videoPath = json_decode(json_encode($videoPath));

                                }

                            } else {
     
                                $videoStreamUrl = Helper::convert_rtmp_to_secure(get_video_end($video->video) , $video->video);

                            }
                        }

                    } else {

                        $videos = $video->video_path ? $video->video.','.$video->video_path : [$video->video];

                        $video_pixels = $video->video_resolutions ? 'original,'.$video->video_resolutions : ['original'];

                        $videoPath = [];

                        Log::info("VIDEOS LIST".print_r($videos , true));

                        if(count($videos) > 0) {

                            $videos = is_array($videos) ? $videos : explode(',', $videos);

                            $video_pixels = is_array($video_pixels) ? $video_pixels : explode(',', $video_pixels);


                            foreach ($videos as $key => $value) {

                                $videoPathData = ['file' => Helper::convert_rtmp_to_secure(get_video_end($value) , $value), 'label' => isset($video_pixels[$key]) ? $video_pixels[$key] : "HD"];


                                $videoPath[] = $videoPathData;
                           
                            }
                        }

                        $videoPath =  json_decode(json_encode($videoPath));
                        
                    }

                } else {

                    $videoStreamUrl = $video->video;

                    $hls_video = $video->video;
                }

            } else {

                $videoStreamUrl = $video->video;

                $hls_video = $video->video;

            }

            $subscribe_status = DEFAULT_FALSE;

            $comment_rating_status = DEFAULT_TRUE;

            if($request->id) {

                $wishlist_status = $request->id ? Helper::check_wishlist_status($request->id,$request->video_tape_id): 0;

                $history_status = Helper::history_status($request->id,$request->video_tape_id);

                $subscribe_status = check_channel_status($request->id, $video->channel_id);

                $mycomment = UserRating::where('user_id', $request->id)->where('rating', '>', 0)->where('video_tape_id', $request->video_tape_id)->first();

                if ($mycomment) {

                    $comment_rating_status = DEFAULT_FALSE;
                }

            }

            $share_link = route('user.single' , $request->video_tape_id);

            $like_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('video_tape_id', $request->video_tape_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            $subscriberscnt = subscriberscnt($video->channel_id);

            $embed_link  = "<iframe width='560' height='315' src='".route('embed_video', array('u_id'=>$video->unique_id))."' frameborder='0' allowfullscreen></iframe>";

            $tags = VideoTapeTag::select('tag_id', 'tags.name as tag_name')
                ->leftJoin('tags', 'tags.id', '=', 'video_tape_tags.tag_id')
                ->where('video_tape_id', $request->video_tape_id)
                ->where('video_tape_tags.status', TAG_APPROVE_STATUS)
                ->get()->toArray();

            $category = Category::find($video->category_id);

            $video['category_unique_id'] = $category ? $category->unique_id : '';
              $tipcount =  UserPoint::where('video_tape_id',$request->video_tape_id)->where('point_type',POINT_TYPE_TIP_ME)->count();
            $response_array = [
                'tags'=>$tags,
                'video'=>$video, 'comments'=>$comments, 
                'channels' => $channels, 'suggestions'=>$suggestions,
                'wishlist_status'=> $wishlist_status, 'history_status' => $history_status, 'main_video'=>$main_video,
                'report_video'=>$report_video, 'flaggedVideo'=>$flaggedVideo , 'videoPath'=>$videoPath,
                'video_pixels'=>$video_pixels, 'videoStreamUrl'=>$videoStreamUrl, 'hls_video'=>$hls_video,
                'like_count'=>$like_count,'dislike_count'=>$dislike_count,'tip_count'=>$tipcount,
                'ads'=>$ads, 'subscribe_status'=>$subscribe_status,
                'subscriberscnt'=>$subscriberscnt,
                'comment_rating_status'=>$comment_rating_status,
                'embed_link'       => $embed_link,
                'banner_title'      =>$video->banner_title,
                'banner_description' =>$video->banner_description,

                ];

            return response()->json(['success'=>true, 'response_array'=>$response_array], 200);

        } else {

            return response()->json(['success'=>false, 'error_messages'=>tr('video_not_found')]);
        }

    }

    /**
     * Function Name : create_channel()
     *
     * To create a channel based on the logged in user
     *
     * @param object $request - User id, token
     *
     * @return success/failure message of boolean 
     */ 
    public function create_channel(Request $request) {

        $channels = getChannels($request->id);

        $user = User::find($request->id);

        if((count($channels) == 0 || Setting::get('multi_channel_status'))) {

            if ($user->user_type) {

                $response = CommonRepo::channel_save($request)->getData();

                if($response->success) {

                    $response_array = ['success'=>true, 'data'=>$response->data, 'message'=>$response->message];
                   
                } else {
                    
                    $response_array = ['success'=>false, 'error_messages'=>$response->error_messages];

                }

            } else {

                $response_array = ['success'=>false,'error_messages'=>Helper::get_error_message(169), 'error_code'=>169];

            }

        } else {

            $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(168), 'error_code'=>168];
        }

        return response()->json($response_array);

    }


    /**
     * Function Name : channel_edit()
     *
     * To edit a channel based on logged in user id (Form Rendering)
     *
     * @param integer $id - Channel Id
     *
     * @return respnse with Html Page
     */
    public function channel_edit(Request $request) {

        $validator = Validator::make( $request->all(), array(
                            'channel_id' => 'required|exists:channels,id',
                        ));

         if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = ['success'=> false, 'error_messages'=>$error_messages];

                // return back()->with('flash_errors', $error_messages);

        } else {

            $channel = Channel::where('user_id', $request->id)->where('id', $request->channel_id)->first();

            if ($channel) {

                $response = CommonRepo::channel_save($request)->getData();

                if($response->success) {

                    $response_array = ['success'=>true, 'data'=>$response->data, 'message'=>$response->message];
                   
                } else {
                    
                    $response_array = ['success'=>false, 'error_messages'=>$response->error_messages];

                }

            } else {

                $response_array = ['success'=>false, 'error_messages'=>tr('not_your_channel')];

            }

        }
        return response()->json($response_array);

    }

    /**
     * Function Name : channel_delete()
     *
     * To delete a channel based on logged in user id & channel id (Form Rendering)
     *
     * @param integer $request - Channel Id
     *
     * @return response with flash message
     */
    public function channel_delete(Request $request) {


        $validator = Validator::make( $request->all(), array(
                            'channel_id' => 'required|exists:channels,id',
                        ));

        if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = ['success'=> false, 'error_messages'=>$error_messages];

                // return back()->with('flash_errors', $error_messages);

        } else {

            $channel = Channel::where('user_id', $request->id)->where('id', $request->channel_id)->first();

            if($channel) {       

                $channel->delete();

                $response_array = ['success'=>true, 'message'=>tr('channel_delete_success')];

            } else {

                $response_array = ['success'=> false, 'error_messages'=>tr('not_your_channel')];


            }

        }

        return response()->json($response_array);


    }

    /**
     * Function Nmae : ppv_list()
     * 
     * to list out  all the paid videos by logged in user using PPV
     *
     * @param object $request - User id, token 
     *
     * @return response of array with message
     */
    public function ppv_list(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'skip'=>($request->device_type == DEVICE_WEB) ? '' : 'required|numeric',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $error_messages = implode(',',$validator->messages()->all());

            $response_array = array(
                    'success' => false,
                    'error_messages' => $error_messages
            );
            return response()->json($response_array);
        } else {

            $currency = Setting::get('currency');

            $query = PayPerView::select('pay_per_views.id as pay_per_view_id',
                    'video_id',
                    'video_tapes.title',
                    'pay_per_views.amount',
                    'pay_per_views.status as video_status',
                    'video_tapes.default_image as picture',
                    'pay_per_views.type_of_subscription',
                    'pay_per_views.is_coupon_applied',
                    'pay_per_views.coupon_reason',
                    'pay_per_views.type_of_user',
                    'pay_per_views.payment_id',
                    'pay_per_views.ppv_amount',
                    'pay_per_views.coupon_amount',
                    'pay_per_views.coupon_code',
                    'pay_per_views.payment_mode',
                     DB::raw('DATE_FORMAT(pay_per_views.created_at , "%e %b %y") as paid_date')
                     )
                    ->leftJoin('video_tapes', 'video_tapes.id', '=', 'pay_per_views.video_id')
                    ->where('pay_per_views.user_id', $request->id)
                    ->where('pay_per_views.amount', '>', 0)
                    ->orderby('pay_per_views.created_at', 'desc');

            $user = User::find($request->id);

            if ($request->device_type == DEVICE_WEB) {

                $model = $query->paginate(16);

                $data = [];

            
                foreach ($model->items() as $key => $value) {

                    $is_ppv_status = DEFAULT_TRUE;

                    if ($user) {

                        $is_ppv_status = ($value->type_of_user == NORMAL_USER || $value->type_of_user == BOTH_USERS) ? ( ( $user->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                    } 

                    $videoDetails = $value->videoTapeResponse ? $value->videoTapeResponse : '';

                    $pay_per_view_status = $videoDetails ? (VideoRepo::pay_per_views_status_check($user ? $user->id : '', $user ? $user->user_type : '', $videoDetails)->getData()->success) : true;

                    $ppv_notes = !$pay_per_view_status ? ($videoDetails->type_of_user == 1 ? tr('normal_user_note') : tr('paid_user_note')) : ''; 
                 
                    $data[] = [
                            'pay_per_view_id'=>$value->pay_per_view_id,
                            'video_tape_id'=>$value->video_id,
                            'title'=>$value->title,
                            'amount'=>$value->amount,
                            'video_status'=>$value->video_status,
                            'paid_date'=>$value->paid_date,
                            'currency'=>Setting::get('currency'),
                            'picture'=>$value->picture,
                            'type_of_subscription'=>$value->type_of_subscription,
                            'type_of_user'=>$value->type_of_user,
                            'payment_id'=>$value->payment_id,
                            'pay_per_view_status'=>$pay_per_view_status,
                            'is_ppv_subscribe_page'=>$is_ppv_status, // 0 - Dont shwo subscribe+ppv_ page 1- Means show ppv subscribe page
                            'ppv_notes'=>$ppv_notes,
                            'coupon_code'=>$value->coupon_code,
                            'payment_mode'=>$value->payment_mode,
                            'coupon_amount'=>$value->coupon_amount,
                            'ppv_amount'=>$value->ppv_amount,
                            'is_coupon_applied'=>$value->is_coupon_applied,
                            'coupon_reason'=>$value->coupon_reason,
                            ];

                }

                $response_array = array('success'=>true, 'data' => $data, 'pagination' => (string) $model->links());

            } else {

                $model = $query->skip($request->skip)
                        ->take(Setting::get('admin_take_count' ,12))
                        ->get();

                $data = [];

                foreach ($model as $key => $value) {

                    $is_ppv_status = DEFAULT_TRUE;

                    if ($user) {

                        $is_ppv_status = ($value->type_of_user == NORMAL_USER || $value->type_of_user == BOTH_USERS) ? ( ( $user->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                    } 

                    $videoDetails = $value->videoTapeResponse ? $value->videoTapeResponse : '';

                    $pay_per_view_status = $videoDetails ? (VideoRepo::pay_per_views_status_check($user ? $user->id : '', $user ? $user->user_type : '', $videoDetails)->getData()->success) : true;

                    $spam = Flag::where('video_tape_id', $value->video_id)
                            ->where('user_id', $user->id)
                            ->first();

                    $spam_status = $spam ? true : false;
    
                    $data[] = ['pay_per_view_id'=>$value->pay_per_view_id,
                            'video_tape_id'=>$value->video_id,
                            'title'=>$value->title,
                            'amount'=>$value->amount,
                            'video_status'=>$value->video_status,
                            'paid_date'=>$value->paid_date,
                            'currency'=>Setting::get('currency'),
                            'picture'=>$value->picture,
                            'type_of_subscription'=>$value->type_of_subscription,
                            'type_of_user'=>$value->type_of_user,
                            'payment_id'=>$value->payment_id,
                            'pay_per_view_status'=>$pay_per_view_status,
                            'is_ppv_subscribe_page'=>$is_ppv_status, // 0 - Dont shwo subscribe+ppv_ 
                            'is_spam'=>$spam_status,
                            'coupon_code'=>$value->coupon_code,
                            'payment_mode'=>$value->payment_mode,
                            'coupon_amount'=>$value->coupon_amount,
                            'ppv_amount'=>$value->ppv_amount,
                            'is_coupon_applied'=>$value->is_coupon_applied,
                            'coupon_reason'=>$value->coupon_reason,
                            ];

                }

                $response_array = ['success'=>true, 'data'=>$data];
            }

            return response()->json($response_array);

        }

    } 


    /**
     * Function Name : paypal_ppv()
     * 
     * Pay the payment for Pay per view through paypal
     *
     * @param object $request - video tape id
     * 
     * @return response of success/failure message
     */
    public function paypal_ppv(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'video_tape_id'=>'required|exists:video_tapes,id,status,'.USER_VIDEO_APPROVED_STATUS.',is_approved,'.ADMIN_VIDEO_APPROVED_STATUS.',publish_status,'.VIDEO_PUBLISHED,
                    'payment_id'=>'required',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ),  array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'video_tape_id.exists' => tr('livevideo_not_exists'),
                ));


            if ($validator->fails()) {
                // Error messages added in response for debugging
                $errors = implode(',',$validator->messages()->all());

                $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

                throw new Exception($errors);

            } else {

                $video = VideoTape::find($request->video_tape_id);

                $user = User::find($request->id);

                $total = $video->ppv_amount;

                $coupon_amount = 0;

                $coupon_reason = '';

                $is_coupon_applied = COUPON_NOT_APPLIED;

                if ($request->coupon_code) {

                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                    if ($coupon) {
                        
                        if ($coupon->status == COUPON_INACTIVE) {

                            $coupon_reason = tr('coupon_inactive_reason');

                        } else {

                            $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                            if ($check_coupon->success) {

                                $is_coupon_applied = COUPON_APPLIED;

                                $amount_convertion = $coupon->amount;

                                if ($coupon->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($coupon->amount, $video->ppv_amount);

                                }

                                if ($amount_convertion < $video->ppv_amount  && $amount_convertion > 0) {

                                    $total = $video->ppv_amount - $amount_convertion;

                                    $coupon_amount = $amount_convertion;

                                } else {

                                    // throw new Exception(Helper::get_error_message(156),156);

                                    $total = 0;

                                    $coupon_amount = $amount_convertion;
                                    
                                }

                                // Create user applied coupon

                                if($check_coupon->code == 2002) {

                                    $user_coupon = UserCoupon::where('user_id', $user->id)
                                            ->where('coupon_code', $request->coupon_code)
                                            ->first();

                                    // If user coupon not exists, create a new row

                                    if ($user_coupon) {

                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                            $user_coupon->no_of_times_used += 1;

                                            $user_coupon->save();

                                        }

                                    }

                                } else {

                                    $user_coupon = new UserCoupon;

                                    $user_coupon->user_id = $user->id;

                                    $user_coupon->coupon_code = $request->coupon_code;

                                    $user_coupon->no_of_times_used = 1;

                                    $user_coupon->save();

                                }

                            } else {

                                $coupon_reason = $check_coupon->error_messages;
                                
                            }

                        }

                    } else {

                        $coupon_reason = tr('coupon_delete_reason');
                    }
                }

                $payment = PayPerView::where('user_id', $request->id)
                            ->where('video_id', $request->video_tape_id)
                            ->where('status', PAID_STATUS)
                            ->orderBy('ppv_date', 'desc')
                            ->first();

                $payment_status = DEFAULT_FALSE;

                if ($payment) {

                    if ($video->type_of_subscription == RECURRING_PAYMENT && $payment->is_watched == WATCHED) {

                        $payment_status = DEFAULT_FALSE;

                    } else {

                        $payment_status = DEFAULT_TRUE;

                    }

                } else {

                    $payment_status = DEFAULT_FALSE;

                }

                if ($video->is_pay_per_view == PPV_ENABLED) {

                    if ($payment_status) {

                        throw new Exception(tr('already_paid_amount_to_video'));

                    }

                    $user_payment = new PayPerView;
                    
                    $user_payment->payment_id  = $request->payment_id;

                    $user_payment->user_id = $request->id;

                    $user_payment->video_id = $request->video_tape_id;

                    $user_payment->status = PAID_STATUS;

                    $user_payment->is_watched = NOT_YET_WATCHED;

                    $user_payment->payment_mode = PAYPAL;

                    $user_payment->ppv_date = date('Y-m-d H:i:s');

                    if ($video->type_of_user == NORMAL_USER) {

                        $user_payment->type_of_user = tr('normal_users');

                    } else if($video->type_of_user == PAID_USER) {

                        $user_payment->type_of_user = tr('paid_users');

                    } else if($video->type_of_user == BOTH_USERS) {

                        $user_payment->type_of_user = tr('both_users');
                    }


                    if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                        $user_payment->type_of_subscription = tr('one_time_payment');

                    } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                        $user_payment->type_of_subscription = tr('recurring_payment');

                    }
                    // Coupon details

                    $user_payment->is_coupon_applied = $is_coupon_applied;

                    $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                    $user_payment->coupon_amount = $coupon_amount;

                    $user_payment->ppv_amount = $video->ppv_amount;

                    $user_payment->amount = $total;

                    $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                    $user_payment->save();

                    if($user_payment) {

                        // Do Commission spilit  and redeems for moderator

                        Log::info("ppv_commission_spilit started");

                        PaymentRepo::ppv_commission_split($video->id , $user_payment->id , "");

                        Log::info("ppv_commission_spilit END"); 
   

                    } 


                    $user = User::find($request->id);

                    $response_array = ['success'=>true, 'message'=>tr('payment_success'),
                            'data'=>['id'=>$user->id ,'token'=>$user->token]];

                } else {

                    throw new Exception(tr('ppv_not_set'));
                    
                }

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);
        }
    

    }    
   
    /**
     * Function Name : live_history()
     *
     * To display my live videos History
     *
     * @param object $request - User id and token
     *
     * @return return array of list
     */
    public function live_history(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'skip'=>($request->device_type == DEVICE_WEB) ? '' : 'required|numeric',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $error_messages = implode(',',$validator->messages()->all());

            $response_array = array(
                    'success' => false,
                    'error_messages' => $error_messages
            );
            return response()->json($response_array);
        } else {

            $currency = Setting::get('currency');

            $query = LiveVideoPayment::select('live_video_payments.id as live_video_payment_id',
                    'live_video_id',
                    'live_videos.title',
                    'live_video_payments.amount',
                    'live_video_payments.status as video_status',
                    'live_videos.snapshot as picture',
                    'live_video_payments.payment_id',
                     DB::raw('DATE_FORMAT(live_video_payments.created_at , "%e %b %y") as paid_date'),
                      DB::raw("'$currency' as currency"))
                    ->leftJoin('live_videos', 'live_videos.id', '=', 'live_video_payments.live_video_id')
                    ->where('live_video_payments.live_video_viewer_id', $request->id)
                    ->where('live_video_payments.amount', '>', 0);

            if ($request->device_type == DEVICE_WEB) {

                $model = $query->paginate(16);

                $response_array = array('success'=>true, 'data' => $model->items(), 'pagination' => (string) $model->links());

            } else {

                $model = $query->skip($request->skip)
                        ->take(Setting::get('admin_take_count' ,12))
                        ->get();

                $response_array = ['success'=>true, 'data'=>$model];
            }

            return response()->json($response_array);

        }
    }

    /**
     * Function Name : live_video_revenue()
     *
     * To display my live videos revenue history
     *
     * @param object $request - User id and token
     *
     * @return return array of list
     */
    public function live_video_revenue(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'skip'=>($request->device_type == DEVICE_WEB) ? '' : 'required|numeric',
            ));

        if ($validator->fails()) {
            // Error messages added in response for debugging
            $error_messages = implode(',',$validator->messages()->all());

            $response_array = array(
                    'success' => false,
                    'error_messages' => $error_messages
            );
            return response()->json($response_array);

        } else {

            $currency = Setting::get('currency');


            $model = LiveVideoPayment::select('live_video_payments.id as live_video_payment_id',
                    'live_video_id',
                    'live_videos.title',
                    'live_video_payments.amount',
                    'live_video_payments.status as video_status',
                    'live_videos.snapshot as video_image',
                    'live_video_payments.payment_id',
                    'live_videos.description',
                     DB::raw('DATE_FORMAT(live_video_payments.created_at , "%e %b %y") as paid_date'),
                      DB::raw("'$currency' as currency"),
                       DB::raw("sum(live_video_payments.user_amount) as user_amount"))
                    ->leftJoin('live_videos', 'live_videos.id', '=', 'live_video_payments.live_video_id')
                    ->where('live_video_payments.user_id', $request->id)
                    ->where('live_videos.channel_id', $request->channel_id)
                    ->where('live_video_payments.amount', '>', 0)
                    ->skip($request->skip)
                    ->take(Setting::get('admin_take_count' ,12))
                    ->groupBy('live_videos.id')
                    ->get();

            $response_array = ['success'=>true, 'data'=>$model];
        

            return response()->json($response_array);

        }
    }



    public function erase_streaming(Request $request) {

        $model = LiveVideo::where('user_id', $request->id)->where('status', 0)->get();

        foreach ($model as $key => $value) {
           
            $value->status = DEFAULT_TRUE;

            if ($value->save()) {

                
            }

        }

        $response_array = ['success'=>true, 'message'=>tr('streaming_stopped')];

        return response()->json($response_array);

    }

    /**
     * FOR MOBILE APP WE ARE USING THIS
     *  
     * Function Name: cards_add()
     *
     * Description: add card using stripe payment
     *
     * @created Vidhya R
     *
     * @edited Vidhya R
     *
     * @param 
     * 
     * @return
     */
    public function cards_add(Request $request) {

        $stripe_secret_key = \Setting::get('stripe_secret_key');

        if($stripe_secret_key) {

            \Stripe\Stripe::setApiKey($stripe_secret_key);

        } else {

            $response_array = ['success' => false, 'error_messages' => tr('add_card_is_not_enabled')];

            return response()->json($response_array);
        }

        try {

            $validator = Validator::make(
                    $request->all(),
                    [
                        'last_four' => '',
                        'card_token' => 'required',
                        'customer_id' => '',
                        'card_type' => '',
                    ]
                );

            if ($validator->fails()) {

                Log::info("validator FAILS INSIDE");

                $error = implode(',',$validator->messages()->all());
             
                throw new Exception($error , 101);

            } else {

                Log::info("INSIDE CARDS ADD");

                $user_details = User::find($request->id);

                if(!$user_details) {

                    throw new Exception(Helper::get_error_message(133), 133);
                    
                }

                $stripe_gateway_details = [
                    
                    "card" => $request->card_token,
                    
                    "email" => $user_details->email,
                    
                    "description" => "Customer for ".Setting::get('site_name'),
                    
                ];


                // Get the key from settings table
                
                $customer = \Stripe\Customer::create($stripe_gateway_details);

                if($customer) {

                    Log::info('Customer'.print_r($customer , true));

                    $customer_id = $customer->id;

                    $card_details = new Card;

                    $card_details->user_id = $request->id;

                    $card_details->customer_id = $customer->id;

                    $card_details->card_token = $customer->sources->data ? $customer->sources->data[0]->id : "";

                    $card_details->card_name = $customer->sources->data ? $customer->sources->data[0]->brand : "";

                    $card_details->last_four = $customer->sources->data[0]->last4 ? $customer->sources->data[0]->last4 : "";

                    // Check is any default is available

                     // check the user having any cards 

                    $check_user_cards = Card::where('user_id',$request->id)->count();

                    $card_details->is_default = $check_user_cards ? 0 : 1;

                    if($card_details->save()) {

                        if($user_details) {

                            $user_details->card_id = $check_user_cards ? $user_details->card_id : $card_details->id;

                            $user_details->save();
                        }

                        $data = [
                                'user_id' => $request->id, 
                                'card_id' => $card_details->id,
                                'customer_id' => $card_details->customer_id,
                                'last_four' => $card_details->last_four, 
                                'card_token' => $card_details->card_token, 
                                'is_default' => $card_details->is_default
                                ];


                        $response_array = ['success' => true, 'message' => tr('add_card_success'), 
                            'data'=> $data];

                            return response()->json($response_array , 200);

                    } else {

                        throw new Exception(Helper::get_error_message(123), 123);
                        
                    }
               
                } else {

                    throw new Exception(tr('cards_add_failed'));
                    
                }

            }

        } catch (Stripe_InvalidRequestError $e) {

            // Invalid parameters were supplied to Stripe's API

            Log::info("error2");

            $error2 = $e->getMessage();

            $response_array = array('success' => false , 'error_messages' => $error2 ,'error_code' => 903);

            return response()->json($response_array , 200);

        } catch (Stripe_AuthenticationError $e) {

            Log::info("error3");

            // Authentication with Stripe's API failed
            $error3 = $e->getMessage();

            $response_array = array('success' => false , 'error_messages' => $error3 ,'error_code' => 903);

            return response()->json($response_array , 200);

        } catch (Stripe_ApiConnectionError $e) {
            Log::info("error4");

            // Network communication with Stripe failed
            $error4 = $e->getMessage();

            $response_array = array('success' => false , 'error_messages' => $error4 ,'error_code' => 903);

            return response()->json($response_array , 200);

        } catch (Stripe_Error $e) {
            Log::info("error5");

            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error5 = $e->getMessage();

            $response_array = array('success' => false , 'error_messages' => $error5 ,'error_code' => 903);

            return response()->json($response_array , 200);

        } catch (\Stripe\StripeInvalidRequestError $e) {

            Log::info("error7");

            // Log::info(print_r($e,true));

            $response_array = array('success' => false , 'error_messages' => Helper::get_error_message(903) ,'error_code' => 903);

            return response()->json($response_array , 200);
        } catch(Exception $e) {


            $error_message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

            return response()->json($response_array , 200);
        }
   
    }

    /**
     * Function Name : tags_list
     *
     * To list out all active tags
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * @param object $request - ''
     *
     * @return response of array values
     */
    public function tags_list(Request $request) {

        $take = $request->take ? $request->take : Setting::get('admin_take_count');

        $query = Tag::select('tags.id as tag_id', 'name as tag_name', 'search_count as count')
                    ->where('status', TAG_APPROVE_STATUS)
                    ->orderBy('created_at', 'desc');

        if ($request->skip){
            $query->skip($request->skip)
                    ->take($take);
        }

        $tags = $query->get();

        return response()->json(['success'=>true, 'data'=>$tags]);
    }


    /**
     * Function Name : tags_view
     *
     * To get any one of the tag details
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * @param object $request - tag id
     *
     * @return response of object values
     */
    public function tags_view(Request $request) {

        $model = Tag::select('tags.id as tag_id', 'name as tag_name', 'search_count as count')->where('tags.status', TAG_APPROVE_STATUS)
                ->where('id', $request->tag_id)
                ->first();

        if ($model) {

            return response()->json(['success'=>true, 'data'=>$model]);

        } else {

            return response()->json(['success'=>false, 'error_messages'=>tr('tag_not_found')]);
        }

    }

    /**
     * Function Name : tags_videos()
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * To display based on tag
     *
     * @param object $request - User Details
     *
     * @return Response of videos list
     */
    public function tags_videos(Request $request) {

        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'tag_id' => 'required|exists:tags,id,status,'.TAG_APPROVE_STATUS,
                )
        );

        if($basicValidator->fails()) {

            $error_messages = implode(',', $basicValidator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $tag = Tag::find($request->tag_id);

            $base_query = VideoTape::leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'video_tapes.category_id' , '=' , 'categories.id')
                            ->leftJoin('video_tape_tags' , 'video_tape_tags.video_tape_id' , '=' , 'video_tapes.id')
                            ->where('video_tapes.publish_status' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.is_approved' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->videoResponse()
                            ->where('video_tapes.age_limit','<=', checkAge($request))
                            ->where('video_tape_tags.tag_id', $request->tag_id)
                            ->orderby('video_tapes.updated_at' , 'desc');

            if ($request->id) {

                // Check any flagged videos are present

                $flag_videos = flag_videos($request->id);

                if($flag_videos) {
                    
                    $base_query->whereNotIn('video_tapes.id',$flag_videos);
                }
                
            }

            if ($request->device_type == DEVICE_WEB) { 

                $videos = $base_query->paginate(16);

                $items = [];

                $pagination = 0;

                if (count($videos) > 0) {

                    foreach ($videos->items() as $key => $value) {
                        
                        $items[] = displayVideoDetails($value, $request->id);

                    }

                    $pagination = (string) $videos->links();

                }

                $response_array = ['success'=>true, 'items'=>$items, 'pagination'=>$pagination];

            } else {

                $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count'))->get();

                $data = [];

                if (count($videos) > 0) {

                    foreach ($videos as $key => $value) {
                        
                        $data[] = displayVideoDetails($value, $request->id);

                    }

                }

                $response_array = ['success'=>true, 'data'=>$data];
            }

        }

        return response()->json($response_array);        
    
    }

    /**
     * Function Name : categories_list()
     *
     * Load all the active categories
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * @param -
     *
     * @return response of json
     */
    public function categories_list(Request $request) {

        $model = Category::select('id as category_id', 'name as category_name')->where('unique_id','!=','making-money')->where('status', CATEGORY_APPROVE_STATUS)->orderBy('created_at', 'desc')
                ->get();

        return response()->json($model);
    
    }

    /**
     * Function Name : categories_view()
     *
     * category details based on id
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * @param - 
     * 
     * @return response of json
     */
    public function categories_view(Request $request) {

        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'category_id' => 'required|exists:categories,id,status,'.CATEGORY_APPROVE_STATUS,
                )
        );

        if($basicValidator->fails()) {

            $error_messages = implode(',', $basicValidator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];              

        } else {

            $model = Category::select('id as category_id', 'name as category_name', 'image as category_image', 'description')->where('status', CATEGORY_APPROVE_STATUS)
                ->where('id', $request->category_id)
                ->first();

            $channels_list = $this->categories_channels_list($request)->getData();

            $channels = [];

            if ($channels_list->success) {

                $channels = $channels_list->data;

            }

            $category_list = $this->categories_videos($request)->getData();

            $categories = [];

            if ($category_list->success) {

                $categories = $category_list->data;

            }

            $response_array = ['success'=>true, 'category'=>$model, 'category_videos'=>$categories,'channels_list'=>$channels];

        }

        return response()->json($response_array);
    }


    /**
     * Function Name : categories_videos()
     *
     * @created_by shobana
     *
     * @updated_by -
     *
     * To display based on category
     *
     * @param object $request - User Details
     *
     * @return Response of videos list
     */
    public function categories_videos(Request $request) {


        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'category_id' => 'required|exists:categories,id,status,'.CATEGORY_APPROVE_STATUS,
                )
        );

        if($basicValidator->fails()) {

            $error_messages = implode(',', $basicValidator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $base_query = VideoTape::leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                            ->leftJoin('categories' , 'video_tapes.category_id' , '=' , 'categories.id')
                            ->where('video_tapes.publish_status' , 1)
                            ->where('video_tapes.status' , 1)
                            ->where('video_tapes.is_approved' , 1)
                            ->where('channels.status', 1)
                            ->where('channels.is_approved', 1)
                            ->videoResponse()
                            ->where('video_tapes.age_limit','<=', checkAge($request))
                            ->where('category_id', $request->category_id)
                            ->where('categories.status', CATEGORY_APPROVE_STATUS)
                            ->orderby('video_tapes.updated_at' , 'desc');

            if ($request->id) {

                // Check any flagged videos are present

                $flag_videos = flag_videos($request->id);

                if($flag_videos) {
                    
                    $base_query->whereNotIn('video_tapes.id',$flag_videos);
                }
                
            }

            if ($request->device_type == DEVICE_WEB) { 

                $videos = $base_query->paginate(16);

                $items = [];

                $pagination = 0;

                if (count($videos) > 0) {

                    foreach ($videos->items() as $key => $value) {
                        
                        $items[] = displayVideoDetails($value, $request->id);

                    }

                    $pagination = (string) $videos->links();

                }

                $response_array = ['success'=>true, 'items'=>$items, 'pagination'=>$pagination];

            } else {

                $videos = $base_query->skip($request->skip)->take(Setting::get('admin_take_count'))->get();

                $data = [];

                if (count($videos) > 0) {

                    foreach ($videos as $key => $value) {
                        
                        $data[] = displayVideoDetails($value, $request->id);

                    }

                }
                
                $response_array = ['success'=>true, 'data'=>$data];

            }

        }

        return response()->json($response_array);        
    
    }


    /**
     * Function Name : categories_channels_list
     *
     * To list out all the channels which is in active status
     *
     * @created_by Shobana 
     *
     * @updated_by Shobana
     *
     * @param Object $request - USer Details
     *
     * @return array of channel list
     */
    public function categories_channels_list(Request $request) {

        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'category_id' => 'required|exists:categories,id,status,'.CATEGORY_APPROVE_STATUS,
                )
        );

        if($basicValidator->fails()) {

            $error_messages = implode(',', $basicValidator->messages()->all());

            return $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $age = 0;

            $channel_id = [];

            $query = Channel::where('channels.is_approved', DEFAULT_TRUE)
                    ->select('channels.*', 'video_tapes.id as video_tape_id', 'video_tapes.is_approved',
                        'video_tapes.status', 'video_tapes.channel_id')
                    ->leftJoin('video_tapes', 'video_tapes.channel_id', '=', 'channels.id')
                    ->where('video_tapes.category_id', $request->category_id)
                    ->where('channels.status', DEFAULT_TRUE)
                    ->where('video_tapes.is_approved', DEFAULT_TRUE)
                    ->where('video_tapes.publish_status', DEFAULT_TRUE)
                    ->where('video_tapes.status', DEFAULT_TRUE)
                    ->groupBy('video_tapes.channel_id');


            if($request->id) {

                $user = User::find($request->id);

                $age = $user->age_limit;

                $age = $age ? ($age >= Setting::get('age_limit') ? 1 : 0) : 0;

                if ($request->has('channel_id')) {

                    $query->whereIn('channels.id', $request->channel_id);
                }


                $query->where('video_tapes.age_limit','<=', $age);

            }

            if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

                $channels = $query->skip($request->skip)->take(Setting::get('admin_take_count', 12))->get();

            } else {

                $channels = $query->paginate(16);

            }

            $lists = [];

            $pagination = 0;

            if(count($channels) > 0) {

                foreach ($channels as $key => $value) {
                    $lists[] = ['channel_id'=>$value->id, 
                            'user_id'=>$value->user_id,
                            'picture'=> $value->picture, 
                            'title'=>$value->name,
                            'description'=>$value->description, 
                            'created_at'=>$value->created_at->diffForHumans(),
                            'no_of_videos'=>videos_count($value->id),
                            'subscribe_status'=>$request->id ? check_channel_status($request->id, $value->id) : '',
                            'no_of_subscribers'=>$value->getChannelSubscribers()->count(),
                    ];

                }

                if ($request->device_type != DEVICE_ANDROID && $request->device_type != DEVICE_IOS) {

                    $pagination = (string) $channels->links();

                }

            }

            if ($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

                $response_array = ['success'=>true, 'data'=>$lists];

            } else {

                $response_array = ['success'=>true, 'channels'=>$lists, 'pagination'=>$pagination];

            }

            return response()->json($response_array);

        }
    }


   /**
    * Function Name : autorenewal_cancel
    *
    * To cancel automatic subscription
    *
    * @created Shobana C
    *
    * @edited -
    *
    * @param object $request - USer details & payment details
    *
    * @return boolean response with message
    */
    public function autorenewal_cancel(Request $request) {

        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'cancel_reason' => 'required',
                )
        );

        if($basicValidator->fails()) {

            $error_messages = implode(',', $basicValidator->messages()->all());

            $response_array = ['success'=>false, 'error_messages'=>$error_messages];

        } else {

            $user_payment = UserPayment::where('user_id', $request->id)
                    ->where('status', PAID_STATUS)
                    ->orderBy('created_at', 'desc')->first();

            if($user_payment) {

                // Check the subscription is already cancelled

                if($user_payment->is_cancelled == AUTORENEWAL_CANCELLED) {

                    $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(175) , 'error_code' => 175];

                    return response()->json($response_array , 200);

                }

                $user_payment->is_cancelled = AUTORENEWAL_CANCELLED;

                $user_payment->cancel_reason = $request->cancel_reason;

                $user_payment->save();

                $subscription = $user_payment->subscription;

                $data = ['id'=>$request->id, 
                    'subscription_id'=>$user_payment->subscription_id,
                    'user_subscription_id'=>$user_payment->id,
                    'title'=>$subscription ? $subscription->title : '',
                    'description'=>$subscription ? $subscription->description : '',
                    'plan'=>$subscription ? $subscription->plan : '',
                    'amount'=>$user_payment->amount,
                    'status'=>$user_payment->status,
                    'expiry_date'=>date('d M Y', strtotime($user_payment->expiry_date)),
                    'created_at'=>$user_payment->created_at->diffForHumans(),
                    'currency'=>Setting::get('currency'),
                    'payment_mode'=>$user_payment->payment_mode,
                    'is_coupon_applied'=>$user_payment->is_coupon_applied,
                    'coupon_code'=>$user_payment->coupon_code,
                    'coupon_amount'=>$user_payment->coupon_amount,
                    'subscription_amount'=>$user_payment->subscription_amount,
                    'coupon_reason'=>$user_payment->coupon_reason,
                    'is_cancelled'=>$user_payment->is_cancelled,
                    'cancel_reason'=>$user_payment->cancel_reason
                ];

                $response_array = ['success'=> true, 'message'=>tr('cancel_subscription_success'), 'data'=>$data];

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(177), 'error_code'=>177];

            }

        }

        return response()->json($response_array);

    }

   /**
    * Function Name : autorenewal_enable
    *
    * To enable automatic subscription
    *
    * @created Shobana C
    *
    * @edited -
    *
    * @param object $request - USer details & payment details
    *
    * @return boolean response with message
    */
    public function autorenewal_enable(Request $request) {

        $user_payment = UserPayment::where('user_id', $request->id)
                ->where('status', PAID_STATUS)
                ->orderBy('created_at', 'desc')
                ->first();

        if($user_payment) {

            // Check the subscription is already cancelled

            if($user_payment->is_cancelled == AUTORENEWAL_ENABLED) {
        
                $response_array = ['success' => 'false' , 'error_messages' => Helper::get_error_message(176) , 'error_code' => 176];

                return response()->json($response_array , 200);
            
            }

            $user_payment->is_cancelled = AUTORENEWAL_ENABLED;
          
            $user_payment->save();

            $subscription = $user_payment->subscription;

            $data = ['id'=>$request->id, 
                'subscription_id'=>$user_payment->subscription_id,
                'user_subscription_id'=>$user_payment->id,
                'title'=>$subscription ? $subscription->title : '',
                'description'=>$subscription ? $subscription->description : '',
                'popular_status'=>$subscription ? $subscription->popular_status : '',
                'plan'=>$subscription ? $subscription->plan : '',
                'amount'=>$user_payment->amount,
                'status'=>$user_payment->status,
                'expiry_date'=>date('d M Y', strtotime($user_payment->expiry_date)),
                'created_at'=>$user_payment->created_at->diffForHumans(),
                'currency'=>Setting::get('currency'),
                'payment_mode'=>$user_payment->payment_mode,
                'is_coupon_applied'=>$user_payment->is_coupon_applied,
                'coupon_code'=>$user_payment->coupon_code,
                'coupon_amount'=>$user_payment->coupon_amount,
                'subscription_amount'=>$user_payment->subscription_amount,
                'coupon_reason'=>$user_payment->coupon_reason,
                'is_cancelled'=>$user_payment->is_cancelled,
                'cancel_reason'=>$user_payment->cancel_reason
            ];

            $response_array = ['success'=> true, 'data'=>$data, 'message'=>tr('autorenewal_enable_success')];

        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(177), 'error_code'=>177];

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : check_coupon_applicable_to_user()
     *
     * To check the coupon code applicable to the user or not
     *
     * @created_by - Shobana Chandrasekar
     *
     * @updated_by - 
     *
     * @param objects $coupon - Coupon details
     *
     * @param objects $user - User details
     *
     * @return response of success/failure message
     */
    public function check_coupon_applicable_to_user($user, $coupon) {

        try {

            $sum_of_users = UserCoupon::where('coupon_code', $coupon->coupon_code)->sum('no_of_times_used');

            if ($sum_of_users < $coupon->no_of_users_limit) {


            } else {

                throw new Exception(tr('total_no_of_users_maximum_limit_reached'));
                
            }

            $user_coupon = UserCoupon::where('user_id', $user->id)
                ->where('coupon_code', $coupon->coupon_code)
                ->first();

            // If user coupon not exists, create a new row

            if ($user_coupon) {

                if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                   // $user_coupon->no_of_times_used += 1;

                   // $user_coupon->save();

                    $response_array = ['success'=>true, 'message'=>tr('add_no_of_times_used_coupon'), 'code'=>2002];

                } else {

                    throw new Exception(tr('per_users_limit_exceed'));
                }

            } else {

                $response_array = ['success'=>true, 'message'=>tr('create_a_new_coupon_row'), 'code'=>2001];

            }

            return response()->json($response_array);

        } catch (Exception $e) {

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage()];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : apply_coupon_subscription()
     *
     * Apply coupon to subscription if the user having coupon codes
     *
     * @created By - Shobana Chandrasekar
     *
     * @edited_by - -
     *
     * @param object $request - User details, subscription details
     *
     * @return response of coupon details with amount
     *
     */
    public function apply_coupon_subscription(Request $request) {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',  
            'subscription_id'=>'required|exists:subscriptions,id'          
        ], array(
            'coupon_code.exists' => tr('coupon_code_not_exists'),
            'subscription_id.exists' => tr('subscription_not_exists'),
        ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        

        $model = Coupon::where('coupon_code', $request->coupon_code)->first();

        if ($model) {

            if ($model->status) {

                $user = User::find($request->id);

                $check_coupon = $this->check_coupon_applicable_to_user($user, $model)->getData();

                if ($check_coupon->success) {

                    if(strtotime($model->expiry_date) >= strtotime(date('Y-m-d'))) {

                        $subscription = Subscription::find($request->subscription_id);

                        if($subscription) {

                            if($subscription->status) {

                                $amount_convertion = $model->amount;

                                if ($model->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($model->amount, $subscription->amount);

                                }

                                if ($subscription->amount >= $amount_convertion && $amount_convertion > 0) {

                                    $amount = $subscription->amount - $amount_convertion;

                                    $response_array = ['success'=> true, 'data'=>['remaining_amount'=>$amount,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=>$model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount]];

                                } else {

                                    // $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(156), 'error_code'=>156];
                                    $amount = 0;
                                    $response_array = ['success'=> true, 'data'=>['remaining_amount'=>$amount,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount]];

                                }

                            } else {

                                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(180), 'error_code'=>180];

                            }

                        } else {

                            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(179), 'error_code'=>179];
                        }

                    } else {

                        $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(173), 'error_code'=>173];

                    }

                } else {

                    $response_array = ['success'=> false, 'error_messages'=>$check_coupon->error_messages];
                }

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(178), 'error_code'=>178];
            }



        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(174), 'error_code'=>174];

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : apply_coupon_video_tapes()
     *
     * Apply coupon to PPV if the user having coupon codes
     *
     * @created By - Shobana Chandrasekar
     *
     * @edited_by - -
     *
     * @param object $request - User details, ppv video details
     *
     * @return response of coupon details with amount
     *
     */
    public function apply_coupon_video_tapes(Request $request) {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',  
            'video_tape_id'=>'required|exists:video_tapes,id,publish_status,'.VIDEO_PUBLISHED.',is_approved,'.ADMIN_VIDEO_APPROVED_STATUS.',status,'.USER_VIDEO_APPROVED_STATUS,     
        ], array(
                'coupon_code.exists' => tr('coupon_code_not_exists'),
                'video_id.exists' => tr('video_not_exists'),
            ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        
        $model = Coupon::where('coupon_code', $request->coupon_code)->first();

        if ($model) {

            if ($model->status) {

                $user = User::find($request->id);

                $vod_video = VideoTape::where('id', $request->video_tape_id)->first();

                $check_coupon = $this->check_coupon_applicable_to_user($user, $model)->getData();

                if ($check_coupon->success) {

                    if(strtotime($model->expiry_date) >= strtotime(date('Y-m-d'))) {

                        $amount_convertion = $model->amount;

                        if ($model->amount_type == PERCENTAGE) {

                            $amount_convertion = amount_convertion($model->amount, $vod_video->ppv_amount);

                        }

                        if ($vod_video->ppv_amount >= $amount_convertion && $amount_convertion > 0) {

                            $amount = $vod_video->ppv_amount - $amount_convertion;

                            $response_array = ['success'=> true, 'data'=>[
                                'remaining_amount'=>$amount,
                                'coupon_amount'=>$amount_convertion,
                                'coupon_code'=>$model->coupon_code,
                                'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount
                                ]];

                        } else {

                            $amount = $vod_video->ppv_amount - $amount_convertion;

                            $response_array = ['success'=> true, 'data'=>[
                                'remaining_amount'=>0,
                                'coupon_amount'=>$amount_convertion,
                                'coupon_code'=>$model->coupon_code,
                                'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount
                                ]];

                        }
                       

                    } else {

                        $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(173), 'error_code'=>173];

                    }

                } else {

                    $response_array = ['success'=> false, 'error_messages'=>$check_coupon->error_messages];

                }

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(178), 'error_code'=>178];
            }            

        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(174), 'error_code'=>174];

        }

        return response()->json($response_array);

    }


    /**
     * Function : custom_live_videos()
     *
     * Created By : shobana 
     *
     * Edited By : None
     *
     * @usage used to return list of live videos
     */
    public function custom_live_videos(Request $request) {

        $model = CustomLiveVideo::where('status', DEFAULT_TRUE)->liveVideoResponse()->orderBy('created_at', 'desc');

        if ($request->has('custom_live_video_id')) {

            $model->whereNotIn('id', [$request->custom_live_video_id]);

        }

        $take = $request->has('take') ? $request->take : Setting::get('admin_take_count' ,12);

        $response = $model->skip($request->skip)->take($take)->get();

        $response_array = ['success' => true , 'live' => $response];

        return response()->json($response_array , 200);

    }

    /**
     * Function : single_live_video()
     *
     * Created By : shobana
     *
     * Edited By : None
     *
     * @usage used to return single live video details
     */

    public function single_custom_live_video(Request $request) {

        $model = CustomLiveVideo::where('id', $request->custom_live_video_id)->where('status' , 1)->liveVideoResponse()->first();

        $suggestions = CustomLiveVideo::where('id','!=', $request->custom_live_video_id)->where('status' , 1)->liveVideoResponse()->get();

        if ($model) {

            $response_array = ['success'=>true, 'model'=>$model , 'suggestions' => $suggestions];

        } else {

            $response_array = ['success' => false, 'message' => tr('custom_live_video_not_found')];
        }

        return response()->json($response_array,200);
    } 

    /**
     * Function Name : apply_coupon_live_videos()
     *
     * Apply coupon to live videos if the user having coupon codes
     *
     * @created By - Shobana Chandrasekar
     *
     * @edited_by - -
     *
     * @param object $request - User details, livevideo details
     *
     * @return response of coupon details with amount
     *
     */
    public function apply_coupon_live_videos(Request $request) {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',  
            'live_video_id'=>'required|exists:live_videos,id'          
        ], array(
            'coupon_code.exists' => tr('coupon_code_not_exists'),
            'live_video_id.exists' => tr('livevideo_not_exists'),
        ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        

        $model = Coupon::where('coupon_code', $request->coupon_code)->first();

        if ($model) {

            if ($model->status) {

                $user = User::find($request->id);

                $check_coupon = $this->check_coupon_applicable_to_user($user, $model)->getData();

                if ($check_coupon->success) {

                    if(strtotime($model->expiry_date) >= strtotime(date('Y-m-d'))) {

                        $live_video = LiveVideo::find($request->live_video_id);

                        if($live_video) {

                            if($live_video->status == VIDEO_STREAMING_ONGOING) {

                                $amount_convertion = $model->amount;

                                if ($model->amount_type == PERCENTAGE) {

                                    $amount_convertion = round(amount_convertion($model->amount, $live_video->amount), 2);

                                }

                                if ($live_video->amount > $amount_convertion && $amount_convertion > 0) {

                                    $amount = $live_video->amount - $amount_convertion;

                                    $response_array = ['success'=> true, 
                                    'data'=>[
                                        'remaining_amount'=>(string) $amount,
                                        'coupon_amount'=>(string) $amount_convertion,
                                        'coupon_code'=>$model->coupon_code,
                                        'original_coupon_amount'=>(string) ($model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount)
                                    ]];

                                } else {

                                    // $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(156), 'error_code'=>156];
                                    $amount = 0;
                                    $response_array = ['success'=> true, 
                                    'data'=>[
                                            'remaining_amount'=>(string) $amount,
                                            'coupon_amount'=>(string) $amount_convertion,
                                            'coupon_code'=>$model->coupon_code,
                                            'original_coupon_amount'=>(string) ($model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount)
                                    ]];

                                }

                            } else {

                                $response_array = ['success'=> false, 'error_messages'=>tr('streaming_stopped')];

                            }

                        } else {

                            $response_array = ['success'=> false, 'error_messages'=>Helper::error_message(173), 'error_code'=>173];
                        }

                    } else {

                        $response_array = ['success'=> false, 'error_messages'=>Helper::error_message(173), 'error_code'=>173];

                    }

                } else {

                    $response_array = ['success'=> false, 'error_messages'=>$check_coupon->error_messages];
                }

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::error_message(178), 'error_code'=>178];
            }



        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::error_message(174), 'error_code'=>174];

        }

        return response()->json($response_array);

    }  


// Connect Stream
    public function connectStream($file = null) {

        try {
            $client = new \GuzzleHttp\Client();

            $url  = Setting::get('wowza_server_url')."/v2/servers/_defaultServer_/vhosts/_defaultVHost_/sdpfiles/$file/actions/connect?connectAppName=live&appInstance=_definst_&mediaCasterType=rtp";

            $request = new \GuzzleHttp\Psr7\Request('PUT', $url);
            $promise = $client->sendAsync($request)->then(function ($response) {
                    // echo 'I completed! ' . $response->getBody();
                Log::info(print_r($response->getBody(), true));
            });
            $promise->wait();
        } catch(\GuzzleHttp\Exception\ClientException $e) {
           // dd($e->getResponse()->getBody()->getContents());
        }

    }

    // Disconnect Stream
    public function disConnectStream($file = null) {

        try {
            $client = new \GuzzleHttp\Client();

            $sdp = $file.".sdp";

            $url  = Setting::get('wowza_server_url')."/v2/servers/_defaultServer_/vhosts/_defaultVHost_/applications/live/instances/_definst_/incomingstreams/$sdp/actions/disconnectStream";

            $request = new \GuzzleHttp\Psr7\Request('PUT', $url);
            $promise = $client->sendAsync($request)->then(function ($response) {
                    //  echo 'I completed! ' . $response->getBody();

                Log::info('I completed! ' . $response->getBody());
                
            });
            $promise->wait();

            $this->deleteStream($file);

        } catch(\GuzzleHttp\Exception\ClientException $e) {
            // dd($e->getResponse()->getBody()->getContents());

            Log::info($e->getResponse()->getBody()->getContents());
        }

    }

    // Delete Stream
    public function deleteStream($file = null) {
        try {
            $client = new \GuzzleHttp\Client();

            $url  = Setting::get('wowza_server_url')."/v2/servers/_defaultServer_/vhosts/_defaultVHost_/sdpfiles/$file";

            $request = new \GuzzleHttp\Psr7\Request('DELETE', $url);
            $promise = $client->sendAsync($request)->then(function ($response) {
                     Log::info('I completed! ' . $response->getBody());
            });
            $promise->wait();
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            // dd($e->getResponse()->getBody()->getContents());

            Log::info($e->getResponse()->getBody()->getContents());
        }

    }

    /**
     * Function Name : user_subscriptions_index
     *
     * @uses To display created video subscription list based on logged in user
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public function user_subscriptions_index(Request $request) {

        try {

            $base_query = UserSubscription::select('id as user_subscription_id', 'channel_id', 'user_id', 'title', 'description', 'amount', 'plan', 'status', 'created_at', 'total_subscription');


            if($request->id && $request->type != "subscribe") {

                $base_query = $base_query->where('user_id', $request->id);

            }

            if($request->channel_id) {

                $channel_details = Channel::find($request->channel_id);

                if(!$channel_details) {

                    throw new Exception(tr('channel_not_found'), 101);
                    
                }

                $base_query = $base_query->where('channel_id', $request->channel_id);

            }

            $user_subscriptions = $base_query->orderBy('updated_at' , 'desc')->get();

            $data = [];

            $data['user_subscriptions'] = $user_subscriptions;

            $data['channel_details'] = $channel_details;

            $response_array = ['success' => true, 'data' => $data];

            return response()->json($response_array);

        } catch(Exception $e) {

            $response_array = ['success' => false, 'error_messages' => $e->getMessage()];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : user_subscriptions_save
     *
     * @uses To save the video subscription plan model
     *
     * @created vithya R
     *
     * @created vithya R
    *
     * @param Object $request
     *
     * @return boolean response of the model (Success/failure Message)
     */
    public function user_subscriptions_save(Request $request) {

        try {            

            $validator = validator($request->all(), [
                'channel_id' => 'exists:channels,id',
                'title' => 'required',
                'description'=>'required',
                'amount' => 'required|numeric|min:0',
                'plan'=>'required|integer|min:1',
                'image' => 'mimes:jpeg,bmp,png',
                'user_subscription_id' => $request->user_subscription_id ? 'required|integer|exists:user_subscriptions,id,user_id,'.$request->id : '',
                ]);

            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception( $error, 101);  
            }

            DB::beginTransaction();

            if($request->user_subscription_id != '' ) {

                $user_subscription_details = UserSubscription::find($request->user_subscription_id);

            } else {

                $user_subscription_details = new UserSubscription;
                
                $user_subscription_details->subscription_type = "month";

                $user_subscription_details->status = DEFAULT_TRUE;
            } 

            $user_subscription_details->unique_id = $request->title;

            $user_subscription_details->channel_id = $request->channel_id;

            $channel_details = Channel::find($request->channel_id);

            $user_subscription_details->user_id = $channel_details ? $channel_details->user_id : 0;

            $user_subscription_details->title = $request->title;
            
            $user_subscription_details->description = $request->description;

            $user_subscription_details->amount = $request->amount;

            $user_subscription_details->plan = $request->plan;

            if( $user_subscription_details->save()) {
                
                DB::commit();

            } else {
                
                throw new Exception(tr('user_subscription_save_error'), 101);
            }


            $message = $request->user_subscriptions_id ? tr('user_subscription_update_success') : tr('user_subscription_create_success'); 

            $response_array = ['success' => true, 'message' => $message];

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }

    }   

    /**
     * Function Name : user_subscriptions_status
     *
     * @uses To change the status of the video plan status
     *
     * @created vithya R
     *
     * @updated vithya R
     *
     * @param integer $request - status of the plan
     *
     * @return response of boolean
     */
    public function user_subscriptions_status(Request $request) {

        try {

            $validator = Validator::make($request->all(),array(
                    'user_subscription_id' => $request->user_subscription_id ? 'required|integer|exists:user_subscriptions,id,user_id,'.$request->id : '',
                )
            );
            
            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception( $error_messages, 101);

            } 

            DB::beginTransaction();

            $user_subscription_details = UserSubscription::find($request->user_subscription_id);

            if (!$user_subscription_details) {

                throw new Exception(tr('user_subscription_not_found'));  

            }

            $user_subscription_details->status = $user_subscription_details->status == APPROVED ? DECLINED : APPROVED;

            if ($user_subscription_details->save()) {

                DB::commit();

                $message = $user_subscription_details->status ? tr('user_subscription_approve_success') : tr('user_subscription_decline_success');

                $response_array = ['success' => true, 'message' => $message];

            } else {

                throw new Exception(tr('user_subscription_status_error'));
                
            }
            
            return response()->json($response_array);

        } catch (Exception $e) {

            DB::rollback();
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }
    
    }

    /**
     * Function Name : user_subscriptions_delete
     *
     * @uses Delete a particular video plan
     *
     * @created vithya R
     *
     * @updated vithya R
     *
     * @param integer $request user_subscription_id
     *
     * @return response of boolean
     */

    public function user_subscriptions_delete(Request $request) {

        try {

            $validator = Validator::make($request->all(),array(
                    'user_subscription_id' => $request->user_subscription_id ? 'required|integer|exists:user_subscriptions,id,user_id,'.$request->id : '',
                )
            );
            
            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception( $error_messages, 101);

            }

            DB::beginTransaction();

            $user_subscription_details = UserSubscription::find($request->user_subscription_id);

            if (!$user_subscription_details) {

                throw new Exception(tr('user_subscription_not_found'));  

            }

            if ($user_subscription_details->delete()) {

                DB::commit();

                $response_array = ['success' => true, 'message' => tr('user_subscription_delete_success')];

            } else {

                throw new Exception(tr('user_subscription_delete_error'));  

                $response_array = ['success' => false, 'error_messages' => tr('user_subscription_delete_error')]; 
            }


            return response()->json($response_array);

        } catch (Exception $e) {

            DB::rollback();
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }

    }
    
    /**
     * Function Name : user_subscriptions_subscribers
     *
     * @uses get subscribers list based on the selected subscription
     *
     * @created vithya R
     *
     * @updated vithya R
     *
     * @param integer $request user_subscription_id
     *
     * @return response of boolean
     */

    public function user_subscriptions_subscribers(Request $request) {

        try {

            $validator = Validator::make($request->all(),array(
                    'user_subscription_id' => $request->user_subscription_id ? 'required|integer|exists:user_subscriptions,id,user_id,'.$request->id : '',
                )
            );
            
            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception( $error_messages, 101);
                
            }
        
            $channel_details = $user_subscription_details = [];

            if($request->channel_id) {

                $channel_details = Channel::find($request->channel_id);

                if(!$channel_details) {

                    throw new Exception(tr('channel_not_found'), 101);
                    
                }

            }

            if($request->user_subscription_id) {

                $user_subscription_details = UserSubscription::find($request->user_subscription_id);

                if(!$user_subscription_details) {

                    throw new Exception(tr('user_subscription_not_found'), 101);
                    
                }

            }

            $user_subscription_payments = UserSubscriptionPayment::where('user_subscription_id', $request->user_subscription_id)->get();


            foreach ($user_subscription_payments as $key => $user_subscription_payment) {

                $user_subscription_payment->user_picture = $user_subscription_payment->userDetails ? $user_subscription_payment->userDetails->picture : "";

                $user_subscription_payment->username = $user_subscription_payment->userDetails ? $user_subscription_payment->userDetails->name : "";

                $user_subscription_payment->subscription_name = $user_subscription_payment->UserSubscriptionDetails ? $user_subscription_payment->UserSubscriptionDetails->title : "";

            }

            $data = [];

            $data['user_subscription_payments'] = $user_subscription_payments;

            $data['channel_details'] = $channel_details;

            $data['user_subscription_details'] = $user_subscription_details;


            $response_array = ['success' => true, 'data' => $data];

            return response()->json($response_array);

        } catch (Exception $e) {
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }

    }

    /**
     * Function Name : user_subscriptions_payment_by_stripe
     *
     * @uses stripe payment for user subscription
     *
     * @created vithya R
     *
     * @updated vithya R
     *
     * @param integer $request user_subscription_id
     *
     * @return response of boolean
     */

    public function user_subscriptions_payment_by_stripe(Request $request) {

        try {

            $validator = Validator::make($request->all(),array(
                    'user_subscription_id' => 'required|exists:user_subscriptions,id',
                )
            );
            
            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception( $error_messages, 101);

            }

            $user_subscription_details = UserSubscription::find($request->user_subscription_id);

            if (!$user_subscription_details) {

                throw new Exception(tr('user_subscription_not_found'), 101);

            }

            $total = $user_subscription_details->amount;

            $user_details = User::find($request->id);

            if ($total > 0) {

                $check_card_exists = User::where('users.id' , $request->id)
                                ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                ->where('cards.id' , $user_details->card_id)
                                ->where('cards.is_default' , DEFAULT_TRUE);

                if($check_card_exists->count() != 0) {

                    $user_card_details = $check_card_exists->first();

                    $stripe_secret_key = Setting::get('stripe_secret_key');

                    $customer_id = $user_card_details->customer_id;

                    if(!$stripe_secret_key) {

                        throw new Exception(Helper::get_error_message(902), 902);

                    }
                        
                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                    try {

                       $user_charge =  \Stripe\Charge::create(array(
                           "amount" => $total * 100,
                           "currency" => "usd",
                           "customer" => $customer_id,
                        ));

                       $payment_id = $user_charge->id;

                       $amount = $user_charge->amount/100;

                       $paid_status = $user_charge->paid;

                        if($paid_status) {

                            $user_subscription_payment = UserSubscriptionPayment::where('user_id' , $request->id)
                                                            ->where('user_subscription_id', $request->user_subscription_id)
                                                            ->where('paid_amount', '<=', 0)
                                                            ->where('status', DEFAULT_FALSE)
                                                            ->first();

                            if(!$user_subscription_payment) {

                                $user_subscription_payment = new UserSubscriptionPayment;

                                $user_subscription_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$user_subscription_details->plan." months"));
                            }

                            // Expiry date 

                            $check_previous_payment = UserSubscriptionPayment::where('user_id' , $request->id)
                                        ->where('channel_id', $user_subscription_details->channel_id)
                                        ->where('status', PAID_STATUS)
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                            $user_subscription_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$user_subscription_details->plan} months"));

                            if($check_previous_payment) {

                                if (strtotime($check_previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                                    $user_subscription_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$user_subscription_details->plan} months", strtotime($check_previous_payment->expiry_date)));

                                } else {

                                    $user_subscription_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$user_subscription_details->plan} months"));

                                }

                            } 


                            $user_subscription_payment->payment_id  = $payment_id;

                            $user_subscription_payment->user_id = $request->id;

                            $user_subscription_payment->user_subscription_id = $request->user_subscription_id;

                            $user_subscription_payment->channel_id = $request->channel_id;

                            $user_subscription_payment->paid_amount = $amount;

                            $user_subscription_payment->payment_mode = CARD;

                            $user_subscription_payment->status = PAID_STATUS;

                            $user_subscription_payment->subscription_amount = $user_subscription_details->amount;

                            $user_subscription_payment->subscription_plan = $user_subscription_details->plan;

                            $admin_commission = Setting::get('admin_commission')/100;

                            $admin_amount = $total * $admin_commission;

                            $user_amount = $total - $admin_amount;

                            $user_subscription_payment->admin_subscribe_amount = $admin_amount;

                            $user_subscription_payment->user_subscribe_amount = $user_amount;

                            $user_subscription_payment->save();

                            // Increase total subscriptions

                            $user_subscription_details->total_subscription += 1;

                            $user_subscription_details->save();

                            // Commission Spilit Completed

                            if($subscription_owner_details = User::find($user_subscription_details->user_id)) {

                                $subscription_owner_details->total_admin_amount += $admin_amount;

                                $subscription_owner_details->total_user_amount += $user_amount;

                                $subscription_owner_details->remaining_amount += $user_amount;

                                $subscription_owner_details->total_amount += $total;

                                $subscription_owner_details->save();

                                add_to_redeem($subscription_owner_details->id , $user_amount);
                            }


                            $data = ['id' => $user_details->id , 'token' => $user_details->token,'paymentid' => $payment_id];

                            Log::info("Stripe Payment".print_r($data, true));

                            $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                            return response()->json($response_array, 200);

                        } else {

                            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(903) , 'error_code' => 903);

                            return response()->json($response_array, 200);

                        }

                    
                    } catch (\Stripe\StripeInvalidRequestError $e) {

                        Log::info(print_r($e,true));

                        $response_array = array('success' => false , 'error_messages' => Helper::get_error_message(903) ,'error_code' => 903);

                        return response()->json($response_array , 200);

                    }

                } else {

                    $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(901) , 'error_code' => 901);

                    return response()->json($response_array , 200);
                }

            } else {


                $user_payment = SubscribeVideoPlan::where('user_id' , $request->id)->where('video_id', $request->video_id)->where('status', DEFAULT_FALSE)->first();

                if($user_payment) {

                    $expiry_date = $user_payment->expiry_date;
                    $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_date. "+".$user_subscription_details->plan_duration." months"));

                } else {
                    $user_payment = new SubscribeVideoPlan;
                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$user_subscription_details->plan_duration." months"));
                }


                $user_payment->payment_id  = "free_plan";
                $user_payment->user_id = $request->id;
                $user_payment->video_subscription_plan_id = $request->video_subscription_id;
                $user_payment->video_id = $request->video_id;
                $user_payment->amount = $user_subscription_details->amount;
                

                $admin_commission = Setting::get('admin_subscribe_video_commission')/100;

                $admin_amount = $total * $admin_commission;

                $user_amount = $total - $admin_amount;


                $user_payment->admin_amount = $admin_amount;

                $user_payment->user_amount = $user_amount;


                $user_payment->save();

                // Commission Spilit Completed

                if($user_model = User::find($user_payment->video->user_id)) {

                    $user_model->total_admin_amount = $user_model->total_admin_amount + $admin_amount;

                    $user_model->total_user_amount = $user_model->total_user_amount + $user_amount;

                    $user_model->remaining_amount = $user_model->remaining_amount + $user_amount;

                    $user_model->total = $user_model->total + $total;

                    $user_model->save();
                    
                    add_to_redeem($user_model->id , $user_amount);
                }

                
                $data = ['id' => $user->id , 'token' => $user->token, 'paymentid'=>$user_payment->payment_id];

                Log::info("Stripe Payment".print_r($data, true));

                $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                return response()->json($response_array, 200);

            }
            
            return response()->json($response_array , 200);

        } catch (Exception $e) {
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }
    
    }

    /**
     * Function Name : user_subscriptions_history()
     *
     * @uses user payments history based on the channel
     *
     * @created Vithya R
     *
     * @updated 
     *
     * @param -
     *
     * @return json response details
     */

    public function user_subscriptions_history(Request $request) {

        try {

            $validator = Validator::make($request->all(),
                array(
                    'skip' => ($request->device_type == DEVICE_WEB) ? '' : 'required|numeric',
                    'channel_id' => 'required|exists:channels,id'
                ));

            if ($validator->fails()) {
                
                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }

            $channel_details = Channel::find($request->channel_id);

            if(!$channel_details) {

                throw new Exception(tr('channel_not_found'), 101);
                
            }

            $query = UserSubscriptionPayment::where('user_subscription_payments.user_id' , $request->id)
                        ->where('user_subscription_payments.channel_id', $request->channel_id)
                        ->leftJoin('user_subscriptions', 'user_subscriptions.id', '=', 'user_subscription_id')
                        ->select('user_subscription_payments.user_id as id',
                                'user_subscription_id',
                                'user_subscription_payments.id as user_subscription_id',
                                \DB::raw('IFNULL(user_subscriptions.title,"") as title'),
                                \DB::raw('IFNULL(user_subscriptions.description,"") as description'),
                                \DB::raw('IFNULL(user_subscriptions.plan,"") as plan'),
                                'user_subscriptions.amount as current_subscription_amount',
                                'user_subscription_payments.subscription_amount as amount',
                                'user_subscription_payments.status as status',
                                \DB::raw('DATE_FORMAT(user_subscription_payments.expiry_date , "%e %b %Y") as expiry_date'),
                                'user_subscription_payments.created_at as created_at',
                                DB::raw("'$' as currency"),
                                'user_subscription_payments.payment_mode',
                                // 'user_subscription_payments.is_coupon_applied',
                                // 'user_subscription_payments.coupon_code',
                                // 'user_subscription_payments.coupon_amount',
                                'user_subscription_payments.subscription_amount',
                                // 'user_subscription_payments.coupon_reason',
                                // 'user_subscription_payments.is_cancelled',
                                // 'user_subscription_payments.cancel_reason',
                                'user_subscription_payments.payment_id')
                        ->orderBy('user_subscription_payments.updated_at', 'desc');
                        
            if ($request->device_type == DEVICE_WEB) {

                $user_subscription_payments = $query->paginate(16);

                $response_array = ['success' => true, 'data' => $user_subscription_payments->items(), 'pagination' => (string) $user_subscription_payments->links(), 'channel_details' => $channel_details];

            } else {

                $model = $query->skip($request->skip)
                        ->take(Setting::get('admin_take_count' ,12))
                        ->get();

                $data = [];

                foreach ($model as $key => $value) { 

                    $data[] = [

                        'id'=>$value->id,
                        'subscription_id'=>$value->subscription_id,
                        'user_subscription_id'=>$value->user_subscription_id,
                        'title'=>$value->title,
                        'description'=>$value->description,
                        'plan'=>$value->plan,
                        'amount'=>$value->amount,
                        'status'=>$value->status,
                        'expiry_date'=>$value->expiry_date,
                        'created_at'=>$value->created_at->diffForHumans(),
                        'currency'=>$value->currency,
                        'payment_mode'=>$value->payment_mode,
                        'is_coupon_applied'=>$value->is_coupon_applied,
                        'coupon_code'=>$value->coupon_code,
                        'coupon_amount'=>$value->coupon_amount,
                        'subscription_amount'=>$value->subscription_amount,
                        'coupon_reason'=>$value->coupon_reason,
                        'is_cancelled'=>$value->is_cancelled,
                        'payment_id'=>$value->payment_id,
                        'cancel_reason'=>$value->cancel_reason,
                        'active_plan'=>($key == 0 && $value->status) ? ACTIVE_PLAN : NOT_ACTIVE_PLAN,
                    ];


                }

                $response_array = ['success'=>true, 'data'=>$data];
            
            }

            return response()->json($response_array);

        } catch (Exception $e) {
            
            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array, 200);

        }

    }
    
public function add_update_coin_market(){

$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
$parameters = [
  'start' => '1',
  'limit' => '25',
  'convert' => 'USD'
];

$headers = [
  'Accepts: application/json',
  'X-CMC_PRO_API_KEY: b817511a-72a8-4092-9aa6-671604f0236d'
];
$qs = http_build_query($parameters); // query string encode the parameters
$request = "{$url}?{$qs}"; // create the request URL


$curl = curl_init(); // Get cURL resource
// Set cURL options
curl_setopt_array($curl, array(
  CURLOPT_URL => $request,            // set the request URL
  CURLOPT_HTTPHEADER => $headers,     // set the headers 
  CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
));

$response = curl_exec($curl); // Send the request, save the response

$data = json_decode($response); // print json decoded response
curl_close($curl); // Close request
 
 // PaymentMethod::truncate();
   
foreach($data->data as $d){
    $datapayment = PaymentMethod::where('name',$d->name)->first();
    if(@$datapayment){
      $currency = $datapayment;
    }else{
       $currency = new PaymentMethod; 
    }
    
   $currency->name = $d->name;
   $currency->symbol = $d->symbol;
   $currency->slug = $d->slug;
   $currency->circulating_supply = $d->circulating_supply;
   $currency->total_supply = $d->total_supply;
   if(@$d->max_supply){
   $currency->max_supply = $d->max_supply;
   }else{
     $currency->max_supply = "";
   }
  
   $currency->num_market_pairs = $d->num_market_pairs;
   $currency->cmc_rank = $d->cmc_rank;
   $currency->usd_price = $d->quote->USD->price;
   $currency->usd_volume_24h = $d->quote->USD->volume_24h;
   $currency->usd_volume_percent_change_1h = $d->quote->USD->percent_change_1h;
   $currency->percent_change_7d = $d->quote->USD->percent_change_7d;
   $currency->usd_percent_change_24h = $d->quote->USD->percent_change_24h;
   $currency->market_cap = $d->quote->USD->market_cap;
   $currency->last_updated = $d->quote->USD->last_updated;
   $currency->save();
  }
  // $currency = new PaymentMethod;
  //  $currency->name = "Venmo";
  //  $currency->cmc_rank = 26;
  //  $currency->save();
  //  $currency = new PaymentMethod;
  //  $currency->name = "CashApp";
  //  $currency->cmc_rank = 27;
  //  $currency->save();
  //    $currency = new PaymentMethod;
  //  $currency->name = "Paypal";
  //  $currency->cmc_rank = 26;
  //  $currency->save();
  //  $currency = new PaymentMethod;
  //  $currency->name = "Mastercard";
  //  $currency->cmc_rank = 27;
  //  $currency->save();
  //     $currency = new PaymentMethod;
  //  $currency->name = "Visa";
  //  $currency->cmc_rank = 26;
  //  $currency->save();
  //  $currency = new PaymentMethod;
  //  $currency->name = "Other";
  //  $currency->cmc_rank = 27;
  //  $currency->save();
   
   
}

 public function makingMoney($request) {

         $base_query = VideoTape::where('video_tapes.is_approved' , 1)->leftJoin('channels' , 'video_tapes.channel_id' , '=' , 'channels.id')
                        ->where('video_tapes.publish_status' , 1)
                        ->where('video_tapes.status' , 1)
                        ->where('channels.status', 1)
                        ->where('channels.is_approved', 1)
                        ->leftJoin('categories' , 'categories.id' , '=' , 'video_tapes.category_id') 
                        ->where('categories.status', CATEGORY_APPROVE_STATUS)
                        ->where('categories.unique_id','making-money')->where('video_tapes.uploaded_by','admin')
                        ->videoResponse()
                        
                        ->orderby('watch_count' , 'desc');

        if ($request->id) {

            // Check any flagged videos are present

            $flag_videos = flag_videos($request->id);

            if($flag_videos) {
                
                $base_query->whereNotIn('video_tapes.id',$flag_videos);
            }

            $base_query = $base_query->where('video_tapes.age_limit','<=', checkAge($request));

        } else {

            $base_query = $base_query->where('video_tapes.age_limit','=', 0);
        }

        $videos = $base_query->paginate(16);

        $items = [];

        $pagination = 0;

        if (count($videos) > 0) {

            foreach ($videos->items() as $key => $value) {
                
                $items[] = displayVideoDetails($value, $request->id);

            }

            $pagination = (string) $videos->links();

        }

        return response()->json(['items'=>$items, 'pagination'=>$pagination]);
    
    }
    function Credit_Points($inputRequest){
          $this->inputRequest = $inputRequest;

        // Log::info(print_r($this->inputRequest, true));
        Log::info("UserPostJob inputRequest".print_r($this->inputRequest, true));
     $video_tape_id = isset($this->inputRequest['video_tape_id']) ? $this->inputRequest['video_tape_id'] : 0;
      $channel_id = isset($this->inputRequest['channel_id']) ? $this->inputRequest['channel_id'] : 0;
        $user_details = User::find($this->inputRequest['owner_user_id']);

        $userpoints =   UserPoint::where('user_id',$this->inputRequest['user_id'])->where('owner_user_id',$this->inputRequest['owner_user_id'])->where('point_type',$this->inputRequest['point_type']);
        //->where(function($q) use ($video_tape_id,$channel_id) {
          if($video_tape_id && $video_tape_id != 0) {
            $userpoints->where('video_tape_id', $video_tape_id);
          } else if( $video_tape_id && $channel_id != 0) {
            $userpoints->where('channel_id', $channel_id);
          }
      if($this->inputRequest['point_type'] == POINT_TYPE_TIP_CREDIT || $this->inputRequest['point_type'] == POINT_TYPE_TIP_ME){
           $userpointss = 0;
             Log::info("User POINT_TYPE_TIP_ME or POINT_TYPE_TIP_CREDIT");

        }else{
           $userpointss = $userpoints->count();
        }
       
        
        if($userpointss == 0){
        if($user_details) {

            $user_point_details = new UserPoint;
                                  
            $user_point_details->user_id = $this->inputRequest['user_id'];

            $user_point_details->owner_user_id = $this->inputRequest['owner_user_id'];

            $user_point_details->point_type = $this->inputRequest['point_type'];

            $user_point_details->video_tape_id = isset($this->inputRequest['video_tape_id']) ? $this->inputRequest['video_tape_id'] : 0;

            $user_point_details->channel_id = isset($this->inputRequest['channel_id']) ? $this->inputRequest['channel_id'] : 0;

            $user_points = Setting::get('user_points') ?: 0;

            if($this->inputRequest['point_type'] == POINT_TYPE_DISLIKE_VIDEO || $this->inputRequest['point_type'] == POINT_TYPE_TIP_CREDIT) {

                $user_points = -(int) $user_points;
                
            }

            $user_point_details->points = $user_points;

            $user_point_details->save();

            $user_details->total_points += $user_points;

            $user_details->save();

        } else {

            Log::info("User details not found");

        }
    }else{
         Log::info("User already gave points for this video");
    }
    }
 public function purchase_point($inputRequest){

          $this->inputRequest = $inputRequest;
    
            $user_details = User::find($this->inputRequest['owner_user_id']);
            $user_point_details = new UserPoint;
               $user_points = $this->inputRequest['points'] ?: 0;                   
            // $user_point_details->user_id = $this->inputRequest['user_id'];

            $user_point_details->owner_user_id = $this->inputRequest['owner_user_id'];

            $user_point_details->point_type = POINT_TYPE_TOKEN_PURCHASE;

           $user_point_details->product_id = isset($this->inputRequest['product_id']) ? $this->inputRequest['product_id'] : 0;
             if($this->inputRequest['point_type'] == POINT_TYPE_POINT_USE) {

                $user_points = -(int) $user_points;
                
            }

              
             $user_point_details->points = $user_points;

            $user_point_details->save();

            $user_details->total_points += $user_points;

            $user_details->save();
  
 }

}
