<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use Auth;
use App\User;
use App\Notification;
use App\Order;
use App\Product;
use App\LiveVideo;
use App\Chat;

use View;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    
     View::composer('*', function($view){
        $res = DB::table('coin_prices')->first();
        $token = "";
        $cart = "";
         $livevideo =   LiveVideo::where('is_streaming', DEFAULT_TRUE)
                    ->where('status', 0)->count();
                    
      if(Auth::check()){
            $token = DB::table('user_tokens')->where('redeem',false)->where('user_id',Auth::user()->id)->sum('token');
            $notifications = Notification::Where('reciever_id',Auth::user()->id)->orderBy('id',"desc")->limit(10)->get();
            $count = Notification::Where('reciever_id',Auth::user()->id)->where('read',false)->count();
             $buyer  = Order::Where('user_id',Auth::user()->id)->select("id")->get();
              $seller = Product::Where('user_id',Auth::user()->id)->select("id")->get();
            $user_id = Auth::user()->id;
            $cart = DB::table("carts")
                ->select(DB::raw("sum(quantity) as total_cart"))
                ->where("status",0)
                ->where("order_id",DB::raw("(select id from `orders` where user_id = $user_id and status=0 limit 1)"))
                ->first()->total_cart;
            $chating = Chat::where('user_id1', Auth::user()->id)->orWhere('user_id2', Auth::user()->id)->orderBy('id','desc')->first();
           
                 $view->with('price', $res)->with('token', $token)->with("cart",$cart)->with("count",$count)->with("notifications",$notifications)->with('livevideo', $livevideo)->with('buyer',$buyer)->with('seller',$seller)->with('chating',$chating);
                 }else{
                  
                     $view->with('livevideo', $livevideo);
                 }
                
       
    });
         

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
