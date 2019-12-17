<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use App\Helpers\Helper;

use Setting;

use Log;

use Auth;

use App\Repositories\UserRepository as UserRepo;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $redirectAfterLogout = '/login';

    /**
     * The Login form view that should be used.
     *
     * @var string
     */

    protected $loginView = 'user.auth.login';

    /**
     * The Register form view that should be used.
     *
     * @var string
     */

    protected $registerView = 'user.auth.register';


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'dob'=>'required',
            'age_limit'=>'required',
            'referral' => 'exists:user_referrers,referral_code,status,'.DEFAULT_TRUE
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {        

        $User = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Hash::make($data['password']),
            'timezone' => $data['timezone'],
            'picture' => asset('placeholder.png'),
            'chat_picture'=>asset('placeholder.png'),
            'login_by' => 'manual',
            'device_type' => 'web',
            'dob' => date('Y-m-d', strtotime($data['dob'])),
            'age_limit'=>$data['age_limit']
        ]);

        // Check the default subscription and save the user type 

        user_type_check($User->id);

        register_mobile('web');

        if(!Setting::get('email_verify_control')) {

            $User->is_verified = 1;

            $User->save();
        }
        
        // Send welcome email to the new user:
        $subject = tr('user_welcome_title' , Setting::get('site_name'));
        $email_data = $User;
        $page = "emails.welcome";
        $email = $data['email'];
        $result = Helper::send_email($page,$subject,$email,$email_data);
      
        return $User;
    }

    public function register(Request $request)
    {

        $age_limit = 0;

        if ($request->dob) {

            $dob = date('Y-m-d', strtotime($request->dob));

            $from = new \DateTime($dob);

            $to   = new \DateTime('today');

            $age_limit = $from->diff($to)->y;

        }

        $request->request->add([ 
            'age_limit'=>$age_limit,
        ]);

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
              $request, $validator
            );
        }

        if ($age_limit < 10) {


           return back()->with('flash_error', tr('min_age_error'));

        }

        $user = $this->create($request->all());

        if($request->referral) {

            UserRepo::referral_register($request->referral, $user);
        }

        if(Setting::get('email_verify_control')) {
            return redirect($this->redirectPath());
            //return redirect($this->redirectPath())->with('flash_error', tr('email_verify_alert'));

        } else {
            //dd($validator->fails());
            \Auth::loginUsingId($user->id);

            return redirect($this->redirectPath())->with('flash_success', tr('registration_success'));
        }
    }

        public function logout(){
            if(\Auth::check()) {
                $user = User::find(\Auth::user()->id);
                 $user->online = false;
                 $user->save();
                \Auth::logout();
            } 
           
          return redirect(route('user.login.form'));  
        }
    protected function authenticated(Request $request, User $user) {
  
        if(\Auth::check()) {

            if($user = User::find(\Auth::user()->id)) {

                // Check Admin Enabled the email verification

                if(Setting::get('email_verify_control')) {

            
                    if(!$user->is_verified) {
                         $user->online = false;
                       
                        \Auth::logout();

                        // Check the verification code expiry

                        Helper::check_email_verification("" , $user, $error);

                        return redirect(route('user.login.form'))->with('flash_error', tr('email_verify_alert'));

                    }

                }

                $user->token_expiry = Helper::generate_token_expiry();
                $user->online = true;
                $user->login_by = 'manual';
                $user->timezone = $request->has('timezone') ? $request->timezone : '';
                $user->save();
            }   
        };
        Log::info(redirect()->intended($this->redirectPath()));
       return redirect()->intended($this->redirectPath());
    }

}
