<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;


use App\Repositories\VideoTapeRepository as VideoRepo;

use App\Jobs\UserPointJob;



use App\Helpers\Helper;

use App\Settings;

use App\User;
use App\Product;

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
use Mail;
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

use App\ProductImage;
use App\Cart;
use App\Order;
use App\UserWallet;
use App\UserAddress;
use App\transaction;
use App\SellerToken;
use App\UserPaymentMethod;
use App\PaymentMethod;
use App\ShippingInfo;
use App\Notification;
use App\ProductRating;
use App\OwnerPage;
use App\ProductWishlist;
use App\UserPoint;
use App\UseTipCredit;

class ProductController extends Controller
{
     protected $UserAPI;
     protected $Paypal;
    public function __construct(UserApiController $API,Request $request)
    {
        $this->UserAPI = $API;
      $this->middleware(['auth'], ['except' => [
                'marketplace','show','buy_product','user-products','admin-products','ownerpage','user_all_products','admin_all_products'
                
     ]]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

  public function marketplace()
    {


   

                $userproduct = DB::table('video_tapes')->get();
       
        $userproduct = DB::table('products')->leftjoin('product_ratings','product_ratings.product_id','=','products.id')->leftjoin('users','users.id','=','products.user_id')
         ->select('products.*',DB::raw('AVG(product_ratings.rating) as ratings_average'),'users.name as username')->where('products.generated_by','U')->orderBy('id','desc')->groupBy('products.id')->get(); 
         
        if(isset($_GET['token'])){
            $adminproduct = DB::table('products')->leftjoin('product_ratings','product_ratings.product_id','=','products.id')
         ->select('products.*',DB::raw('AVG(product_ratings.rating) as ratings_average'))->where('type','seller_token')->orderBy('id','desc')->groupBy('products.id')->get(); 
            // $adminproduct = Product::where('type','seller_token')->orderBy('id','desc')->get();
        }else{
            $adminproduct = DB::table('products')->leftjoin('product_ratings','product_ratings.product_id','=','products.id')
         ->select('products.*',DB::raw('AVG(product_ratings.rating) as ratings_average'))->where('products.generated_by','A')->orderBy('id','desc')->groupBy('products.id')->get(); 
        }
       
        return view('user.product.marketplace')->with('userproduct', $userproduct)->with('adminproduct', $adminproduct);
     
     }

     public function view_cart(){
         $user = Auth::user();
    $user_id  = $user->id;
    $order = Order::where('user_id',$user_id)->where('status',false)->first();
    $cart = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.price','carts.quantity')->where('orders.user_id',$user_id)->where('orders.status',false)->groupBy("products.id")->get();

    $cart_item =    $this->cart_item();
       
        return view('user.product.view_cart')->with('carts',$cart)->with("cart_item",$cart_item)->with("order", $order);
     }

 public function myproduct(){
    $user = Auth::user();
    $myproduct = $user->getproducts()->where('products.name','!=','')->orderBy('id','desc')->paginate(20);
    return view('user.product.my_product')->with('page', 'trending')
                                    ->with('myproduct',$myproduct);
 }
 public function delete_cart($id){
        $user = Auth::user();
       $order = Order::where('user_id',$user->id)->where('status',false)->first();
 $cart = Cart::where('product_id',$id)->where('order_id', $order->id)->delete();
  
   echo  $this->cart_detail_html();
 }
public function update_cart($id,$qty){
       $user = Auth::user();
       $order = Order::where('user_id',$user->id)->where('status',false)->first();
 $cart = Cart::where('product_id',$id)->where('order_id', $order->id)->first();
  $cart->quantity = $qty;
  $cart->save();

   echo  $this->cart_detail_html();
 }
 public function add_to_cart($id){
    $user = Auth::user();
    $order = Order::where('user_id',$user->id)->where('status',false)->first();

    if(!isset($order)){

           $order =  new Order;
           $order->user_id = $user->id;
           $order->save();
           $cart =  new Cart;
           $cart->product_id = $id;
           $cart->order_id = $order->id;
           if($cart->save()){
          echo $this->cart_item();
           }
    }else{
        $product = Product::find($id);
        if($product) {
            $crt = DB::table('carts as c')
                ->join('products as p','p.id','=','c.product_id')
                ->where('c.order_id',$order->id)
                ->where('p.user_id','!=',$product->user_id)
                ->select('c.*')
                ->get();
            if($crt) {
                foreach ($crt as $key => $value) {
                    DB::table('carts')->where('id', $value->id)->delete();
                }
            }
        }

    $cart = Cart::where('order_id',$order->id)->where('product_id',$id)->first();
    if($cart){
         $cart->quantity = $cart->quantity+1;
          if($cart->save()){
            echo $tt =  $this->cart_item();
            }
     }else{
           $cart =  new Cart;
           $cart->product_id = $id;
           $cart->order_id = $order->id;
           if($cart->save()){

            echo $this->cart_item();
           }
     }
}


   //return redirect(route('user.myproduct'))->with('flash_success', tr('successfully_created'));
 }

 public function cart_item(){
    $user = Auth::user();
    $user_id  = $user->id;
    $cart = DB::table("carts")
                ->select(DB::raw("sum(quantity) as total_cart"))
                ->where("status",0)
                ->where("order_id",DB::raw("(select id from `orders` where user_id = $user_id and status=0 limit 1)"))
                ->first()->total_cart;

                
                 
    return $cart; 
 }
 
  public function cart_detail_html(){
    $user = Auth::user();
    $user_id  = $user->id;
    $carts = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.price','carts.quantity')->where('orders.user_id',$user_id)->where('orders.status',false)->get();
         foreach($carts as $c){
            ?>
                <tr>
                    <td> 
                        <img src="{{asset('uploads/product')}}/{{$c->image}}"> 
                    </td>
                    <td>
                        <a href="{{route('user.product_preview',$c->id)}}" title="Preview" style="color: #337ab7;"> <h4><?php echo $c->name?> </h4>
                        </a>
                        <p  class="dlt delete_cart" id="<?php echo $c->id?>"><span>Delete</span></p>
                    </td>
                    <td>
                       <?php echo $c->price?>
                    </td>
                    <td>

                        <select class="form-control">
                         <?php for ($i =1; $i <= 10; $i++){ ?>
                <option value="<?php echo $i?>" <?php if($c->quantity==$i){ echo "selected";}?>><?php echo $i ?></option>
                  <?php }?>   
                    
                        </select>
                    </td>
                    </tr>
              <?php }   
 }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   

          $user = Auth::user();
      
        // $token = SellerToken::where('user_id',Auth::id())->where('redeem',0)->get();
           $token = UserPoint::where('user_id',Auth::id())->where('points','!=',"-1.00")->get();
           //    $token1 = UserPoint::where('user_id', Auth::id())->sum('points');
           // print_r($token1);
        $newproducts = Product::where('user_id',Auth::id())->where('name','')->where('seller_token_id','!=','0')->get();
          $tipme =  UseTipCredit::where('user_id',Auth::id())->where('status',false)->count();
          
         $mymethod = $user->getpaymentmethod()->get();
         
        if($user->total_points==0){
             return redirect(route('user.marketplace','token=true'))->with('flash_success', tr('successfully_created'));
        }else{
            if(count($mymethod)==0){

                  $allmethod = PaymentMethod::orderBy('cmc_rank',"asc")->get();
           return view('user.product.add_product')->with('tokens',$token)->with('newproducts',$newproducts)->with('allmethod',$allmethod);
           }else{
          return view('user.product.add_product')->with('tokens',$token)->with('newproducts',$newproducts)->with("tipme",$tipme);
           } 
        }

        
    }
        public function edit_payment_method()
    {   
          $user = Auth::user();
       
        
         $mymethod = $user->getpaymentmethod()->get();
         
       
          

                  $allmethod = PaymentMethod::orderBy('cmc_rank',"asc")->get();
           return view('user.account.edit_payment_method')->with('mymethod',$mymethod)->with('allmethod',$allmethod);
           
       

        

    }
        public function edit($id)
    {
        $product = Product::find($id);
         $newproducts = Product::where('user_id',Auth::id())->where('name','')->where('seller_token_id','!=','0')->get();
          $token = SellerToken::where('user_id',Auth::id())->where('redeem',0)->get();
       return view('user.product.add_product')->with('data',$product)->with('newproducts',$newproducts)->with('tokens',$token); 
    }
     public function update_payment_method(Request $request){
     $user = Auth::user();
     $mymethod = $user->getpaymentmethod()->get();
     if(count($mymethod)>0){
       DB::table('user_payment_methods')->where('user_id', '=', $user->id)->delete(); 
     }
    foreach($request->payment_methods as $key=>$pay){
        $payment_method =  new UserPaymentMethod;
        $payment_method->payment_method_id =  $pay;
          $payment_method->payment_detail =  $request->payment_detail[$key];
        $payment_method->user_id =  $user->id;
        $payment_method->save();

    }
    return redirect(route('user.add_product'));
   }
   public function save_payment_method(Request $request){
     $user = Auth::user();
    
    foreach($request->payment_methods as $key=>$pay){
        $payment_method =  new UserPaymentMethod;
        $payment_method->payment_method_id =  $pay;
        $payment_method->payment_detail =  $request->payment_detail[$key];
        $payment_method->user_id =  $user->id;
        $payment_method->save();

    }

  return redirect(route('user.add_product'));
   }

   public function save_rating_comment(Request $request){

        $product_rating =  new ProductRating;
         $user = Auth::user();
         $mymethod = $user->getpaymentmethod()->get();
        $product_rating->user_id =  $user->id;
        $product_rating->product_id =  $request->product_id;
        $product_rating->comment =  $request->comments;
     if($request->ratings){
      $product_rating->rating =  $request->ratings;  
    }
      if($request->hasFile('media')){

                $get = $request->file("media");

                $image = $product_rating->product_fileUpload($get);

                   if($image){
                        $product_rating->media = $image;
                    
                   }  
        }
                 $product_rating->save();
                
                 $product =    Product::find($product_rating->product_id);

                 if($request->ratings>0){

                       $point_types = DB::table('point_types')->where("name","Rating")->first();

                       $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","product")->orderBy('id','desc')->first();
                     $inputRequest = [];
                     $inputRequest['user_id'] = $user->id;
                     $inputRequest['owner_user_id'] = $product->user_id;
                    $inputRequest['point_type'] = POINT_TYPE_PRODUCT_RATING;
                     $inputRequest['product_id'] = $request->product_id;
                     $inputRequest['points'] = $data->point;
                      $this->UserAPI->product_tip($inputRequest);
                          $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $user->id;
                          $this->UserAPI->product_tip($inputRequest);

                   
                    }
                    if($request->comments!=""){
                        $point_types = DB::table('point_types')->where("name","Comment")->first();
                  
                       $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","product")->orderBy('id','desc')->first();
                     $inputRequest = [];
                     $inputRequest['user_id'] = $user->id;
                     $inputRequest['owner_user_id'] = $product->user_id;
                      $inputRequest['point_type'] = POINT_TYPE_PRODUCT_COMMENT;
                     $inputRequest['product_id'] = $request->product_id;
                     $inputRequest['points'] = $data->point;
                      $this->UserAPI->product_tip($inputRequest); 
                    $inputRequest['owner_user_id'] = $inputRequest['user_id'] =$user->id;
                          $this->UserAPI->product_tip($inputRequest); 
                    }
                   if($request->media!=""){
                    $point_types = DB::table('point_types')->where("name","Media_Image")->first();

            $data =  DB::table('point_settings')->where("point_type_id",$point_types->id)->where("type","product")->orderBy('id','desc')->first();
                     $inputRequest = [];
                     $inputRequest['user_id'] = $user->id;
                     $inputRequest['owner_user_id'] = $product->user_id;
                      $inputRequest['point_type'] = POINT_TYPE_PRODUCT_IMAGE;
                     $inputRequest['product_id'] = $request->product_id;
                     $inputRequest['points'] = $data->point; 
                     $this->UserAPI->product_tip($inputRequest);
                         $inputRequest['owner_user_id'] = $inputRequest['user_id'] = $user->id;
                          $this->UserAPI->product_tip($inputRequest);
                    }

              

                $user = User::find($user->id);



   $ratings = ProductRating::find($product_rating->id);
                
    $response_array = array('success' => true , 'comment' => $ratings,'total_points'=>$user->total_points, 'date' => $ratings->created_at->diffForHumans(),'message' => tr('comment_success') );
        $response = response()->json($response_array, 200);
     return $response;
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
            /*echo "<pre>";
            print_r($request->all()); die;*/
         if($request->id){
            $product =  Product::find($request->id);
         }else{
          $product = new Product;  
         }
         $image_id = $request->get("image_id");
         $more_images = array();
         $more_images_id = array();
       if($request->hasFile('image')){

                $get = $request->file("image");

                $image = $product->product_fileUpload($get);

                   if($image){
                        $product->image = $image;
                    
                   }  
        }

        if($request->hasFile("product_images")){
            $all_images = $request->file("product_images");

            $count = count($all_images);
            for($i = 0;$i < $count; $i++){
                if(isset($all_images[$i]) && !empty($all_images[$i])){
                      $images = $product->product_fileUpload($all_images[$i]);  
                      $more_images[] = $images;
                 }
             }

        }

         

    $product->user_id = Auth::user()->id;
    $product->name = $request->name;
 
    $product->description = $request->description;
    $product->price = $request->price;
    $product->type = $request->type;
    $product->status = 1;
    $product->generated_by = "U";
     

    if($product->save() || $image_id){
      $tipme =  UseTipCredit::where('user_id',Auth::id())->where('status',false)->first();
      if($tipme){
       $tipme->status = true;
       $tipme->save();
       }
        $id = $product->id;
        $count = count($more_images);
        if($more_images && $count > 0){
            for($i=0;$i<$count;$i++){

                if(strlen(trim($image_id[$i])) > 0){
                    $productImages = ProductImage::find($image_id[$i]);
                    
                }else {
                    $productImages = new ProductImage;  
                }
              
                $productImages->product_id = @$id;
                $productImages->image = $more_images[$i];
                $productImages->status = 1;
                $productImages->save(); 
            }
            
        }
     
     $delete_image = $request->get("delete_product_image");
     if($delete_image){
        $ex = explode(",",$delete_image);
        if(count($ex) > 0){
            ProductImage::whereIn("id",$ex)->delete();
        }
     }
    }
        return redirect(route('user.myproduct'))->with('flash_success', tr('successfully_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      
       $product = Product::with("getImages")->find($id);
        $user = Auth::user();
         

        $comments = DB::table('product_ratings')->leftjoin('users', 'users.id', '=', 'product_ratings.user_id')->select('product_ratings.*','users.name as username','users.picture')->where('product_ratings.product_id',$id)->orderBy('product_ratings.id',"desc")->get();
          $comment_rating_status = DEFAULT_TRUE;
          
          $can_comment = DEFAULT_FALSE;
          $wishlist='';
         if(@$user){
            $ordered = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('orders.*','transactions.*')->where('orders.user_id',$user->id)->where('orders.current_status',"D")->where('products.id',$id)->where('products.type','!=','seller_token')->groupBy("orders.id")->orderBy("orders.id",'desc')->get();
                if($ordered){
                   $can_comment = DEFAULT_TRUE; 
                }
         $mycomment = ProductRating::where('user_id', Auth::user()->id)->where('rating', '>', 0)->where('product_id', $id)->first();
         
               
             
                
        $wishlist = ProductWishlist::where("user_id",$user->id)->where('product_id',$id)->first();
            }
        $avrage = ProductRating::where('product_id', $id)
                    ->groupBy('product_id')
                    ->avg('rating');
        
       
                   
       return view('user.product.product_preview')->with('data',$product)->with('comments' , $comments)->with('avrage' , $avrage)->with('comment_rating_status' , $comment_rating_status)->with('average',$avrage)->with('can_comment',$can_comment)->with('wishlist',$wishlist);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
         return redirect(route('user.myproduct'))->with('flash_success', tr('successfully_deleted'));
    }

 public function buy_product($id){
    $product = Product::find($id);
    $user = User::find($product->user_id);
      $data =  $this->coinpayments_api_transaction('create_transaction',$product->price,$user->email);

      // print_r($data);die;
       header("Location: ".$data['result']['checkout_url']."");die();
 }
 public function create_stripe_customer(){
   
         $stripe_secret_key = \Setting::get('stripe_secret_key');

        if($stripe_secret_key) {


 \Stripe\Stripe::setApiKey($stripe_secret_key);

// $account = \Stripe\Customer::create([
//   "description" => "Customer for jenny.roseen@example.com",
//    "email" =>"jenny.rosen@example.com",
//   "source" => $bank_account['id']
  
// ]);
 $bank_account = \Stripe\Token::create([
  'bank_account' => [
    'country' => 'US',
    'currency' => 'usd',
    'account_holder_name' => 'Jenny Rosen',
    'account_holder_type' => 'individual',
    'routing_number' => '110000000',
    'account_number' => '000123456789'
  ]
]);
$account = \Stripe\Account::create([
    'country' => 'US',
    'type' => 'express',
    "email" => "bob@example.com"
  
    
   
]);


// $bank_account = \Stripe\Account::createExternalAccount(
//  $account['id'],
//   [
//     'external_accout' => $bank_account['id'],
//   ]
// );
   $user_details = User::find(Auth::id());
  $check_card_exists = User::where('users.id' , Auth::id())
                                ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                ->where('cards.id' , $user_details->card_id)
                                ->where('cards.is_default' , DEFAULT_TRUE);
$user_card_details = $check_card_exists->first();
 // $user_charge =  \Stripe\Charge::create(array(
 //                           "amount" => $total * 100,
 //                           "currency" => "usd",
 //                           "customer" => $customer_id,
 //                        ));

$payment = \Stripe\Charge::create([
  "amount" => 200,
  "currency" => "usd",
  "customer" => $user_card_details->customer_id, // obtained with Stripe.js
  "description" => "Charge for jenny.rosen@example.com",
  "transfer_data" => [
    "destination" => $account['id'],
  ],
 

]);



  }
 }
public function coinpayments_api_transaction($cmd, $amount,$mail,$req = array()) {
       $public_key = config('app.coin_public_key');
    
                   $req = array();
                        $req['version'] = 1;
                        $req['cmd'] = $cmd;
                        $req['key'] = $public_key;
                        $req['address'] = "QUtqSn9B5UjfLHE2o54LSRXpT3AUr2v6Kb";

                        $req['format'] = 'json'; //supported values are json and xml
                        $req['amount'] = $amount; //supported values are json and xml
                        $req['buyer_email'] = $mail; //supported values are json and xml
                        $req['currency1'] = 'USD'; //supported values are json and xml
                        $req['item_name'] = 'Test'; //supported values are json and xml
                        $req['currency2'] = 'LTCT'; //supported values are json and xml
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

   /* Listing user all  products*/
   public function user_all_products()
    {
        $userproduct = Product::where('generated_by','U')->orderBy('id','desc')->paginate(12);
        //$adminproduct = Product::where('generated_by','A')->orderBy('id','desc')->get();
        return view('user.product.user_products')
                        ->with('userproduct', $userproduct);
     
     }
     /* Listing admin all  products*/
   public function admin_all_products()
    {
        $adminproduct = Product::where('generated_by','A')->orderBy('id','desc')->paginate(20);
        return view('user.product.admin_products')
                        ->with('adminproduct', $adminproduct);
     
     } 

    public function saveWalletAddress(Request $req) {
        if(@$req->wallet && $req->wallet) {
            $wallet = new UserWallet();
            $checkWallet = UserWallet::where('user_id', Auth::id())->first();
            if($checkWallet) {
                $wallet = UserWallet::find($checkWallet->id);
            }
            $wallet->user_id = Auth::id();
            $wallet->wallet_address = $req->wallet;
            $wallet->status = 1;
            if($wallet->save()) {
                return redirect()->route('user.add_product')->with('flash_message' , 'Wallet added successfully');
            } else {
                return redirect()->route('user.add_product')->with('flash_error' , 'Wallet error');
            }
          } else {
            return redirect()->route('user.add_product')->with('flash_error' , 'fgf');
        }
    }

    public function checkCartOtherSellerProduct(Request $req) {
        if(@$req->product_id && $req->product_id) {
            $order = Order::where('user_id',Auth::id())->where('status',false)->first();
            if($order) {
                $product = Product::find($req->product_id);
                if($product) {
                    $diffSeller = DB::table('carts as c')
                                ->join('products as p','p.id','=','c.product_id')
                                ->where('c.order_id',$order->id)
                                ->where('p.user_id','!=',$product->user_id)
                                ->count();
                    return $diffSeller;
                }
            }
        }
        return 0;
    }
    public function add_address() {
     return view('user.product.add_address');
    }
  
     public function edt_address($id) {
        $UserAddress = UserAddress::find($id);
     return view('user.product.add_address')->with('data',$UserAddress);
    }
     public function delete_address($id) {
        $UserAddress = UserAddress::find($id);
        $UserAddress->delete();
     return redirect()->route('user.checkout')->with('flash_message' , 'Address deleted successfully');
    }
    public function total_cart_price(){
     $user = Auth::user();
     $user_id  = $user->id;
    $carts = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.price','carts.quantity')->where('orders.user_id',$user_id)->where('orders.status',false)->get();
                $total_price = 0;
                 foreach($carts as $c){
                    $total_price += $c->quantity*$c->price;
                 }
          
                 return $total_price;
  }
   public function save_address(Request $req) {
    
    if($req->id){
         $addresses = UserAddress::find($req->id);
    }else{
      $addresses = new UserAddress;  
    }
 
    $addresses->first_name = $req->first_name;
    $addresses->last_name = $req->last_name;
    $addresses->address_1 = $req->address_1;
    $addresses->address_2 = $req->address_2;
    $addresses->pincode = $req->pincode;
    $addresses->city = $req->city;
    $addresses->state = $req->state;
    $addresses->user_id = Auth::id();
    $addresses->save();
    
    
   return redirect()->route('user.checkout')->with('flash_message' , 'Address added successfully');
   
    }
    public function checkout() {
       
         $addresses = UserAddress::where('user_id',Auth::id())->orderBy('id','desc')->get();
         $wallet = UserWallet::where('user_id',Auth::id())->orderBy('id','desc')->first();
         $order = Order::where('user_id',Auth::id())->where('status',false)->first();
         if(count($order)!=0){
          $products = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.generated_by','products.user_id')->where('orders.id',$order->id)->where('orders.status',false)->first();
            $payment_methods = DB::table('payment_methods')->join('user_payment_methods', 'user_payment_methods.payment_method_id', '=', 'payment_methods.id')->where('user_payment_methods.user_id',$products->user_id)->groupBy("payment_methods.id")->get();
            
         if(count($addresses)>0){
             $cart_item =    $this->cart_item();
              $total_price =    $this->total_cart_price();
         return view('user.product.checkout1')->with('addresses' , $addresses)->with('cart_item' , $cart_item)->with('total_price' , $total_price)->with('order', $order)->with('wallet', $wallet)->with('products',$products)->with('payment_method',$payment_methods);
         }else{
           return redirect()->route('user.add_address');
         }
        }else{
             return redirect()->route('user.marketplace');
        } 
    }
    public function myorders(){
        $user = User::find(Auth::id());
         $orders = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->leftjoin('shipping_infos', 'shipping_infos.order_id', '=', 'orders.id')->select('orders.*','transactions.*','shipping_infos.id as shipping_id','shipping_infos.shipping_service','shipping_infos.tracking_number','shipping_infos.image')->where('orders.user_id',$user->id)->where('products.type','!=','seller_token')->groupBy("orders.id")->orderBy("orders.id",'desc')->get();
                  foreach($orders as $key=>$order){
                   
                    $orders[$key]->products = DB::table('carts')->join('products', 'products.id', '=', 'carts.product_id')->leftjoin('users', 'users.id', '=', 'products.user_id')->select('products.*','carts.quantity',"users.name as username")->where('carts.order_id',$order->order_id)->groupBy("products.id")->get();
                  }
                   
       
            
                 return view('user.product.user_orders')->with('orders',$orders);
    }
      public function shipping_detail($id){
         $orders = $this->transaction_order($id);
         $shipping_info  = ShippingInfo::where('order_id',$orders->order_id)->orderBy("id",'desc')->first();
        $img = url('uploads/shipping').'/'.$shipping_info->image;
                    
    $html = "<div><p>Provider:".$shipping_info->shipping_service."</p><p>Tracking Number:".$shipping_info->tracking_number."<p><img src=". $img." width='500px' heigh='400px;'></div>";
         echo $html;
      }
    public function order_status($id,$status){
         $orders = $this->transaction_order($id);
         $order = Order::find($orders->order_id);
         $order->current_status = $status;
         $order->save();
         echo "success";
      }
      public function customerorders(){
        $user = User::find(Auth::id());
          if(@$_GET['action']=="ongoing"){
             $orders = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('orders.*','transactions.*')->where('products.user_id',$user->id)->where('orders.current_status',"IP")->where('products.type','!=','seller_token')->groupBy("orders.id")->orderBy("orders.id",'desc')->get();
                  foreach($orders as $key=>$order){
                   $orders[$key]->products = DB::table('carts')->join('products', 'products.id', '=', 'carts.product_id')->leftjoin('users', 'users.id', '=', 'products.user_id')->select('products.*','carts.quantity',"users.name as username")->where('carts.order_id',$order->order_id)->groupBy("products.id")->get();
               }
          }elseif(@$_GET['action']=="complete"){
             $orders = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('orders.*','transactions.*')->where('products.user_id',$user->id)->where('orders.current_status',"D")->where('products.type','!=','seller_token')->groupBy("orders.id")->orderBy("orders.id",'desc')->get();
                  foreach($orders as $key=>$order){
                   $orders[$key]->products = DB::table('carts')->join('products', 'products.id', '=', 'carts.product_id')->leftjoin('users', 'users.id', '=', 'products.user_id')->select('products.*','carts.quantity',"users.name as username")->where('carts.order_id',$order->order_id)->groupBy("products.id")->get();

                  }

          }else{
         $orders = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('orders.*','transactions.*')->where('products.user_id',$user->id)->where('orders.current_status',"P")->where('products.type','!=','seller_token')->groupBy("orders.id")->orderBy("orders.id",'desc')->get();
                  foreach($orders as $key=>$order){
                   $orders[$key]->products = DB::table('carts')->join('products', 'products.id', '=', 'carts.product_id')->leftjoin('users', 'users.id', '=', 'products.user_id')->select('products.*','carts.quantity',"users.name as username")->where('carts.order_id',$order->order_id)->groupBy("products.id")->get();

                  }
              }
                   
        return view('user.product.customer_orders')->with('orders',$orders);
    }
     public function checkout_order(Request $req) {
       
       $order = Order::where('user_id',Auth::id())->where('status',false)->first();
       $order->addres_id = $req->address_id;
       $order->order_date = date('Y-m-d H:i:s');
       $order->save();
       $total_price =    $this->total_cart_price();
       $products = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.generated_by','products.user_id')->where('orders.id',$order->id)->where('orders.status',false)->first();
                 $user = User::find(Auth::id());
                 $transaction = new transaction;
        if($products->generated_by=='A'){
                
             if($req->payment_type=='coin'){
            $data =  $this->UserAPI->coinpayments_api_transaction('create_transaction',$total_price,$user->email);
            $transaction->order_id = $req->order_id;
            $transaction->gateway_type = $req->payment_type;
            $transaction->gateway_id = $data['result']['txn_id'];
            $transaction->save();
           $user = User::find(Auth::id());
            header("Location: ".$data['result']['checkout_url']."");die();
              }else{ 
                 if($req->payment_type=='paypal'){
                return redirect()->route('user.order_pay',$req->order_id);
                } 
                 if($req->payment_type=='card'){
                 $check_card_exists = User::where('users.id' , Auth::id())
                                        ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                        ->where('cards.id' , $user->card_id)
                                        ->where('cards.is_default' , DEFAULT_TRUE);
     

                 if($check_card_exists->count() != 0) {
                 // 
               $this->stripe_admin_payment($req);
               DB::table('carts')->where('order_id',$order->id)->update(["status"=>true]);
               $token = DB::table('carts')->join('products', 'products.id', '=', 'carts.product_id')->where('carts.order_id',$order->id)->where('products.type',"seller_token")->get();
               if(count($token)!=0){
                return redirect()->route('user.add_product'); 
               }else{
                return redirect()->route('user.myorders');
               }
               
             }else{
              return redirect()->route('user.card.card_details');
             }
         }
          }
        }else{
            $total_price =    $this->total_cart_price(); 
         $transaction->order_id = $req->order_id;
         $transaction->total_price = $total_price;

         $transaction->save();

          $notification =  new Notification;
                    $notification->sender_id = $user->id;
                    $notification->reciever_id = $products->user_id;
                    $notification->transaction_id = $transaction->id;
                    $notification->label = "New Order";
                    $notification->message =  $user->name. " place a new order please check your order listing";
                    $notification->save();
          $order->status = true;
          $order->save();
        return redirect()->route('user.myorders')->with('flash_message' , 'Request has been submitted successfully.You will shortly recieve invoice & payment instruction');
        
      }
     }

     public function notifications(){
        $nnotifications = Notification::Where('reciever_id',Auth::id())->orderBy('id',"desc")->paginate(20);
       
        return view('user.product.notification')->with('nnotifications',$nnotifications);
     }
     public function notifications_update(){
        DB::table('notifications')
                ->where('reciever_id', Auth::id())
                ->update(['read' => true]);
        
       echo  Notification::Where('reciever_id',Auth::user()->id)->where('read',false)->count();
        
       
     }
      public function transaction_update(Request $req) {
        $transaction =  transaction::find($req->transaction_id);
        if(@$req->seller_remark){
             $transaction->seller_remark = $req->seller_remark;
             $transaction->invoice_date = date('Y-m-d H:i:s');
        }
         $seller = User::find(Auth::id());
        $transaction->save();
         $orders = $this->transaction_order($req->transaction_id);
             $subject = tr('user_Invoice' , Setting::get('site_name'));
             
              $email_data["orders"] = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.price','carts.quantity')->where('orders.id',$orders->id)->get();
             
                  $user = User::find($orders->user_id);
                  $address = UserAddress::find($orders->addres_id);
                     $email_data['user'] = $user;
                     $email_data['seller'] = $seller;
                     $email_data['address'] = $address;
                    $email_data['transaction'] = $transaction;
              
                    $page = "emails.invoice";
                    $email = $user->email;
                    $notification =  new Notification;
                    $notification->sender_id = $seller->id;
                    $notification->reciever_id = $user->id;
                    $notification->transaction_id = $transaction->id;
                    $notification->label = "New Invoice";
                    $notification->message =  $seller->name. " send you a new invoice";
                    $notification->save();
                    //Helper::send_email($page,$subject,$email,$email_data);
        return redirect()->route('user.customerorders')->with('flash_message' , 'Payment Instruction and Invoice has been sent');
      }
      public function view_invoice($id){

      $transaction =  transaction::find($id);
        $orders = $this->transaction_order($id);
      
            $email_data["orders"] = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.user_id','products.price','products.description','carts.quantity')->where('orders.id',$orders->order_id)->get();
                 
                  $user = User::find($orders->user_id);
                  $seller = User::find(@$email_data["orders"][0]->user_id);
                  $address = UserAddress::find($orders->addres_id);
                     $email_data['user'] = $user;
                     $email_data['seller'] = $seller;
                     $email_data['address'] = $address;
                     $email_data['transaction'] = $transaction;
              
                       return view('user.product.view_invoice')->with('email_data',$email_data);
      }
     public function payment_send(Request $req){
        $transaction =  transaction::find($req->transaction_id);
         $user = User::find(Auth::id());
         $orders = $this->transaction_order($req->transaction_id);
            if($orders){
              if($orders->payment_sent==true && $orders->status==false){
                    return redirect()->route('user.myorders')->with('flash_message' , 'You have already paid for this order.Please wait for seller varification');
              }else if($orders->payment_sent==true && $orders->status==true){
                 return redirect()->route('user.myorders')->with('flash_message' , 'You have already paid for this order.');
              }else{
                if(@$req->buyer_remark){
                   $transaction->buyer_remark =  $req->buyer_remark;
                }
                $transaction->payment_sent = true;
                $transaction->payment_date = date('Y-m-d H:i:s');
                $transaction->save();
                 $subject = tr('varify_Invoice' , Setting::get('site_name'));
             
              $email_data["orders"] = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                ->join('products', 'products.id', '=', 'carts.product_id')->select('products.name','products.id','products.image','products.price','carts.quantity')->where('orders.id',$orders->id)->get();
             
                  $seller = User::find($orders->seller_id);
                  $address = UserAddress::find($orders->addres_id);
                   $transaction =  transaction::find($req->transaction_id);
                     $email_data['user'] = $user;
                     $email_data['seller'] = $seller;
                     $email_data['address'] = $address;
                    $email_data['transaction'] = $transaction;
              
                    $page = "emails.invoice";
                    $email = $seller->email;
                    $notification =  new Notification;
                    $notification->sender_id = $user->id;
                    $notification->reciever_id = $seller->id;
                    $notification->transaction_id = $transaction->id;
                    $notification->label = "Payment Sent";
                    $notification->message =  $user->name. " send you a payment for invoice";
                    $notification->save();
                    // Helper::send_email($page,$subject,$email,$email_data);
                return redirect()->route('user.myorders')->with('flash_message' , 'Your payment status has been updated.Please wait for seller varification');
              }

            }


     }
     public function mark_paid($id){
      $transaction =  transaction::find($id);
      $user = User::find(Auth::id());
     $orders = $this->transaction_order($id);
      if($user->id==$orders->seller_id){
        $transaction->status =  true;
        $transaction->varify_date =  date('Y-m-d H:i:s');
         $transaction->save();
         $order = Order::find($orders->order_id);
         $order->payment_status = true;
         $order->total_price = $transaction->total_price;
         $order->save();

          $notification =  new Notification;
                    $notification->sender_id = $user->id;
                    $notification->reciever_id = $order->user_id;
                    $notification->transaction_id = $transaction->id;
                    $notification->label = "Payment Varified";
                    $notification->message =  $user->name. " varified your payment for invoice";
                    $notification->save();
       return redirect()->route('user.customerorders')->with('flash_message' , 'Payment varified!!');
      }else{
       return redirect()->route('user.customerorders')->with('flash_error' , 'Permisssion Denied'); 
      }

     }
     public function save_shipping(Request $req){
         $user = User::find(Auth::id());
        $transaction =  transaction::find($req->transaction_id);
         $orders = $this->transaction_order($req->transaction_id);
         $shipping  =  New ShippingInfo;
         $shipping->order_id = $orders->order_id;
         $shipping->tracking_number = $req->tracking_number;
         $shipping->shipping_service = $req->shipping_service;
         if($req->hasFile('image')){

                $get = $req->file("image");

             $image = $shipping->shipping_fileUpload($get);
               
                   if($image){
                        $shipping->image = $image;
                    
                   }  
        }
        if($shipping->save()){
          $order = Order::find($orders->order_id);
           $order->current_status =  "IP";
           $order->save();
            $notification =  new Notification;
                    $notification->sender_id = $user->id;
                    $notification->reciever_id = $orders->user_id;
                    $notification->transaction_id = $transaction->id;
                    $notification->label = "New Order";
                    $notification->message =  $user->name. " dispatched your order view order listing for shipping detail";
                    $notification->save();
            return redirect()->route('user.customerorders')->with('flash_message' , 'Shipping Info has been added successfully');
        }
     }
     public function transaction_order($id){
         $orders = DB::table('carts')
                ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('transactions', 'orders.id', '=', 'transactions.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('orders.*','transactions.*','products.user_id as seller_id')->where('transactions.id',$id)->first();
                  return $orders;
     }
     public function stripe_admin_payment($req){
          $user = User::find(Auth::id());
          $order = Order::where('user_id',Auth::id())->where('status',false)->first();
          $check_card_exists = User::where('users.id' , Auth::id())
                                        ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                        ->where('cards.id' , $user->card_id)
                                        ->where('cards.is_default' , DEFAULT_TRUE);
     

                 if($check_card_exists->count() != 0) {
                              $stripe_secret_key = Setting::get('stripe_secret_key');
                            $user_card = $check_card_exists->first();

                            

                            $customer_id = $user_card->customer_id;

                      }

                    if($stripe_secret_key) {

                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                    } else {

                    throw new Exception(Helper::get_error_message(902), 902);

                    }

                    try{ 
                      
                     $total_price =    $this->total_cart_price();
                     
                     $user_charge =  \Stripe\Charge::create(array(
                    "amount" => $total_price*100,
                    "currency" => "usd",
                    "customer" => $customer_id,
                        ));

                      $transaction = new transaction;
                      $transaction->order_id = $req->order_id;
                      $transaction->gateway_type = $req->payment_type;
                      $transaction->gateway_id = $user_charge->id;
                      $transaction->total_price = $user_charge->amount/100;
                      $transaction->status = $user_charge->paid;
                      $transaction->payment_sent = true;
                      $transaction->payment_date = date('Y-m-d H:i:s');
                      $transaction->varify_date =  date('Y-m-d H:i:s');
                      $transaction->save();
                      $order->status = true;
                      $order->payment_status = true;
                      $order->save();
                     $products = DB::table('carts')
                  ->join('orders', 'orders.id', '=', 'carts.order_id')
                  ->join('products', 'products.id', '=', 'carts.product_id')->select('products.token','products.user_id','products.id',"carts.quantity")->where('orders.id',$order->id)->where('type','seller_token')->get();
                   foreach($products as $ps){
                    for($i=1;$i<=$ps->quantity;$i++){
                       $token = new SellerToken;
                       $token->token = $ps->token;
                       $token->user_id = $user->id;
                       $token->save(); 
                         $inputRequest = [];

                      // $inputRequest['user_id'] = $request->id;
                        $inputRequest['owner_user_id'] = $user->id;
                        $inputRequest['point_type'] = POINT_TYPE_TOKEN_PURCHASE;
                        $product_id = isset($ps->id) ? $ps->id : 0;
                        $inputRequest['product_id'] = $product_id;
                        $inputRequest['points'] = 1;
                       $this->UserAPI->purchase_point($inputRequest);
                    }
                  
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
      public function use_token(Request $req) {
     
        if(@$req->token && $req->token) {
           $token =  SellerToken::where('user_id',Auth::id())->where('redeem',0)->first();
           if($token){
              $token->redeem = true;
              $token->save();
            }
            $product = new UseTipCredit;
            $product->tip_id = $req->token;
            $product->user_id = Auth::id();
            $product->save();
           
                 $inputRequest = [];

                   $inputRequest['owner_user_id'] = Auth::id();
                        $inputRequest['point_type'] = POINT_TYPE_POINT_USE;
                        $product_id = isset($ps->id) ? $ps->id : 0;
                        $inputRequest['product_id'] = $product_id;
                        $inputRequest['points'] = 1;
                       $this->UserAPI->purchase_point($inputRequest); 
                     return redirect()->route('user.add_product')->with('flash_message' , 'Token used successfully');
                      

                
           
          } else {
            return redirect()->route('user.add_product')->with('flash_error' , 'fgf');
        }
                       
    }
     public function ownerpage(){
     $owners = OwnerPage::orderBy('created_at','desc')
                    ->where('status','1')
                    ->get();
    return view('user.product.owner_page')->with('owners',$owners);
       }

        public function add_to_wishlist($id){

        $user = Auth::user();
         $data = ProductWishlist::where("user_id",$user->id)->where('product_id',$id)->first();
         if(count($data)>0){
         $wishlist = $data;
         if( $wishlist->wish == true){
             $wishlist->wish = false;

         }else{
            $wishlist->wish = true;
         }
            
         }else{
          $wishlist = new ProductWishlist;
          $wishlist->user_id = $user->id;
       $wishlist->product_id = $id;
       $wishlist->wish = true;
         }
        
       
      if($wishlist->save()){
           return $wishlist;
      }
  
   }
     public function remove_to_wishlist($id){

        $user = Auth::user();
         $wishlist = ProductWishlist::where("user_id",$user->id)->where('product_id',$id)->first();
          $wishlist->wish = false;
      if($wishlist->save()){
           echo 'success';
      }
  
   }

}
