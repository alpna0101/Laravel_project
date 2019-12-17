<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\VideoTapeRepository as VideoRepo;

use App\Jobs\UserPointJob;

use App\Http\Requests;

use App\Helpers\Helper;

use App\Settings;

use App\User;

use App\Wishlist;

use App\Page;

use App\Flag;

use App\Admin;

use Auth;

use DB;

use Validator;

use View;

use Setting;

use Exception;

use App\ChatMessage;

use Log;

use App\PayPerView;

use App\Card;

use App\BannerAd;

use App\Subscription;

use App\Channel;

use App\VideoTape;

use App\VideoTapeImage;

use App\Repositories\CommonRepository as CommonRepo;

use App\ChannelSubscription;

use App\UserPayment;

use App\Category;

use App\VideoTapeTag;

use App\Tag;

use App\LiveVideo;

use App\Viewer;

use App\LiveVideoPayment;

use App\UserReferrer;

use App\Referral;

use App\UserSubscription;

use App\UserSubscriptionPayment;

class UserController extends Controller {

    protected $UserAPI;

    protected $Paypal;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $API, Request $request)
    {   

        // print_r(route('user.live_video.start_broadcasting'));

        $this->UserAPI = $API;

        $this->middleware(['auth'], ['except' => [
                'master_login',
                'index',
                'single_video',
                'contact',
                'trending', 
                'channels', 
                'add_history', 
                'page_view', 
                'channel_list', 
                'watch_count', 
                'partialVideos', 
                'payment_mgmt_videos', 
                'forgot_password' ,
                'channel_videos',
                'categories_view',
                'categories_videos',
                'categories_channels',
                'custom_live_videos',
                'single_custom_live_video',
                'tags_videos',
                'all_categories',
                'category_videos',
                'sub_category_videos',
                'android_web_page',
                'live_videos',
                'broadcasting',
                'referrals_signup'

        ]]);

        $this->middleware(['verifyUser'], ['except' => [
            'forgot_password'
        ]]);

    }


    public function deleteStreaming() {

        $model = LiveVideo::where('user_id',Auth::user()->id)->where('status', 0)->get();

        if (count($model) > 0) {

            Log::info("Logged In user id".Auth::user()->id);

             // Log::info("Model".print_r($model, true));

            foreach ($model as $key => $value) {

                Log::info("Usr Id".print_r($value->user_id,true));

                    
                if ($value->is_streaming) { 

                    Log::info("deleteStreaming");

                    // $value->status = DEFAULT_TRUE;

                    $value->save();

                } else {

                    $value->delete();

                }

            }

        }


    }


    public function broadcast(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
        ]);        

        $response = $this->UserAPI->broadcast($request)->getData();


        if ($response->success) {

           return redirect(route('user.live_video.start_broadcasting', array('id'=>$response->data->unique_id,'c_id'=>$response->data->channel_id)))->with('flash_success', tr('video_going_to_broadcast'));

            // return redirect(route('user.android.video', array('u_id'=>$response->data->unique_id,'c_id'=>$response->data->channel_id, 'id'=>\Auth::user()->id)));
        } else {

            return back()->with('flash_error', $response->error_messages);

        }

    }


    public function broadcasting(Request $request) {


        if ($request->id) {


            $model = LiveVideo::where('unique_id', $request->id)
                        ->where('status', '!=', DEFAULT_TRUE)
                       // ->where('user_id', Auth::user()->id)
                        ->first();
    
            if ($model) {

                // $delete_videos = LiveVideo::

                $videoPayment = null;


                if (Auth::check()) {

                    // if(!count($subscription)) {

                    //     return redirect(route('user.dashboard'))->with('flash_error', tr('no_subscription_found'));
                    // }

                    $userModel = User::find(Auth::user()->id);

                    if ($model->user_id != $userModel->id) {



                             // Load Viewers model

                                $viewer = Viewer::where('video_id', $model->id)->where('user_id',Auth::user()->id)->first();

                                $new_user = 0;

                                if(!$viewer) {

                                    $new_user = 1;

                                    $viewer = new Viewer;

                                    $viewer->video_id = $model->id;

                                    $viewer->user_id = Auth::user()->id;
                                }

                                $viewer->count = ($viewer->count) ? $viewer->count + 1 : 1;

                                $viewer->save();

                                if ($new_user) {

                                    if ($model) {

                                        Log::info("test");

                                        $model->viewer_cnt += 1;

                                        $model->save();
                                        
                                    }

                                }

                            // video payment 

                            $videoPayment = LiveVideoPayment::where('live_video_id', $model->id)
                                ->where('live_video_viewer_id', Auth::user()->id)
                                ->where('status',DEFAULT_TRUE)->first();



                        
                    }

                    $appSettings = json_encode([
                        'SOCKET_URL' => Setting::get('SOCKET_URL'),
                        'CHAT_ROOM_ID' => isset($model) ? $model->id : null,
                        'BASE_URL' => Setting::get('BASE_URL'),
                        'TURN_CONFIG' => [],
                        'TOKEN' =>  ($model->user_id == $userModel->id) ? Auth::user()->token : null,
                        'USER_PICTURE'=>$userModel->chat_picture,
                        'NAME'=>$userModel->name,
                        'CLASS'=>'left',
                        'USER' => ($model->user_id == $userModel->id) ? ['id' => $userModel->id, 'role' => "model"] : null,
                        'VIDEO_PAYMENT'=>($videoPayment) ? $videoPayment : null,
                    ]);

                    $comments = ChatMessage::where('live_video_id', $model->id)->get();

                } else {

                    $model->viewer_cnt += 1;

                    $model->save();

                    $appSettings = json_encode([
                        'SOCKET_URL' => Setting::get('SOCKET_URL'),
                        'CHAT_ROOM_ID' => isset($model) ? $model->id : null,
                        'BASE_URL' => Setting::get('BASE_URL'),
                        'TURN_CONFIG' => [],
                        'TOKEN' =>  null,
                        'USER_PICTURE'=>$model->user->chat_picture,
                        'NAME'=>$model->user->name,
                        'CLASS'=>'left',
                        'USER' => null,
                        'VIDEO_PAYMENT'=>($videoPayment) ? $videoPayment : null,
                    ]);

                    $comments = null;

                }


                $query = LiveVideo::where('is_streaming', DEFAULT_TRUE)
                    ->where('status', 0)->whereNotIn('id', [$model->id]);

                if (Auth::check()) {

                    $query->whereNotIn('user_id', [Auth::user()->id]);

                }

                $videos = $query->paginate(15);



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

                $model->video_url = $video_url;

                return view('user.videos.live-video')->with('page', 'live-video')
                    ->with('subPage', 'broadcast')
                    ->with('data', $model)->with('appSettings', $appSettings)->with('comments',$comments)->with('videos', $videos);


            } else {

                return redirect(route('user.channel', ['id'=>$request->c_id]))->with('flash_error', tr('no_live_video_found'));

            }

        } else {

            if ($request->c_id) {

                return redirect(route('user.channel', ['id'=>$request->c_id]))->with('flash_error', tr('id_not_matching'));

            } else {

                return redirect(route('user.dashboard'))->with('flash_error', tr('something_error'));

            }


        }

    }


    public function stop_streaming(Request $request) {

        $model = LiveVideo::find($request->id);

        $model->status = DEFAULT_TRUE;

        if(Auth::check()) {

            if ($model->user_id == Auth::user()->id) {

                $model->end_time = getUserTime(date('H:i:s'), ($model->user) ? $model->user->timezone : '', "H:i:s");

                $model->no_of_minutes = getMinutesBetweenTime($model->start_time, $model->end_time);

                $message =  tr('streaming_stopped_success');


                if (Setting::get('wowza_server_url')) {

                    $this->UserAPI->disConnectStream($model->user_id.'-'.$model->id);

                }

                

                $route = route('user.channel', ['id'=>$model->channel_id]);

            } else {

                $message = tr('no_more_video_available');

                $route = route('user.live_videos');
            }

        } else {

            $message = tr('no_more_video_available');

            $route = route('user.live_videos');

        }

        if ($model->save()) {

            

        }

        return redirect($route)->with('flash_success',$message);
    }


    public function live_videos(Request $request) {

        $query = LiveVideo::where('is_streaming', DEFAULT_TRUE)
                    ->where('status', 0);

        if (Auth::check()) {

            $query->whereNotIn('user_id', [Auth::user()->id]);

        }

        $videos = $query->paginate(15);

        return view('user.videos.live_videos_list')
                ->with('videos', $videos)
                ->with('page', 'live_videos')
                ->with('subPage', 'live_videos');

    }

    public function setCaptureImage(Request $req, $roomId) {
        //TODO - allow model of this room only

        $data = explode(',', $req->get('base64'));

        if ($data[1] != '') {
            file_put_contents(join(DIRECTORY_SEPARATOR, [public_path(), 'uploads', 'rooms', $roomId . '.png']), base64_decode($data[1]));
            $model = LiveVideo::find($roomId);
            $model->snapshot = Helper::web_url()."/uploads/rooms/".$roomId . '.png';
            $model->save();

            if ($model->save()) {
                return response()->json(true,200);
            } else {
                return response()->json(false,200);
            }
        }
         
    }


    public function get_viewer_cnt(Request $request) {

        $model = LiveVideo::find($request->id);

        if ($model) {

            $viewer_cnt = $model->viewer_cnt;

        } else {

            $viewer_cnt = 0;

        }

        return response()->json(['viewer_cnt'=>$viewer_cnt, 'model'=>$model]);

    }


    public function payment_url(Request $request) {

        $id = $request->id;

        $user_id = $request->user_id;

        if (!Auth::check() || !$user_id) {

            return redirect(route('user.login.form'));

        } else {

            $video_payment = LiveVideoPayment::where('live_video_viewer_id' , $user_id)->where('live_video_id' , $id)->where('status', DEFAULT_TRUE)->first();

            if ($video_payment) {

                return redirect(route('user.live_video.start_broadcasting', array('id'=>$video_payment->getVideo->unique_id, 'c_id'=>$video_payment->getVideo->channel_id)));


            }

            return redirect(route('user.live-video.invoice', array('id'=>$id)));

           /* if (Setting::get('payment_type') == 'stripe') {

                return redirect(route('user.stripe_payment_video', array('id'=>$id, 'user_id'=>$user_id)));

            } else {

                return redirect(route('user.live_video_paypal', array('id'=>$id, 'user_id'=>$user_id)));
            }*/
        }

    }

    public function live_videos_payment_url(Request $request) {

        $id = $request->id;

        $user_id = Auth::check() ? Auth::user()->id : '';

        if (!Auth::check() || !$user_id) {

            return redirect(route('user.login.form'));

        } else {

            $video_payment = LiveVideoPayment::where('live_video_viewer_id' , $user_id)->where('live_video_id' , $id)->where('status', DEFAULT_TRUE)->first();

            if ($video_payment) {

                return redirect(route('user.live_video.start_broadcasting', array('id'=>$video_payment->getVideo->unique_id, 'c_id'=>$video_payment->getVideo->channel_id)));


            }

            $coupon_code = $request->coupon_code ?  $request->coupon_code : '';
            //return redirect(route('user.live-video.invoice', array('id'=>$id)));

            if ($request->payment_type == 2) {

                return redirect(route('user.stripe_payment_video', array('id'=>$id, 'user_id'=>$user_id, 'coupon_code'=>$coupon_code)));

            } else {

                return redirect(route('user.live_video_paypal', array('id'=>$id, 'user_id'=>$user_id, 'coupon_code'=>$coupon_code)));
            }
        }

    }


    public function stripe_payment_video(Request $request) {


        $request->request->add([

            'video_id'=>$request->id,

            'id'=>Auth::check() ? Auth::user()->id : '',

        ]);

        $response = $this->UserAPI->stripe_live_ppv($request)->getData();

        if ($response->success) {


            $video_payment = LiveVideoPayment::where('live_video_viewer_id' , $request->id)->where('live_video_id' , $request->video_id)->where('status', DEFAULT_TRUE)->first();

            return redirect(route('user.live_video.start_broadcasting', array('id'=>$video_payment->getVideo->unique_id, 'c_id'=>$video_payment->getVideo->channel_id)));

            //return redirect(route(''));

        } else {

            return back()->with('flash_error', $response->error_messages);
        }

        if (\Auth::user()->card_id) {

            $user_card = Card::find(Auth::user()->card_id);

            if ($user_card && $user_card->is_default) {

                $video = LiveVideo::find($request->id);

                if($video && !$video->status && $video->is_streaming) {

                    $total = $video->amount;

                    // Get the key from settings table
                    $stripe_secret_key = Setting::get('stripe_secret_key');

                    $customer_id = $user_card->customer_id;
                    
                    if($stripe_secret_key) {

                        \Stripe\Stripe::setApiKey($stripe_secret_key);
                    } else {

                        // $response_array = array('success' => false, 'error' => Helper::error_message(902) , 'error_code' => 902);

                       // return response()->json($response_array , 200);

                        return back()->with('flash_error', Helper::get_error_message(902));
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
                            $user_payment->live_video_viewer_id = Auth::user()->id;
                            $user_payment->user_id = $video->user_id;
                            $user_payment->live_video_id = $video->id;
                            $user_payment->status = 1;
                            $user_payment->amount = $amount;
                            // $user_payment->save();

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

                                add_to_redeem($user->id, $user_amount);
                            
                            }



                            return redirect(route('user.live_video.start_broadcasting',array('id'=>$video->unique_id, 'c_id'=>$video->channel_id)));

                        } else {

                            return back()->with('flash_error', Helper::get_error_message(903));

                        }
                    
                    } catch (\Stripe\StripeInvalidRequestError $e) {

                        Log::info(print_r($e,true));

                        /*$response_array = array('success' => false , 'error' => Helper::get_error_message(903) ,'error_code' => 903);*/

                        return back()->with('flash_error', Helper::get_error_message(903));

                       // return response()->json($response_array , 200);
                    
                    }

                
                } else {

                    return back()->with('flash_error', tr('no_live_video_found'));
                    
                }


            } else {

                return back()->with('flash_error', tr('no_default_card_available'));

            }

        } else {

            return back()->with('flash_error', tr('no_default_card_available'));

        }
        

    }

    public function delete_video($id, $user_id) {

        // Load Model
        $model = LiveVideo::find($id);

        if ($model) {

            if ($model->user_id == $user_id) {

                if ($model->is_streaming) {

                    $model->status = DEFAULT_TRUE;

                    $model->end_time = getUserTime(date('H:i:s'), ($model->user) ? $model->user->timezone : '', "H:i:s");

                    // $model->no_of_

                    if ($model->save()) {

                       
                    } else {

                        $response_array = ['success'=>false, 'error_messages'=>tr('went_wrong')];

                    }

                    $response_array = ['success'=>true];

                }

            } else {

                $response_array = ['success'=>false, 'error_messages'=> tr('not_authorized_person')];

            }
            
        } else {

            $response_array = ['success'=>false, 'error_messages'=> tr('no_live_video_present')];

        }

        return response()->json($response_array);

    }

    public function live_history(Request $request) {

        $request->request->add([ 
            'id'=>Auth::user()->id,
            'token'=>Auth::user()->token,
            'device_type'=>DEVICE_WEB,
        ]); 

        $response = $this->UserAPI->live_history($request)->getData();

        if ($response->success) {

            return view('user.history.live_history')->with('page', 'history')
                ->with('subPage', 'live_history')
                ->with('response', $response);


        } else {

            return back()->with('flash_error', $response->error_messages);
        }

    }

    public function live_mgmt_videos(Request $request) {

        // Get Videos

        // $videos = VideoRepo::channel_videos($request->channel_id, null, $request->skip);

       // $payment_videos = VideoRepo::payment_videos($request->channel_id, null, $request->skip);

        $live_video_history = $this->UserAPI->live_video_revenue($request)->getData();


        $view = View::make('user.videos.partial_live_video_history')
                    ->with('live_video_history', $live_video_history)->render();

        return response()->json(['view'=>$view, 'length'=>count($live_video_history->data)]);
    }

    public function android_web_page(Request $request) {

        if ($request->u_id) {

            $model = LiveVideo::where('unique_id', $request->u_id)
                        ->where('status', '!=', DEFAULT_TRUE)
                       // ->where('user_id', Auth::user()->id)
                        ->first();
    
            if ($model) {

                Auth::loginUsingId($request->id);

                // $delete_videos = LiveVideo::

                $videoPayment = null;


                if (Auth::check()) {

                    // $usrModel

                    /*$userModel = User::find(Auth::user()->id);

                    if ($model->user_id != $userModel->id) {

                            // Load Viewers model

                            $viewer = Viewer::where('video_id', $model->id)->where('user_id', Auth::user()->id)->first();

                            if(!$viewer) {

                                $viewer = new Viewer;

                                $viewer->video_id = $model->id;

                                $viewer->user_id = Auth::user()->id;

                            }

                            $viewer->count = ($viewer->count) ? $viewer->count + 1 : 1;

                            $viewer->save();

                            if ($viewer) {

                                $model->viewer_cnt += 1;

                                $model->save();

                            }
                            // video payment 

                            $videoPayment = LiveVideoPayment::where('live_video_id', $model->id)
                                ->where('live_video_viewer_id', Auth::user()->id)
                                ->where('status',DEFAULT_TRUE)->first();
                            

                    }*/

                  


                } else {

                    $model->viewer_cnt += 1;

                    $model->save();
                }


            } else {

                Log::info(tr('no_live_video_found'));

            }

        } else {

            if ($request->c_id) {

                Log::info(tr('id_not_matching'));

            } else {

                Log::info(tr('something_error'));

            }


        }

        $model->video_url = "";

        return view('user.android.android-video')->with('data', $model)->with('page', '')->with('sub_page','');
    }

    /**
     * Function Name : master_login()
     *
     * To Activate Super user by admin
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return with Success/Failure Message
     */
    public function master_login(Request $request) {

        try {

            DB::beginTransaction();

            if (Auth::guard('admin')->check()) {

                // Get current login admin details

                $master_user_id = Auth::guard('admin')->user()->user_id;

                // Check the admin has logged in

                if($master_user_id != null || $master_user_id != "") {

                    // Check already record exists

                    $check_admin_user_details = User::where('email' , Auth::guard('admin')->user()->email)->first();

                    if($check_admin_user_details) {

                        //$check_admin_user_details->token = AppJwt::create(['id' => $check_admin_user_details->id, 'email' => $check_admin_user_details->email, 'role' => "model"]);

                        $check_admin_user_details->token = Helper::generate_token();

                        $check_admin_user_details->is_master_user = 1;

                        $check_admin_user_details->role = "model";

                        if ($check_admin_user_details->save()) {


                        } else {

                            throw new Exception(tr('user_details_not_saved'));
                            
                        }

                    } else {

                        throw new Exception(tr('user_details_not_saved'));
                    }

                } else {

                    $check_admin_user_details = new User;

                    $check_admin_user_details->name = "Master User";

                    $check_admin_user_details->email = Auth::guard('admin')->user()->email;

                    $check_admin_user_details->password = \Hash::make("123456");

                    $check_admin_user_details->user_type = $check_admin_user_details->is_master_user = $check_admin_user_details->is_verified = $check_admin_user_details->status = 1;

                    $check_admin_user_details->device_type = WEB;

                    $check_admin_user_details->role = "model";

                    if ($check_admin_user_details->save()) {

                        $admin = Admin::where('email',  Auth::guard('admin')->user()->email)->first();

                        if ($admin) {

                            $admin->user_id = $check_admin_user_details->id;
                           
                            $admin->save();
                        }   

                        //$check_admin_user_details->token = AppJwt::create(['id' => $check_admin_user_details->id, 'email' => $check_admin_user_details->email, 'role' => "model"]);

                        $check_admin_user_details->token = Helper::generate_token();
                        
                        $check_admin_user_details->save();

                    } else {

                        throw new Exception(tr('user_details_not_saved'));
                    }

                }

                
                $master_user_id = $check_admin_user_details->id;

                $master_user_details = User::find($master_user_id);

                // If master user details is not empty -> Login the admin as user

                if($master_user_details) {

                    Auth::loginUsingId($master_user_id, true);

                } else {

                    throw new Exception(tr('user_not_found'));

                }

            } else {

                throw new Exception(tr('admin_not_logged_in'));

            }

            DB::commit();

            return redirect()->to('/')->with('flash_success', tr('master_login_success'));

        } catch(Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            return back()->with('flash_error', $e);

        }

    }

    /**
     * Function Name : index()
     *
     * Show the user dashboard.
     * 
     * @created_by Shobana
     *
     * @updated_by - 
     * 
     * @param Object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {


        Log::info("Timezone ".print_r(date('Y-m-d H:i:s'), true));

        Log::info("Convert Timezone ".print_r(convertTimeToUSERzone(date('Y-m-d H:i:s'), 'Europe/London', 'Y-m-d H:i:s'), true));


        $database = config('database.connections.mysql.database');
        
        $username = config('database.connections.mysql.username');

        if($database && $username && Setting::get('installation_process') == 2) {

            counter('home');

            $watch_lists = $wishlists = array();

            if (Auth::check()) {
                
                $request->request->add([ 
                    'id'=>\Auth::user()->id,
                    'age' => \Auth::user()->age_limit,
                ]);   
            }

            if($request->has('id')){

                $wishlists = $this->UserAPI->wishlist_list($request)->getData();

                $watch_lists = $this->UserAPI->watch_list($request)->getData();  
            }


            $recent_videos = $this->UserAPI->recently_added($request)->getData();

            $trendings = $this->UserAPI->trending_list($request)->getData();
            
            $suggestions  = $this->UserAPI->suggestion_videos($request)->getData();

            $channels = getChannels(WEB);

            $banner_videos = [];

            if (Setting::get('is_banner_video')) {

                $banner_videos = VideoTape::select('id as video_tape_id', 'banner_image as image', 'title as video_title', 'description as content')
                                ->where('video_tapes.is_banner' , 1 )
                                ->where('video_tapes.status', DEFAULT_TRUE)
                                ->orderBy('video_tapes.created_at' , 'desc')
                                ->get();
            }

            $banner_ads = [];

            if(Setting::get('is_banner_ad')) {

                $banner_ads = BannerAd::select('id as banner_id', 'file as image', 'title as video_title', 'description as content', 'link')
                            ->where('banner_ads.status', DEFAULT_TRUE)
                            ->orderBy('banner_ads.created_at' , 'desc')
                            ->get();

            }

            return view('user.index')
                        ->with('page' , 'home')
                        ->with('subPage' , 'home')
                        ->with('wishlists' , $wishlists)
                        ->with('recent_videos' , $recent_videos)
                        ->with('trendings' , $trendings)
                        ->with('watch_lists' , $watch_lists)
                        ->with('suggestions' , $suggestions)
                        ->with('channels' , $channels)
                        ->with('banner_videos', $banner_videos)
                        ->with('banner_ads', $banner_ads);
        } else {

            return redirect()->route('installTheme');

        }
        
    }


    /**
     * Function Name : trending()
     *
     * To list out videos based on the watching count
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param object $request - User Details
     *
     * @return video details
     */
    public function trending(Request $request) {

        if (Auth::check()) {

            $request->request->add([ 
                'id' => \Auth::user()->id,
                'token' => \Auth::user()->token,
                'device_token' => \Auth::user()->device_token,
                'age'=>\Auth::user()->age_limit,
            ]);

        }

        $trending = $this->UserAPI->trending_list($request)->getData();

        return view('user.trending')->with('page', 'trending')
                                    ->with('videos',$trending);
    
    }

    /**
     * Function Name : channels()
     *
     * To list out channels which is created by all the users
     *
     * @param object $request - User Details
     *
     * @return channel details details
     */
    public function channels(Request $request){

        if(Auth::check()) {

            $request->request->add([ 
                'id' => \Auth::user()->id,
                'token' => \Auth::user()->token,
                'device_token' => \Auth::user()->device_token,
                'age'=>\Auth::user()->age_limit,
            ]);

        }


        $response = $this->UserAPI->channel_list($request)->getData();


        return view('user.channels.list')->with('page', 'channels')
                ->with('subPage', 'channel_list')
                ->with('response', $response);

    }

    /**
     * Function Name : history()
     *
     * To list out history of user based
     *
     * @param object $request - User Details
     *
     * @return array of history 
     */
    public function history(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'age'=>\Auth::user()->age_limit,
        ]);

        $histories = $this->UserAPI->watch_list($request)->getData();

        return view('user.account.history')
                        ->with('page' , 'history')
                        ->with('subPage' , 'user-history')
                        ->with('histories' , $histories);
    
    }


    /**
     * Function Name : wishlist()
     *
     * To list out wishlist of user based
     *
     * @param object $request - User Details
     *
     * @return array of wishlist 
     */
    public function wishlist(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'age'=>\Auth::user()->age_limit,
        ]);
        
        $videos = $this->UserAPI->wishlist_list($request)->getData();

        return view('user.account.wishlist')
                    ->with('page' , 'wishlist')
                    ->with('subPage' , 'user-wishlist')
                    ->with('videos' , $videos);
    
    }

    /**
     * Function Name : channel_videos()
     *
     * Based on the channel id , channel related videos will display
     *
     * @param integer $id : Channel Id
     *
     * @return channel videos list
     */
    public function channel_videos($id, Request $request) {

        $channel = Channel::where('id', $id)->first();

        if ($channel) {

            $request->request->add([ 
                'age' => \Auth::check() ? \Auth::user()->age_limit : "",
                'id'=> \Auth::check() ? \Auth::user()->id : "",
            ]);

            if ($request->id != $channel->user_id || !Auth::check()) {

                if ($channel->status == USER_CHANNEL_DECLINED_STATUS || $channel->is_approved == ADMIN_CHANNEL_DECLINED_STATUS) {

                    return redirect()->to('/')->with('flash_error', tr('channel_declined'));

                }
 
            }

            $videos = $this->UserAPI->channel_videos($id, 0 , $request)->getData();

            $channel_owner_id = Auth::check() ? ($channel->user_id == Auth::user()->id ? $channel->user_id : "") : "";

            $trending_videos = $this->UserAPI->channel_trending($id, 4 , $channel_owner_id , $request)->getData();

            $payment_videos = $this->UserAPI->payment_videos($id, 0)->getData();

            $live_videos = VideoRepo::live_videos_list($id, WEB, null);

            $subscribe_status = false;

            if ($request->id) {

                $subscribe_status = check_channel_status($request->id, $id);

            }

            $subscriberscnt = subscriberscnt($channel->id);

            $live_video_history = [];

            if (Auth::check()) {

                $request->request->add([
                    'skip'=>0,
                    'channel_id'=>$id,
                    'id'=>Auth::user()->id,

                ]);

                $live_video_history = $this->UserAPI->live_video_revenue($request)->getData();

            }

            return view('user.channels.index')
                        ->with('page' , 'channels_'.$id)
                        ->with('subPage' , 'channels')
                        ->with('channel' , $channel)
                        ->with('live_videos', $live_videos)
                        ->with('videos' , $videos)->with('trending_videos', $trending_videos)
                        ->with('payment_videos', $payment_videos)
                        ->with('subscribe_status', $subscribe_status)
                        ->with('subscriberscnt', $subscriberscnt)
                        ->with('live_video_history', $live_video_history);
        } else {

            return back()->with('flash_error', tr('channel_not_found'));

        }
    }

    /**
     * Function Name : single_video()
     * 
     * To view single video based on video id
     *
     * @param integer $request - Video id
     *
     * @return based on video displayed all the details'
     */
    public function single_video(Request $request) {

        $request->request->add([ 
                'video_tape_id' => $request->id,
        ]);

        if (Auth::check()) {

            $request->request->add([ 
                'id'=>Auth::user()->id,
                'age_limit'=>Auth::user()->age_limit,
            ]);

        } else {
             $request->request->add([ 
                'id'=> '',
            ]);
        }

        $data = $this->UserAPI->video_detail($request)->getData();

        if (isset($data->url)) {

            return redirect($data->url);
        }

        if ($data->success) {

            $response = $data->response_array;

            // Video is autoplaying ,so we are incrementing the watch count 

            if ($request->id != $response->video->channel_created_by) {

                $this->watch_count($request);

            }
        
            return view('user.single-video')
                        ->with('page' , '')
                        ->with('subPage' , '')
                        ->with('video' , $response->video)
                        ->with('comments' , $response->comments)
                        ->with('suggestions',$response->suggestions)
                        ->with('wishlist_status' , $response->wishlist_status)
                        ->with('history_status' , $response->history_status)
                        ->with('main_video' , $response->main_video)
                        ->with('url' , $response->main_video)
                        ->with('channels' , $response->channels)
                        ->with('report_video', $response->report_video)
                        ->with('videoPath', $response->videoPath)
                        ->with('video_pixels', $response->video_pixels)
                        ->with('videoStreamUrl', $response->videoStreamUrl)
                        ->with('hls_video' , $response->hls_video)
                        ->with('flaggedVideo', $response->flaggedVideo)
                        ->with('ads', $response->ads)
                        ->with('subscribe_status', $response->subscribe_status)
                        ->with('like_count',$response->like_count)
                        ->with('dislike_count',$response->dislike_count)
                        ->with('subscriberscnt', $response->subscriberscnt)
                        ->with('comment_rating_status', $response->comment_rating_status)
                        ->with('embed_link', $response->embed_link)
                        ->with('tags', $response->tags);
       
        } else {

            if($data->error_code == 5001) {

                $video_tape_details = VideoTape::find($request->video_tape_id);

                return redirect()->route('user.user_subscriptions.index', ['type' => 'subscribe', 'channel_id' => $video_tape_details->channel_id])->with('flash_error', $data->error_messages);

            }

            $error_message = isset($data->error_messages) ? $data->error_messages : tr('something_error');

            return back()->with('flash_error', $error_message);
            
        } 
    }


    /**
     * Function Name : profile()
     *
     * Show the profile list.
     *
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {

        if ($request->id) {

            $id = $request->id;
           

        } else {

            $id = Auth::user()->id;
            
        }

         $user = User::find($id);

         $request->request->add([ 
                'id' => $user->id,
                'token' => $user->token,
                'device_token' => $user->device_token,
                'age'=>$user->age_limit,
            ]);

        $wishlist = $this->UserAPI->wishlist_list($request)->getData();

        return view('user.account.profile')
                    ->with('page' , 'profile')
                    ->with('user', $user)
                    ->with('subPage' , 'user-profile')->with('wishlist', $wishlist);
    }

    /**
     * Function Name : update_profile() 
     *
     * Edit profile user details
     * 
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request){

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'age'=>\Auth::user()->age_limit,
        ]);

        $wishlist = $this->UserAPI->wishlist_list($request)->getData();

        return view('user.account.edit-profile')->with('page' , 'profile')
                    ->with('subPage' , 'user-update-profile')
                    ->with('wishlist', $wishlist);
    
    }

    /**
     * Function Name : update_profile() 
     *
     * Save any changes to the users profile.
     * 
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function profile_save(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
        ]);

        $response = $this->UserAPI->update_profile($request)->getData();

        if($response->success) {

            return redirect(route('user.profile'))->with('flash_success' , tr('profile_updated'));

        } else {

            $message = isset($response->error) ? $response->error : " "." ".$response->error_messages;

            return back()->with('flash_error' , $message);
        }
    
    }

    /**
     * Function Name : profile_save_password() 
     * 
     * Save changed password.
     * 
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function profile_save_password(Request $request) {
        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
        ]);

        $response = $this->UserAPI->change_password($request)->getData();

        if($response->success) {

            return back()->with('flash_success' , tr('password_success'));

        } else {

            $message = $response->error." ".$response->error_messages;

            return back()->with('flash_error' , $message);
        }
    
    }


    /**
     * Function Name : profile_change_password() 
     * 
     * Display only password change form
     * 
     * @param object $request - User Details
     *
     * @return \Illuminate\Http\Response
     */
    public function profile_change_password(Request $request) {

        return view('user.account.change-password')->with('page' , 'profile')
                    ->with('subPage' , 'user-change-password');

    }

    /**
     * Function Name : add_history()
     *
     * To Add in history based on user, once he complete the video , the video will save
     *
     * @param Integer $request - Video Id
     *
     * @return response of Boolean with message
     */
    public function add_history(Request $request) {

        if(Auth::check()) {
            $request->request->add([ 
                'id' => \Auth::user()->id,
                'token' => \Auth::user()->token,
                'device_token' => \Auth::user()->device_token,
                'video_tape_id' => $request->video_tape_id
            ]);
        }

        $response = $this->UserAPI->add_history($request)->getData();

        if($response->success) {

            $response->message = Helper::get_message(118);

        } else {

            $response->success = false;

            $response->message = tr('something_error');

        }

        $response->status = $request->status;

        return response()->json($response);
    
    }
 
    /**
     * Function Name : watch_count()
     *
     * To save watch count when ever user see the video
     *
     * @param Integer $request - Video Tape Id
     *
     * @return response of boolean
     */
    public function watch_count(Request $request) {

        if($video = VideoTape::where('id',$request->video_tape_id)
                ->where('status',1)
                ->where('video_tapes.is_approved' , 1)
                ->first()) {

            \Log::info("ADD History - Watch Count Start");

            $user_id = Auth::check() ? Auth::user()->id : 0;

            if($video->getVideoAds) {

                \Log::info("getVideoAds Relation Checked");

                if ($video->getVideoAds->status) {

                    \Log::info("getVideoAds Status Checked");

                    // User logged in or not

                    if ($user_id) {

                        if ($video->user_id != $user_id) {

                            // Check the video view count reached admin viewers count, to add amount for each view

                            if ($video->user_id != Auth::user()->id) {


                                if($video->watch_count >= Setting::get('viewers_count_per_video') && $video->ad_status) {

                                    \Log::info("Check the video view count reached admin viewers count, to add amount for each view");

                                    $video_amount = Setting::get('amount_per_video');

                                    // $video->watch_count = $video->watch_count + 1;

                                    $video->amount += $video_amount;

                                    add_to_redeem($video->user_id , $video_amount);

                                    \Log::info("ADD History - add_to_redeem");


                                } else {

                                    \Log::info("ADD History - NO REDEEM");

                                    // $video->redeem_count += 1;

                                    // $video->watch_count = $video->watch_count + 1;
                                }

                            }

                        }

                    }

                }
            }

            $video->watch_count += 1;

            $video->save();

            \Log::info("ADD History - Watch Count Start completed");

            return response()->json(['success'=>true, 
                    'data'=>['watch_count'=>number_format_short($video->watch_count)]]);

        } else {

            return response()->json(['success'=>false]);
        }

    }

    /**
     * Function Name : delete_history()
     *
     * To delete a history based on logged in user id
     *
     * @param integer $request - Video Tape Id
     *
     * @return response of success/falure message
     */
    public function delete_history(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token
        ]);

        $response = $this->UserAPI->delete_history($request)->getData();

        if($response->success) {

            return back()->with('flash_success' , Helper::get_message(121));

        } else {

            return back()->with('flash_error' , tr('admin_not_error'));

        }
    
    }


    /**
     * Function Name : add_wishlist()
     *
     * Add a wishlist based on logged in user id
     *
     * @param integer $request - Video Tape Id
     *
     * @return response of success/falure message
     */
    public function add_wishlist(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'video_tape_id' => $request->video_tape_id
        ]);

        $response = $this->UserAPI->add_wishlist($request)->getData();

        if($response->success) {

            $response->message = Helper::get_message(118);

        } else {

            $response->success = false;

            $response->message = tr('something_error');
        }

        $response->status = $request->status;

        return response()->json($response);
    }

    /**
     * Function Name : delete_wishlist()
     *
     * To delete wishlist based on user id
     * 
     * @param intger $request - Video tape id
     *
     * @return response of success/failure message
     */
    public function delete_wishlist(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token
        ]);

        $response = $this->UserAPI->delete_wishlist($request)->getData();

        if($response->success) {

            return back()->with('flash_success',tr('wishlist_removed'));

        } else {

            return back()->with('flash_error', tr('something_error'));
        }
    } 

    /**
     * Function Name : add_comment()
     * 
     * To Add comment based on single video
     *
     * @param integer $video_tape_id - Video Tape ID
     *
     * @return response of success/failure message
     */
    public function add_comment(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'video_tape_id'=>$request->video_tape_id
        ]);

        $response = $this->UserAPI->user_rating($request)->getData();

        if($response->success) {

            $response->message = Helper::get_message(118);

        } else {

            $response->success = false;

            $response->message = tr('something_error');
        }

        return response()->json($response);
    
    }

    public function comments(Request $request) {

        $videos = Helper::get_user_comments(\Auth::user()->id,WEB);

        return view('user.comments')
                    ->with('page' , 'profile')
                    ->with('subPage' , 'user-comments')
                    ->with('videos' , $videos);
    }

    /**
     * Function Name : channel_create()
     *
     * To create a channel based on logged in user id  (Form Rendering)
     *
     * @return respnse with flash message
     */
    public function channel_create() {
        
        $model = new Channel;

        $channels = getChannels(Auth::user()->id);

        if((count($channels) == 0 || Setting::get('multi_channel_status'))) {

            if (Auth::user()->user_type) {

                return view('user.channels.create')->with('page', 'channels')
                    ->with('subPage', 'create_channel')->with('model', $model);

            } else {

                return redirect(route('user.dashboard'))->with('flash_error', tr('subscription_error'));

            }

        } else {

            return redirect(route('user.dashboard'))->with('flash_error', tr('channel_create_error'));
        }

    }

    /**
     * Function Name : save_channel()
     *
     * To create a channel based on logged in user id
     *
     * @param Object $request - Channel Details
     *
     * @return respnse with flash message
     */
    public function save_channel(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'channel_id' =>$request->id,
            'device_type'=>DEVICE_WEB,
        ]);
       
        $response = CommonRepo::channel_save($request)->getData();

        if($response->success) {
            // $response->message = Helper::get_message(118);
            return redirect(route('user.channel', ['id'=>$response->data->id]))
                ->with('flash_success', $response->message);
        } else {
            
            return back()->with('flash_error', $response->error_messages);
        }

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
    public function channel_edit($id) {

        $model = Channel::find($id);

        if (Auth::check()) {

            if ($model) {

                if (Auth::user()->id != $model->user_id) {

                    return redirect(route('user.channel.mychannel'))->with('flash_error', tr('unauthroized_person'));

                }

            }

        }

        return view('user.channels.edit')->with('page', 'channels')
                    ->with('subPage', 'edit_channel')->with('model', $model);

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

        $channel = Channel::where('id' , $request->id)->first();

        if($channel) {  

            if (Auth::check()) {

                if (Auth::user()->id != $channel->user_id) {

                    return redirect(route('user.channel.mychannel'))->with('flash_error', tr('unauthroized_person'));

                }
                
            }     

            $channel->delete();

            return redirect(route('user.dashboard'))->with('flash_success',tr('channel_delete_success'));

        } else {

            return back()->with('flash_error',tr('something_error'));

        }

    }

    /**
     * Function Name : delete_account()
     *
     * To delete account , based on the user (Form Rendering)
     *
     * @param object $request - User Details
     *
     * @return response of success/failure message
     */
    public function delete_account(Request $request) {

        if(\Auth::user()->login_by == 'manual') {

            return view('user.account.delete-account')
                    ->with('page' , 'profile')
                    ->with('subPage' , 'delete-account');
        } else {

            return $this->delete_account_process($request);

        }
        
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
    public function delete_account_process(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token
        ]);

        $response = $this->UserAPI->delete_account($request)->getData();

        if($response->success) {
            
            return redirect(route('user.dashboard'))->with('flash_success', tr('user_account_delete_success'));

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

        return back()->with('flash_error', Helper::get_error_message(146));

    }


    /**
     * Function Name : save_report_videos
     * Save report videos based on user based
     *
     * @param object $request - Post Attributes
     *
     * @return flash message
     */
    public function save_report_video(Request $request) {
       //  try {
            // Validate the coming post values
        $validator = Validator::make($request->all(), [
            'video_tape_id' => 'required',
            'reason' => 'required',
        ]);
        // If validator Fails, redirect same with error values
        if ($validator->fails()) {
             //throw new Exception("error", tr('admin_published_video_failure'));
            return back()->with('flash_error', tr('admin_published_video_failure'));
        }
        // Assign Post request values into Data variable
        $data = $request->all();

        // include user_id index into the data varaible  "Auth::user()->id" -> Logged In user id
        $data['user_id'] = \Auth::user()->id;
        $data['status'] = DEFAULT_TRUE;
        // Save the values in DB
        if (Flag::create($data)) {
            return redirect('/')->with('flash_success', tr('report_video_success_msg'));
        } else {
            //throw new Exception("error", tr('admin_published_video_failure'));
            return back()->with('flash_error', tr('admin_published_video_failure'));
        }
        /*} catch (Exception $e) {
            return back()->with('flash_error', $e);
        }*/
    }

    /**
     * Function Name : remove_report_video()
     * Remove the video from spam folder and make it as unspam
     *
     * @param integer $id Flag id
     *
     * @return flash error/flash success
     */
    public function remove_report_video($id) {
        // Load Spam Video from flag section
        $model = Flag::where('video_tape_id', $id)->where('user_id', Auth::user()->id)->first();

        Log::info("Loaded Values : ".print_r($model, true));
        // If the flag model exists then delete the row
        if ($model) {
            Log::info("Loaded Values 1 : ".print_r($model, true));
            Log::info("Delete values :". print_r($model->delete()));
            $model->delete();
            return back()->with('flash_success', tr('unmark_report_video_success_msg'));
        } else {
            // throw new Exception("error", tr('admin_published_video_failure'));
            return back()->with('flash_error', tr('admin_published_video_failure'));
        }
    }

    /**
     * Function Name : spam_videos()
     * Based on logged in user load spam videos
     *
     * @return spam videos
     */
    public function spam_videos(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'age'=>\Auth::user()->age_limit,
        ]);
        // Get logged in user id

        $model = $this->UserAPI->spam_videos($request, 12)->getData();

        // Return array of values
        return view('user.account.spam_videos')->with('model' , $model)
                        ->with('page' , 'Profile')
                        ->with('subPage' , 'Spam Videos');
    }   


    public function subscriptions() {

        $query = Subscription::where('status', DEFAULT_TRUE);

        if(Auth::check()) {

            if(Auth::user()->zero_subscription_status) {

                $query->whereNotIn('amount', [0]);

            }

        }

        $model = $query->get();

        return view('user.account.subscriptions')->with('subscriptions', $model)->with('page', 'Profile')->with('subPage', 'Subscriptions');
    }

    public function ad_request(Request $request) {

        if($data = VideoTape::find($request->id)) {

            $data->ad_status  = $data->ad_status ? 0 : 1;

            if($data->save()) {

                if($data->getVideoAds) {

                    $data->getVideoAds->status = $data->ad_status;

                    $data->getVideoAds->save();
                }
            }

            return response()->json(['status'=>$data->ad_status, 'success'=>true], 200);

        } else {

            return response()->json(['success'=>false], 200);
            
        }
    }

    public function video_upload(Request $request) {

        $model = new VideoTape;

        $id = $request->id;

        $channel = '';

        if (Auth::check()) {

            $channel = Channel::where('user_id', Auth::user()->id)->where('id', $id)->first();

            if(!Auth::user()->user_type) {

                return redirect(route('user.dashboard'))->with('flash_error', tr('subscribe_to_continue_video'));

            }
            
        }

        if (!$channel) {

            return redirect(route('user.channel.mychannel'))->with('flash_error', tr('unauthroized_person'));
        }

        $categories_list = $this->UserAPI->categories_list($request)->getData();

        $tags = $this->UserAPI->tags_list($request)->getData()->data;

        return view('user.videos.create')->with('model', $model)->with('page', 'videos')
            ->with('subPage', 'upload_video')->with('id', $id)
            ->with('categories', $categories_list)
            ->with('tags', $tags);
    }


    public function video_edit(Request $request) {

        $model = VideoTape::find($request->id);

        if($model) {

            if (Auth::check()) {

                if (Auth::user()->id != $model->user_id) {

                    return redirect(route('user.channel.mychannel'))->with('flash_error', tr('unauthroized_person'));

                }
                
            }    

            $model->publish_time = $model->publish_time ? (($model->publish_time != '0000-00-00 00:00:00') ? date('d-m-Y H:i:s', strtotime($model->publish_time)) : null) : null;

            $categories_list = $this->UserAPI->categories_list($request)->getData();

            $tags = $this->UserAPI->tags_list($request)->getData()->data;

            $model->tag_id = VideoTapeTag::where('video_tape_id', $request->id)->where('status', TAG_APPROVE_STATUS)->get()->pluck('tag_id')->toArray();

            return view('user.videos.edit')->with('model', $model)->with('page', 'videos')
                ->with('subPage', 'upload_video')
                ->with('categories', $categories_list)
                ->with('tags', $tags);

        } else {

            return back()->with('flash_error', tr('video_not_found'));

        }
    }


    public function video_save(Request $request) {

        $response = CommonRepo::video_save($request)->getData();

        if ($response->success) {

            $view = '';

            if ($response->data->video_type == VIDEO_TYPE_UPLOAD) {

                $tape_images = VideoTapeImage::where('video_tape_id', $response->data->id)->get();

                $view = \View::make('user.videos.select_image')
                        ->with('model', $response)
                        ->with('tape_images', $tape_images)
                        ->render();

            }

            $message = tr('user_video_upload_success');

            // Check the video status 

            if($response->data->is_approved == DEFAULT_FALSE) {

                $message = tr('user_video_upload_waiting_for_admin_approval');

            }

            \Session::set('flash_message_ajax' , $message);

            return response()->json(['success'=>true, 'path'=>$view, 'data'=>$response->data , 'message' => 'Successfull uploaded'], 200);

        } else {

            return response()->json($response);

        }

    }   

    public function video_delete($id) {

        if($video = VideoTape::where('id' , $id)->first())  {

            if (Auth::check()) {

                if (Auth::user()->id != $video->user_id) {

                    return redirect(route('user.channel.mychannel'))->with('flash_error', tr('unauthroized_person'));

                }
                
            }    

            Helper::delete_picture($video->video, "/uploads/videos/");

            Helper::delete_picture($video->subtitle, "/uploads/subtitles/"); 

            if ($video->banner_image) {

                Helper::delete_picture($video->banner_image, "/uploads/images/");
            }

            Helper::delete_picture($video->default_image, "/uploads/images/");

            if ($video->video_path) {

                $explode = explode(',', $video->video_path);

                if (count($explode) > 0) {


                    foreach ($explode as $key => $exp) {


                        Helper::delete_picture($exp, "/uploads/videos/");

                    }

                }

                

            }

            $video->delete();
        }

        return back()->with('flash_success', tr('video_delete_success'));
    }  

    public function save_default_img(Request $request) {

        $response = CommonRepo::set_default_image($request)->getData();

        return response()->json($response);

    }

    public function upload_video_image(Request $request) {

        $response = CommonRepo::upload_video_image($request)->getData();

        return response()->json($response);
    }


    public function user_subscription_save($s_id, $u_id) {

        $response = CommonRepo::save_subscription_plan($s_id, $u_id)->getData();

        if($response->success) {

            return redirect()->route('user.subscriptions')->with('flash_success', $response->message);

        } else {

            return back()->with('flash_error', $response->message);

        }

    }

    public function get_images($id) {

        $response = CommonRepo::get_video_tape_images($id)->getData();

        $tape_images = VideoTapeImage::where('video_tape_id', $id)->get();

        $view = \View::make('user.videos.select_image')->with('model', $response)
            ->with('tape_images', $tape_images)->render();

        return response()->json(['path'=>$view, 'data'=>$response->data]);

    }  

    /**
     * Used to get the redeems
     *
     */

    public function redeems(Request $request) {

        return view('user.redeems.index');

    }

    /**
     * Send Request to admin
     *
     */

    public function send_redeem_request(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token
        ]);

        $response = $this->UserAPI->send_redeem_request($request)->getData();

        if($response->success) {

            return back()->with('flash_success', tr('send_redeem_request_success'));

        } else {

            return back()->with('flash_error', $response->error_messages);
        }

        return back()->with('flash_error', Helper::get_error_message(146));

    }

    /**
     * Send Request to admin
     *
     */

    public function redeem_request_cancel($id , Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'redeem_request_id' => $id,
        ]);

        $response = $this->UserAPI->redeem_request_cancel($request)->getData();

        if($response->success) {

            return back()->with('flash_success', tr('send_redeem_request_success'));

        } else {

            return back()->with('flash_error', $response->error);
        }

        return back()->with('flash_error', Helper::get_error_message(146));

    }

    public function page_view($id) {

        $page = Page::find($id);

        if (!$page) {

            return back()->with('flash_error', tr('no_page_found'));

        }

        return view('static.common')->with('model' , $page)
                        ->with('page' , $page->type)
                        ->with('subPage' , '');

    }

    public function subscribe_channel(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'user_id'     => 'required|exists:users,id',
                'channel_id'     => 'required|exists:channels,id',
                ));


        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $model = ChannelSubscription::where('user_id', $request->user_id)->where('channel_id',$request->channel_id)->first();

            if (!$model) {

                $model = new ChannelSubscription;

                $model->user_id = $request->user_id;

                $model->channel_id = $request->channel_id;

                $model->status = DEFAULT_TRUE;

                $model->save();

                // ADD POINTS TO THE OWNER USER start

                if($model) {

                    $channel_details = Channel::find($request->channel_id);

                    $inputRequest = [];

                    $inputRequest['user_id'] = Auth::user()->id;

                    $inputRequest['owner_user_id'] = $channel_details->user_id;

                    $inputRequest['point_type'] = POINT_TYPE_SUBSCRIBE_VIDEO;

                    $inputRequest['channel_id'] = $request->channel_id;

                    $inputRequest['points'] = Setting::get('user_points') ?: 0;

                    Log::info("UserPointJob - Subscribe channel".print_r($inputRequest, true));

                    dispatch(new UserPointJob($inputRequest)); 

                    $inputRequest['owner_user_id'] = $inputRequest['user_id'] = Auth::user()->id; 

                    dispatch(new UserPointJob($inputRequest));   

                }

                // ADD POINTS TO THE OWNER USER ends

                return back()->with('flash_success', tr('channel_subscribed'));

            } else {

                return back()->with('flash_error', tr('already_channel_subscribed'));

            }
        }
   
    }

    public function unsubscribe_channel(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'subscribe_id'     => 'required|exists:channel_subscriptions,id',
                ));


        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $model = ChannelSubscription::find($request->subscribe_id);

            if ($model) {

                $model->delete();

                return back()->with('flash_success', tr('channel_unsubscribed'));

            } else {

                return back()->with('flash_error', tr('not_found'));

            }
        }

    }


    public function likeVideo(Request $request)  {
        $request->request->add([
            'id' => Auth::user()->id,
            'token'=>Auth::user()->token
        ]);

        $response = $this->UserAPI->likevideo($request)->getData();

        // dd($response);
        return response()->json($response);

    }

    public function disLikeVideo(Request $request) {

        $request->request->add([ 
            'id' => Auth::user()->id,
            'token'=>Auth::user()->token
        ]);

        $response = $this->UserAPI->dislikevideo($request)->getData();

        return response()->json($response);

    }

    public function channel_subscribers(Request $request) {

        $list = [];

        $channel_id = $request->channel_id ? $request->channel_id : '';

        $channel = null;

        if ($channel_id) {

            $list[] = $request->channel_id;

            $channel = Channel::find($channel_id);

        } else {

            $channels = getChannels(Auth::user()->id);

            foreach ($channels as $key => $value) {
                $list[] = $value->id;
            }
        }

        $subscribers = ChannelSubscription::whereIn('channel_subscriptions.channel_id', $list)
                        ->select('channel_subscriptions.channel_id as channel_id',
                                'channels.name as channel_name',
                                'users.id as user_id',
                                'users.name as user_name',
                                'users.picture as user_image',
                                'channel_subscriptions.id as subscriber_id',
                                'channel_subscriptions.created_at as created_at')
                        ->leftJoin('channels', 'channels.id', '=', 'channel_subscriptions.channel_id')
                        ->leftJoin('users', 'users.id', '=', 'channel_subscriptions.user_id')
                        ->orderBy('created_at', 'desc')
                        ->paginate();

        return view('user.channels.subscribers')->with('page', 'channels')->with('subPage', 'subscribers')->with('subscribers', $subscribers)->with('channel_id', $channel_id)->with('channel', $channel);

    }

    public function card_details(Request $request) {

        $cards = Card::where('user_id', Auth::user()->id)->get();

        $video_id = $request->v_id ? $request->v_id : '';

        $subscription_id = $request->s_id ? $request->s_id : '';

        return view('user.account.cards')->with('page', 'account')
            ->with('subPage', 'cards')
            ->with('cards', $cards)
            ->with('video_id', $video_id)
            ->with('subscription_id', $subscription_id);
    }


    /**
     * Show the payment methods.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_card_add(Request $request) {

        $last_four = substr($request->number, -4);

        $stripe_secret_key = \Setting::get('stripe_secret_key');

        $response = json_decode('{}');

        if($stripe_secret_key) {

            \Stripe\Stripe::setApiKey($stripe_secret_key);

        } else {

            $response->success = false;
            
            $response->message = tr('adding_cards_not_enabled_application');

            return back()->with('flash_errors', $response);
        }

        try {

            // Get the key from settings table
            
            $customer = \Stripe\Customer::create([
                    "card" => $request->stripeToken,
                    "email" => \Auth::user()->email
                ]);

            if($customer) {

                $customer_id = $customer->id;


                $cards = new Card;
                $cards->user_id = \Auth::user()->id;
                $cards->customer_id = $customer_id;
                $cards->last_four = $last_four;
                $cards->card_token = $customer->sources->data ? $customer->sources->data[0]->id : "";

                // Check is any default is available
                $check_card = Card::where('user_id', \Auth::user()->id)->first();

                $cards->cvv = $request->cvv;

                $cards->card_name = $request->card_name;

                $cards->month = $request->month;

                $cards->year = $request->year;

                if($check_card)
                    $cards->is_default = 0;
                else
                    $cards->is_default = 1;
                
                $cards->save();

                $user = User::find(\Auth::user()->id);

                if($user && $cards->is_default) {

                    $user->payment_mode = 'card';
                    $user->card_id = $cards->id;
                    $user->save();

                }

                $response_array = array('success' => true);

                $response_code = 200;

            } else {
                $response->message('Could not create client ID');
            }
        
        } catch(Exception $e) {

            return back()->with('flash_error' , $e->getMessage());

        }
            
        if ($request->video_id) {

            return redirect(route('user.subscription.ppv_invoice', $request->video_id))->with('flash_success', tr('successfully_created'));

        } else if($request->subscription_id) {

            return redirect(route('user.subscription.invoice', ['s_id'=>$request->subscription_id]))->with('flash_success', tr('successfully_created'));

        }

        return back()->with('flash_success', tr('successfully_created'));
    }



    public function payment_card_default(Request $request)
    {
        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
        ]);

        $response = $this->UserAPI->default_card($request)->getData();

        if($response->success) {
            $message = tr('card_default_success');
            $type = "flash_success";
        } else {
            $message = tr('unkown_error');
            $type = "flash_error";
        }

        return back()->with($type, $message);
    }

    /**
     * Show the payment methods.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_card_delete(Request $request)
    {
        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
        ]);

        $response = $this->UserAPI->delete_card($request)->getData();
        
        if($response->success) {

            $message = $response->message;

            $type = "flash_success";

        } else {
            $message = $response->error_messages;
            $type = "flash_error";
        }

        return back()->with($type, $message);
    }

    /**
     * Show the payment methods.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_update_default(Request $request) {

        $this->validate($request, [
                'payment_mode' => 'required',
            ]);

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
        ]);        

        $response = $this->UserAPI->payment_mode_update($request)->getData();

        if($response->success) {
            $message = tr('card_default_success');
            $type = "flash_success";
        } else {
            $message = tr('unkown_error');
            $type = "flash_error";
        }

        return back()->with($type, $message);
    }

    /**
     * Function Name : stripe_payment()
     *
     * To pay the payment of subscription through stripe 
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param object $request - user and subscription details
     *
     * @return json response details
     */
    public function stripe_payment(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'subscription_id' => $request->subscription_id,
            'coupon_code'=>$request->coupon_code
        ]);        

        $response = $this->UserAPI->stripe_payment($request)->getData();

        if ($response->success) {

            return redirect(route('user.subscription.success'))->with('flash_success', $response->message);

        } else {

            if ($response->error_code == 901) {

                return back()->with('flash_error', $response->error_messages.'. '.tr('default_card_add_message').'  <a href='.route('user.card.card_details', ['s_id'=>$request->subscription_id]).'>'.tr('add_card').'</a>');

            }

            return back()->with('flash_error', $response->error_messages);
        }

    }

    /**
     * Function Name : subscribed_channels()
     *
     * To list otu  subscribed channels based on logged in users
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param object $request - user details
     *
     * @return json response details
     */
    public function subscribed_channels(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
        ]);        

        if ($request->id) {

            $channel_id = ChannelSubscription::where('user_id', $request->id)->pluck('channel_id')->toArray();

            $request->request->add([ 
                'channel_id' => $channel_id,
            ]);        
        }

        $response = $this->UserAPI->channel_list($request)->getData();

        // dd($response);

        return view('user.channels.list')->with('page', 'channels')
                ->with('subPage', 'channel_list')
                ->with('response', $response);

    }


    /**
     * Function Name : partialVideos()
     *
     * To get video details of channels videos using skip & take
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param object $request - user and channel details
     *
     * @return json response details
     */
    public function partialVideos(Request $request) {

        $request->request->add([ 

               'age' => \Auth::check() ? \Auth::user()->age_limit : "",

        ]);

        $videos = $this->UserAPI->channel_videos($request->channel_id, $request->skip, $request)->getData();

        $channel = Channel::find($request->channel_id);

        $view = View::make('user.videos.partial_videos')
                    ->with('videos',$videos)
                    ->with('channel',$channel)
                    ->render();

        return response()->json(['view'=>$view, 'length'=>count($videos)]);
    }


    /**
     * Function Name : payment_mgmt_videos()
     *
     * To get payment video details of logged in user using skip & Take
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param object $request - user and channel details
     *
     * @return json response details
     */
    public function payment_mgmt_videos(Request $request) {

        // Get Videos

        // $videos = VideoRepo::channel_videos($request->channel_id, null, $request->skip);

       // $payment_videos = VideoRepo::payment_videos($request->channel_id, null, $request->skip);

        $payment_videos = $this->UserAPI->payment_videos($request->channel_id, $request->skip)->getData();


        $view = View::make('user.videos.partial_payment_videos')
                    ->with('payment_videos', $payment_videos)->render();

        return response()->json(['view'=>$view, 'length'=>$payment_videos->count]);
    }


    /**
     * Function Name : invoice()
     *
     * To Display subscription invoice page based on subscription id
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param integer $id - subscription id
     *
     * @return json response details
     */
    public function invoice(Request $request) {

        $request->request->add([ 
            'u_id'=>Auth::check() ? \Auth::user()->id : '',
        ]);

        $model = $request->all();

        if (!$request->s_id) {

            return back()->with('flash_error', tr('something_error'));

        }

        $subscription = Subscription::find($request->s_id);

        if(!count($subscription)) {
            return redirect(route('user.dashboard'))->with('flash_error', tr('no_subscription_found'));
        }


        return view('user.invoice')->with('page', 'invoice')->with('subPage', 'invoice')->with('model', $model)->with('subscription',$subscription)
            ->with('model',$model);
    }


    /**
     * Function Name : ppv_invoice()
     *
     * To Display ppv invoice page based on video id
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param integer $id - video id
     *
     * @return json response details
     */
    public function ppv_invoice($id) {

        $video = VideoTape::find($id);

        if ($video) {

            if (Auth::check()) {

                $video->video_tape_id = $video->id;

                $ppv_status = VideoRepo::pay_per_views_status_check(Auth::user()->id, Auth::user()->user_type, $video)->getData();

                if ($ppv_status->success) {

                    return redirect(route('user.single', $video->video_tape_id));
                }

            }

            return view('user.ppv_invoice')
                ->with('page', 'ppv-invoice')
                ->with('video',$video)
                ->with('subPage', 'ppv-invoice');
                
        } else {

            return back()->with('flash_error', tr('video_not_found'));
        }
    }

    /**
     * Function Name : pay_per_view()
     *
     * To Display ppv video page
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - video with user Details
     *
     * @return json response details
     */
    public function pay_per_view($id) {

        $video = VideoTape::find($id);

        if(!$video) {


            return back()->with('flash_error', tr('video_not_found'));

        }
        return view('user.pay_per_view')
                ->with('page', 'pay_per_view')
                ->with('subPage', 'pay_per_view')->with('video', $video);

    }


    /**
     * Function Name: payper_videos()
     * To load all the paper views
     *
     * @return view page
     */
    public function payper_videos(Request $request) {
        // Get Logged in user id
        $id = Auth::user()->id;

        $request->request->add([ 
            'id'=>\Auth::user()->id,
            'age' => \Auth::user()->age_limit,
        ]);  

        $model = $this->UserAPI->pay_per_videos($request)->getData();

        // Return the view page
        return view('user.payperview')->with('model' , $model)
                        ->with('page' , 'Profile')
                        ->with('subPage' , 'Payper Videos');
    }

    /**
     * Function Name : payment_type()
     *
     * To Check whether the user is going to pay through paypal / stripe payment (For PPV)
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function payment_type($id, Request $request) {

        if($request->payment_type == 1) {

            return redirect(route('user.ppv-video-payment', ['id'=>$id, 'coupon_code'=>$request->coupon_code]));

        } else {

            return redirect(route('user.card.ppv-stripe-payment', ['video_tape_id'=>$id, 'coupon_code'=>$request->coupon_code]));
        }
    }

    /**
     * Function Name : subscription_payment()
     *
     * To Check whether the user is going to pay through paypal / stripe payment (For subscription)
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function subscription_payment(Request $request) {

        if($request->payment_type == 1) {

            return redirect(route('user.paypal' , $request->s_id, 'coupon_code', $request->coupon_code));

        } else {

            return redirect(route('user.card.stripe_payment' , ['subscription_id' => $request->s_id, 'coupon_code'=>$request->coupon_code]));
        }
    }

    /**
     * Function Name : ppv_stripe_payment()
     *
     * To Pay PPV amount through stripe payment gateway
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function ppv_stripe_payment(Request $request) {

        $request->request->add([
            'id'=>Auth::user()->id,
            ]);

        $payment = $this->UserAPI->stripe_ppv($request)->getData();

        if ($payment->success) {

            return redirect(route('user.video.success',$request->video_tape_id))->with('flash_success', $payment->message);

        } else {


            if ($payment->error_code == 901) {

                return back()->with('flash_error', $payment->error_messages.'. '.tr('default_card_add_message').'  <a href='.route('user.card.card_details', ['v_id'=>$request->video_tape_id]).'>'.tr('add_card').'</a>');

            }

            return back()->with('flash_error', $payment->error_messages);
        }
    }

    /**
     * Function Name : payment_success()
     *
     * To displaye subscription success message
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function payment_success() {

        return view('user.subscription');
    }

    /**
     * Function Name : video_success()
     *
     * To displaye video success messae
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function video_success($id = "") {

        if(!$id) {
            return redirect()->to('/')->with('flash_error' , tr('something_error'));
        }

        return view('user.video_subscription')->with('id', $id);
    }

    /**
     * Function Name : save_video_payment
     * Brief : To save the payment details
     *
     * @param integer $id Video Id
     * @param object  $request Object (Post Attributes)
     *
     * @return flash message
     */
    public function save_video_payment($id, Request $request) {

        // Load Video Model
        $model = VideoTape::find($id);

        // Get post attribute values and save the values
        if ($model) {

            $request->request->add([ 
                'ppv_created_by'=> Auth::user()->id ,
                'is_pay_per_view'=>PPV_ENABLED
            ]); 

            if ($data = $request->all()) {

                // Update the post
                if (VideoTape::where('id', $id)->update($data)) {
                    // Redirect into particular value
                    return back()->with('flash_success', tr('payment_added'));       
                } 
            }
        }
        return back()->with('flash_error', tr('admin_published_video_failure'));
   
    }

    /**
     * Function Name : remove_payper_view()
     * To remove pay per view
     * 
     * @return falsh success
     */
    public function remove_payper_view($id) {
        
        // Load video model using auto increment id of the table
        $model = VideoTape::find($id);
        if ($model) {
            $model->ppv_amount = 0;
            $model->is_pay_per_view = PPV_DISABLED;
            $model->type_of_subscription = 0;
            $model->type_of_user = 0;
            $model->save();
            if ($model) {
                return back()->with('flash_success' , tr('removed_pay_per_view'));
            }
        }
        return back()->with('flash_error' , tr('admin_published_video_failure'));
    
    }

    /**
     * Function Name : my_channels()
     *
     * To list out channels based on logged in users
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function my_channels(Request $request) {

        $request->request->add([
            'id'=>Auth::user()->id,
        ]);

        $response = $this->UserAPI->user_channel_list($request)->getData();


        return view('user.channels.list')->with('page', 'my_channel')
                ->with('subPage', 'channel_list')
                ->with('response', $response);
    }


    /**
     * Function Name : forgot_password()
     *
     * To send password to the requested users
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function forgot_password(Request $request) {

        $response = $this->UserAPI->forgot_password($request)->getData();

        if ($response->success) {

            return back()->with('flash_success', $response->message);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }
    }

    /**
     * Function Name : subscription_history()
     *
     * To list out subscribed history based on id
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function subscription_history(Request $request) {

        $request->request->add([ 
            'id'=>Auth::user()->id,
            'token'=>Auth::user()->token,
            'device_type'=>DEVICE_WEB,
        ]); 

        $response = $this->UserAPI->subscribedPlans($request)->getData();

        if ($response->success) {

            return view('user.history.subscription_history')->with('page', 'history')
                ->with('subPage', 'subscription_history')
                ->with('response', $response);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

    }

    /**
     * Function Name : ppv_history()
     *
     * To list out ppv history based on id
     *
     * @created_by Shobana
     *
     * @updated_by - 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function ppv_history(Request $request) {

        $request->request->add([ 
            'id'=>Auth::user()->id,
            'token'=>Auth::user()->token,
            'device_type'=>DEVICE_WEB,
        ]); 

        $response = $this->UserAPI->ppv_list($request)->getData();

        if ($response->success) {

            return view('user.history.ppv_history')->with('page', 'history')
                ->with('subPage', 'ppv_history')
                ->with('response', $response);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

    }


    /**
     * Function Name : tags_videos()
     *
     * To list out tags videos based on tag id
     * 
     * @created_by - Shobana Chandrasekar
     *
     * @updated_by - -
     *
     * @param integer $request->id - Category Id
     *
     * @return response of success/failure message
     */
    public function tags_videos(Request $request) {

        $tag = Tag::find($request->id);

        if ($tag) {

            if (Auth::check()) {

                $request->request->add([ 
                    'tag_id'=>$request->id,
                    'id' => \Auth::user()->id,
                    'token' => \Auth::user()->token,
                    'device_token' => \Auth::user()->device_token,
                    'age'=>\Auth::user()->age_limit,
                    'device_type'=>DEVICE_WEB
                ]);
            } else {

                $request->request->add([ 
                    'tag_id'=>$request->id,
                    'device_type'=>DEVICE_WEB
                ]);
            }

            $data = $this->UserAPI->tags_videos($request)->getData();


            if($data->success) {

                return view('user.tags.tags_videos')->with('page', 'tag_name'.$tag->id)
                                        ->with('videos',$data)
                                        ->with('tag', $tag);

            } else {

                return back()->with('flash_error', $data->error_messages);

            }
        } else {

            return back()->with('flash_error', tr('tag_not_found'));

        }
    }

   /**
    * Function Name : subscriptions_autorenewal_enable
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
    public function subscriptions_autorenewal_enable(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'device_type'=>DEVICE_WEB
        ]);

        $response = $this->UserAPI->autorenewal_enable($request)->getData();

        if ($response->success) {

            return back()->with('flash_success', $response->message);

        } else {

            return back()->with('flash_error', $response->error_messages);
        }

    }

   /**
    * Function Name : subscriptions_autorenewal_pause
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
    public function subscriptions_autorenewal_pause(Request $request) {

        $request->request->add([ 
            'id' => \Auth::user()->id,
            'token' => \Auth::user()->token,
            'device_token' => \Auth::user()->device_token,
            'device_type'=>DEVICE_WEB
        ]);

        $response = $this->UserAPI->autorenewal_cancel($request)->getData();

        if ($response->success) {

            return back()->with('flash_success', $response->message);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

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
    public function categories_view($id, Request $request) {

        $request->request->add([ 
            'category_id'=>$id,
            'id' => \Auth::check() ? \Auth::user()->id : '',
            'token' => \Auth::check() ? \Auth::user()->token : '',
            'device_token' => \Auth::check() ? \Auth::user()->device_token : '',
            'device_type'=>DEVICE_ANDROID
        ]);

        $category = Category::where('unique_id', $request->category_id)->first();

        if ($category) {

             $request->request->add([ 
                'category_id'=>$category->id,
            ]);


        } else {

            return back()->with('flash_error', tr('category_not_found'));

        }

        $response = $this->UserAPI->categories_view($request)->getData();

        if ($response->success) {

            $category = $response->category;

            $videos = $response->category_videos;

            $channels = $response->channels_list;


            return view('user.categories.view')
                        ->with('page' , 'categories_'.$request->category_id)
                        ->with('subPage' , 'categories')
                        ->with('category' , $category)
                        ->with('videos', $videos)
                        ->with('channels', $channels);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }
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
        
        $request->request->add([ 
            'id' => \Auth::check() ? \Auth::user()->id : '',
            'token' => \Auth::check() ? \Auth::user()->token : '',
            'device_token' => \Auth::check() ? \Auth::user()->device_token : '',
            'device_type'=>DEVICE_ANDROID
        ]);

        $response = $this->UserAPI->categories_videos($request)->getData();

        if ($response->success) {

            $view = View::make('user.categories.videos')
                    ->with('videos',$response->data)
                    ->render();

            return response()->json(['success'=>true, 'view'=>$view]);

        } else {

            return response()->json(['success'=>false, 'data'=>$response->error_messages]);

        }

    } 


    /**
     * Function Name : categories_channels
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
    public function categories_channels(Request $request) {

        $request->request->add([ 
            'id' => \Auth::check() ? \Auth::user()->id : '',
            'token' => \Auth::check() ? \Auth::user()->token : '',
            'device_token' => \Auth::check() ? \Auth::user()->device_token : '',
            'device_type'=>DEVICE_ANDROID
        ]);

        $response = $this->UserAPI->categories_channels_list($request)->getData();

        if ($response->success) {

            $view = View::make('user.categories.channels')
                    ->with('channels',$response->data)
                    ->render();

            return response()->json(['success'=>true, 'view'=>$view]);

        } else {

            return response()->json(['success'=>false, 'data'=>$response->error_messages]);

        }

    }   

        /**
     *
     * Function : custom_live_videos()
     *
     * @description return list of live videos created by admin
     *
     * @author Shobana , Edited By - shobana
     *
     * @return list page for live videos
     */

    public function custom_live_videos(Request $request) {

        $request->request->add([
            'paginate' => 1
        ]);

        $response = $this->UserAPI->custom_live_videos($request)->getData();

        // dd($response->live);

        return view('user.custom_live_videos.index')->with('page', 'custom_live_videos')
                ->with('subPage', 'custom_live_videos')
                ->with('data', isset($response->live) ? $response->live : []);

    }

    /**
     *
     * Function : single_live_rtmp_videos()
     *
     * @description return view details of live video
     *
     * @author Shobana , Edited By - shobana
     *
     * @return view page for selected live video
     */
    public function single_custom_live_video($id = "" , Request $request) {

        $request->request->add([
            'custom_live_video_id'=> $id,
        ]);

        $response = $this->UserAPI->single_custom_live_video($request)->getData();

        if(!$response->success) {
            return redirect()->to('/')->with('flash_error' , "Details not found");
        } 

        return view('user.custom_live_videos.view')->with('page', 'custom_live_videos')
                ->with('subPage', 'custom_live_videos')
                ->with('suggestions', isset($response->suggestions) ? $response->suggestions : [])
                ->with('video', isset($response->model) ? $response->model : []);

    }


    /**
     *
     * Function : settings()
     *
     * @description Display all the portion of the logged in user
     *
     * @author Shobana , Edited By - shobana
     *
     * @return list of options
     */
    public function settings(Request $request) {

        return view('user.settings')
                ->with('page', 'settings')
                ->with('subPage', '');
    }


    /**
     * Function Name : live_videos_invoice()
     *
     * To view the live video invoice page
     *
     * @created_by shobana
     *
     * @updated by --
     *
     * @param integer $request - video id 
     *
     * @return response of json
     */
    public function live_videos_invoice(Request $request){

        $video = LiveVideo::find($request->id);

        if ($video) {

            if (Auth::check()) {

               $live_video_payment =  LiveVideoPayment::where('live_video_id', $video->id)
                                ->where('live_video_viewer_id', Auth::user()->id)
                                ->where('status',DEFAULT_TRUE)->first();

                if ($live_video_payment) {

                    return redirect(route('user.single', $video->video_tape_id));

                } 

            }

            return view('user.live-videos.invoice')
                ->with('page', 'live-video-invoice')
                ->with('video',$video)
                ->with('subPage', 'live-video-invoice');
                
        } else {

            return back()->with('flash_error', tr('video_not_found'));
        }

    }

    /**
     * Function Name : referral_code_signup()
     *
     * @uses signup user through referrals
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param string referral_code 
     *
     * @return redirect signup page
     */
    public function referrals_signup($referral_code){

        try {

            if(!$referral_code) {

                throw new Exception("referral code invalid", 101);
                
            }

            $check_referral_code =  UserReferrer::where('referral_code', $referral_code)->where('status', DEFAULT_TRUE)->first();

            if(!$check_referral_code) {

                throw new Exception("referral code invalid", 101);
                
            }

            $user_details = User::where('status', USER_APPROVED)->where('id', $check_referral_code->user_id)->first();

            if(!$user_details) {

                throw new Exception("referral code invalid", 101);
                
            }

            return redirect(route('user.register.form', ['referral' => $referral_code]));

        } catch(Exception $e) {

            $error = $e->getMessage();

            $error_code = $e->getCode();

            return redirect(route('user.register.form'))->with('flash_error', $error);
        }

    }

    /**
     * Function Name : referrals()
     *
     * @uses signup user through referrals
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param string referral_code 
     *
     * @return redirect signup page
     */
    public function referrals(Request $request){

        try {

            $user_details =  Auth::user();

            $user_referrer_details = UserReferrer::where('user_id', $user_details->id)->first();

            if(!$user_referrer_details) {

                $user_referrer_details = new UserReferrer;

                $user_referrer_details->user_id = $user_details->id;

                $user_referrer_details->referral_code = uniqid();

                $user_referrer_details->save();

            }

            $referrals = Referral::where('parent_user_id', $user_details->id)->orderBy('created_at', 'desc')->get();

            foreach ($referrals as $key => $referral_details) {

                if($referral_user_details = $referral_details->userDetails) {

                    $referral_details->username = $referral_user_details->name ? : "";

                    $referral_details->picture = $referral_user_details->picture ? : "";

                }
            
            }

            return view('user.referrals.index')
                    ->with('referrals', $referrals)
                    ->with('user_referrer_details', $user_referrer_details);

        } catch(Exception $e) {

            $error = $e->getMessage();

            $error_code = $e->getCode();

            return redirect()->back()->with('flash_error', $error);
        }

    }

    /**
     * Function Name: referrals_view()
     *
     * @uses get the subscription & PPV details for selected referral user
     *
     * @created vithya R
     *
     * @updated vithya R
     *
     * @param integer $user_id
     *
     * @return response of success / failure message.
     */

    public function referrals_view(Request $request) {

        $user_details = User::find($request->user_id);

        if(!$user_details) {
            
            return back()->with('flash_error', tr('user_not_found'));
        }

        $user_referrer_details = UserReferrer::where('user_id', $request->parent_user_id)->first();

        $subscription_payments = UserPayment::select('user_payments.*', 'subscriptions.title')
                            ->leftjoin('subscriptions', 'subscriptions.id', '=', 'user_payments.subscription_id')
                            ->orderBy('user_payments.created_at' , 'desc')
                            ->where('user_payments.user_id' , $user_details->id)
                            ->get();

        $ppv_payments = PayPerView::select('pay_per_views.*', 'video_tapes.title', 'users.name as user_name')
                            ->leftJoin('video_tapes', 'video_tapes.id', '=', 'pay_per_views.video_id')
                            ->leftJoin('users', 'users.id', '=', 'pay_per_views.user_id')
                            ->where('pay_per_views.user_id' , $user_details->id)
                            ->orderBy('pay_per_views.created_at' , 'desc')->get();

        return view('user.referrals.view')
                    ->with('page', 'users')
                    ->with('sub_page', 'view-user')
                    ->with('user_details', $user_details)
                    ->with('user_referrer_details', $user_referrer_details)
                    ->with('subscription_payments', $subscription_payments)
                    ->with('ppv_payments', $ppv_payments);

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

            $request->request->add([
                    'id' => Auth::user()->id,
                ]);

            $response = $this->UserAPI->user_subscriptions_index($request)->getData();

            if(!$response->success) {

                throw new Exception($response->error_messages);
                
            }

            $channel_details = $response->data->channel_details;

            $user_subscriptions = $response->data->user_subscriptions;

            return view('user.user_subscriptions.index')
                        ->with('page','user_subscriptions')
                        ->with('subPage', 'user_subscriptions')
                        ->with('user_subscriptions', $user_subscriptions)
                        ->with('channel_details', $channel_details);

        } catch(Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }
    
    }


    /**
     * Function Name : user_subscriptions_create
     *
     * @uses To create subscription plan model
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */
    public function user_subscriptions_create(Request $request) {

        try {

            $channel_details = Channel::where('user_id', Auth::user()->id)->where('id', $request->channel_id)->first();

            if(!$channel_details) {

                throw new Exception(tr('channel_not_found'));
                
            }

            $user_subscription_details = new UserSubscription;

            return view('user.user_subscriptions.create')
                        ->with('page', 'user_subscriptions')
                        ->with('subPage', 'create_video_subscription')
                        ->with('channel_details', $channel_details)
                        ->with('user_subscription_details', $user_subscription_details);

        } catch(Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }
    }

    /**
     * Function Name : user_subscriptions_edit
     *
     * @uses To update subscription plan model
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public function user_subscriptions_edit(Request $request) {

        $validator = Validator::make($request->all(),array(
                'user_subscription_id' => 'required',
            )
        );
        
        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $errors);

        } else {

            $user_subscription_details = UserSubscription::where('id', $request->user_subscription_id)->where('channel_id', $request->channel_id)->first();

            $channel_details = Channel::find($request->channel_id);

            if(!$user_subscription_details || !$channel_details) {

                return back()->with('flash_error', tr('admin_not_enough_details'));

            }

            return view('user.user_subscriptions.edit')
                        ->with('page', 'user_subscriptions')
                        ->with('channel_details', $channel_details)
                        ->with('user_subscription_details', $user_subscription_details);

        }   
    
    }

    /**
     * Function Name : user_subscriptions_save
     *
     * @uses To save the video subscription plan based on loggd in user
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public function user_subscriptions_save(Request $request) {

        $request->request->add([
                'id'=>Auth::user()->id,
            ]);
        $response = $this->UserAPI->user_subscriptions_save($request)->getData();


        if ($response->success) {

            return redirect()->route('user.user_subscriptions.index', ['channel_id' => $request->channel_id])->with('flash_success', $response->message);

        } else {

            return back()->withInput()->with('flash_error', $response->error_messages);

        }

    }

    /**
     * Function Name user_subscriptions_status
     *
     * @uses To change the status of the video plan status
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */    
    public function user_subscriptions_status(Request $request) {

        $request->request->add([
                'id' => Auth::user()->id,
            ]);
        $response = $this->UserAPI->user_subscriptions_status($request)->getData();

        if ($response->success) {

            return back()->with('flash_success', $response->message);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

    }

    /**
     * Function Name : user_subscriptions_delete
     *
     * @uses Delete a particular video plan
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */ 
    public function user_subscriptions_delete(Request $request) {

        try {

            $request->request->add([
                    'id' => Auth::user()->id,
                ]);

            $response = $this->UserAPI->user_subscriptions_delete($request)->getData();

            if (!$response->success) {

                throw new Exception($response->error_messages);
            }

            return back()->with('flash_success', $response->message);

        } catch(Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }

    }

    /**
     * Function Name : user_subscriptions_subscribers
     *
     * @uses Get subscribers list based on the subscription
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */ 
    public function user_subscriptions_subscribers(Request $request) {

        try {

            $request->request->add([
                    'id' => Auth::user()->id,
                ]);

            $response = $this->UserAPI->user_subscriptions_subscribers($request)->getData();

            if (!$response->success) {

                throw new Exception($response->error_messages);
            }

            $channel_details = $response->data->channel_details;

            $user_subscription_payments = $response->data->user_subscription_payments;

            $user_subscription_details = $response->data->user_subscription_details;

            return view('user.user_subscriptions.subscribers')
                        ->with('page', 'user_subscriptions')
                        ->with('subPage', 'user_subscriptions')
                        ->with('channel_details', $channel_details)
                        ->with('user_subscription_details', $user_subscription_details)
                        ->with('user_subscription_payments', $user_subscription_payments);

        } catch(Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }

    }

    /**
     * Function Name : user_subscriptions_invoice
     *
     * @uses invoice page 
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */ 

    public function user_subscriptions_invoice(Request $request) {

        if (!$request->user_subscription_id) {

            return back()->with('flash_error', tr('something_error'));

        }

        $user_subscription_details = UserSubscription::find($request->user_subscription_id);

        $channel_details = Channel::find($request->channel_id);


        return view('user.user_subscriptions.invoice')
                    ->with('page', 'user_subscriptions')
                    ->with('subPage', 'user_subscriptions-invoice')
                    ->with('user_subscription_details',$user_subscription_details)
                    ->with('channel_details',$channel_details);
   
    }

    /**
     * Function Name : user_subscriptions_payment
     *
     * @uses based user choosed payment mode, deduct amount 
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public function user_subscriptions_payment(Request $request) {

        try {

            $logged_user_id = Auth::user()->id;

            $request_parameters = ['user_subscription_id' => $request->user_subscription_id, 'logged_user_id' => $logged_user_id, 'channel_id' => $request->channel_id];

            // Log::info("Paypal Payment".print_r($request_parameters, true));

            if($request->payment_type == PAYPAL) {

                Log::info("Paypal Payment");

                return redirect()->route('user.user_subscriptions.payment-paypal' , $request_parameters);

            } else {

                return redirect()->route('user.user_subscriptions.payment-stripe' , $request_parameters);
            }

        } catch(Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }
    
    }

    /**
     * Function Name : user_subscriptions_payment_by_stripe
     *
     * @uses invoice page 
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public function user_subscriptions_payment_by_stripe(Request $request) {

        $request->request->add([
            'id'=>Auth::user()->id,
            ]);

        $payment = $this->UserAPI->user_subscriptions_payment_by_stripe($request)->getData();


        if ($payment->success) {

            return redirect()->route('user.user_subscriptions.payment-success',$request->user_subscription_id)->with('flash_success', $payment->message);

        } else {


            if ($payment->error_code == 901) {

                return back()->with('flash_error', $payment->error_messages.'. '.tr('default_card_add_message').'  <a href='.route('user.card.card_details').'>'.tr('add_card').'</a>');

            }

            return back()->with('flash_error', $payment->error_messages);
        }
    
    }

    /**
     * Function Name : user_subscriptions_payment_success()
     *
     * @uses To displaye subscription success messae
     *
     * @created vithya R
     *
     * @created vithya R 
     *
     * @param Object $request - User Details
     *
     * @return json response details
     */
    public function user_subscriptions_payment_success($user_subscription_id = "") {

        if(!$user_subscription_id) {

            return redirect()->to('/')->with('flash_error' , tr('something_error'));

        }

        $user_subscription_details = UserSubscription::find($user_subscription_id);

        return view('user.user_subscriptions.payment-success')->with('user_subscription_details', $user_subscription_details);
    }

    /**
     * Function Name : user_subscriptions_payment_zero_plan
     *
     * @uses invoice page 
     *
     * @created vithya R
     *
     * @created vithya R
     *
     * @param Object $request
     *
     * @return repsonse of html page
     */

    public static function user_subscriptions_payment_zero_plan(Request $request) {

        try {

            DB::beginTransaction();

            $request->request->add(['id' => Auth::user()->id]);

            if(!$request->user_subscription_id) {

                return back()->with('flash_error' , tr('something_error'));

            }

            $user_subscription_details = UserSubscription::find($request->user_subscription_id);

            $user_subscription_payment = new UserSubscriptionPayment;

            $check_previous_payment = UserSubscriptionPayment::where('user_id' , $request->id)
                                        ->where('user_subscription_id' , $request->user_subscription_id)
                                        ->where('paid_amount', '<=', 0)
                                        ->where('status', DEFAULT_TRUE)
                                        ->first();

            if($check_previous_payment) {

                $expiry_date = $check_previous_payment->expiry_date;

                $user_subscription_payment->expiry_date = date('Y-m-d', strtotime($expiry_date. "+".$user_subscription_details->plan." months"));
                
            } else {

                $user_subscription_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$user_subscription_details->plan." months"));
            }

            $user_subscription_payment->payment_id  = "Free Plan";

            $user_subscription_payment->payment_mode  = "FREE-PLAN";

            $user_subscription_payment->user_id = $request->id;

            $user_subscription_payment->subscription_amount = $user_subscription_payment->paid_amount = 0;

            $user_subscription_payment->user_subscription_id = $user_subscription_details->id;

            $user_subscription_payment->subscription_plan = $user_subscription_details->plan;

            $user_subscription_payment->channel_id = $user_subscription_details->channel_id;

            $user_subscription_payment->status = PAID_STATUS;
            
            $user_subscription_payment->save();

            $user_subscription_details->total_subscription += 1;

            $user_subscription_details->save();

            DB::commit();

            return view('user.user_subscriptions.payment-success')->with('user_subscription_details', $user_subscription_details);

        } catch(Exception $e) {    

            DB::rollback();  

            $error_messages = $e->getMessage() ?: tr('payment_failure');    

            return back()->with('flash_error', $error_messages);

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

        $request->request->add([ 
            'id' => Auth::user()->id,
            'token' => Auth::user()->token,
            'device_type' => DEVICE_WEB,
        ]); 

        $response = $this->UserAPI->user_subscriptions_history($request)->getData();

        if ($response->success) {

            $user_subscription_payments = $response->data;

            $channel_details = $response->channel_details;

            return view('user.user_subscriptions.history')
                        ->with('page', 'user_subscriptions')
                        ->with('subPage', 'user_subscriptions')
                        ->with('user_subscription_payments', $user_subscription_payments)
                        ->with('channel_details', $channel_details)
                        ->with('response', $response);

        } else {

            return back()->with('flash_error', $response->error_messages);

        }

    }

}