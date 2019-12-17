<?php

/**************************************************
* Repository Name: PaymentRepository
*
* Purpose: This repository used to do all functions related payments.
*
*@author: vidhyar2612
*
* Date Created: 30 Dec 2017
**************************************************/

namespace App\Repositories;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Validator;

use Hash;

use Log;

use Setting;

use Session;

use App\User;

use App\UserPayment;

use App\VideoTape;

use App\PayPerView;

use App\Subscription;

use App\UserReferrer;

use App\Referral;

use App\UserSubscription;

use App\UserSubscriptionPayment;
use App\UserToken;

class PaymentRepository {

    /**
     * @uses to store the payment failure
     *
     * @param $user_id
     *
     * @param $subscription_id
     *
     * @param $reason
     *
     * @param $payment_id = After payment - if any configuration failture or timeout
     *
     * @return boolean response
     */

    public static function subscription_payment_failure_save($user_id = 0 , $subscription_id = 0 , $reason = "" , $payment_id = "") {

        Log::info("subscription_payment_failure_save STRAT");

        /*********** DON't REMOVE LOGS **************/

        // Log::info("1- Subscription ID".$subscription_id);

        // Log::info("2- USER ID".$user_id);
        
        // Log::info("3- MESSAGE ID".$reason);

        // Check the user_id and subscription id not null

        /************ AFTER user paid, if any configuration failture *******/

        if($payment_id) {

            $user_payment_details = UserPayment::where('payment_id',$payment_id)->first();

            $user_payment_details->reason = "After_Payment"." - ".$reason;

            $user_payment_details->save();

            return true;

        }

        /************ Before user payment, if any configuration failture or TimeOut *******/

        if(!$user_id || !$subscription_id) {

            Log::info('Payment failure save - USER ID and Subscription ID not found');

            return false;

        }

        // Get the user payment details

        $user_payment = new UserPayment();

        $user_payment->expiry_date = date('Y-m-d H:i:s');

        $user_payment->payment_id  = "Payment-Failed";
        
        $user_payment->user_id = $user_id;
        
        $user_payment->subscription_id = $subscription_id;
        
        $user_payment->status = 0;

        $user_payment->reason = $reason ? $reason : "";

        $user_payment->save();

        return true;
        

    }

    /**
     * @uses to store the PPV payment failure
     *
     * @param $user_id
     *
     * @param $admin_video_id
     *
     * @param $payment_id
     *
     * @param $reason
     *
     * @param $payment_id = After payment - if any configuration failture or timeout
     *
     * @return boolean response
     */

	public static function ppv_payment_failure_save($user_id = 0 , $video_tape_id = 0 , $reason = "" , $payment_id = "") {

        /*********** DON't REMOVE LOGS **************/

        // Log::info("1- Subscription ID".$subscription_id);

        // Log::info("2- USER ID".$user_id);
        
        // Log::info("3- MESSAGE ID".$reason);

	    // Check the user_id and subscription id not null

        /************ AFTER user paid, if any configuration failture  or timeout *******/

        if($payment_id) {

            $ppv_payment_details = PayPerView::where('payment_id',$payment_id)->first();

            $ppv_payment_details->reason = "After_Payment"." - ".$reason;

            $ppv_payment_details->save();

            return true;

        }

        /************ Before user payment, if any configuration failture or TimeOut *******/

        if(!$user_id || !$video_tape_id) {

            Log::info('Payment failure save - USER ID and Subscription ID not found');

            return false;

        }

        $ppv_user_payment_details = PayPerView::where('user_id' , $user_id)->where('video_id' , $video_tape_id)->where('amount',0)->first();

        if(empty($ppv_user_payment_details)) {

            $ppv_user_payment_details = new PayPerView;

        }

        $ppv_user_payment_details->expiry_date = date('Y-m-d H:i:s');

        $ppv_user_payment_details->payment_id  = "Payment-Failed";

        $ppv_user_payment_details->user_id = $user_id;

        $ppv_user_payment_details->video_id = $video_tape_id;

        $ppv_user_payment_details->reason = "BEFORE-".$reason;

        $ppv_user_payment_details->save();

        return true;
	    

	}

    /**
     * @uses to store the payment with commission split 
     *
     * @param $admin_video_id
     *
     * @param $payperview_id
     *
     * @param $moderator_id
     * 
     * @return boolean response
     */

    public static function ppv_commission_split($video_tape_id = "" , $payperview_id = "") {

        if(!$video_tape_id || !$payperview_id) {

            Log::info("VideoTape"+$video_tape_id);

            Log::info("payperview_id"+$payperview_id);

            return false;
        }

        /***************************************************
         *
         * commission details need to update in following sections 
         *
         * admin_videos table - how much earnings for particular video
         *
         * pay_per_views - On Payment how much commission has calculated 
         *
         * Moderator - If video uploaded_by moderator means need add commission amount to their redeems
         *
         ***************************************************/

        // Get the details

        $video_tape_details = VideoTape::find($video_tape_id);

        if(count($video_tape_details) == 0 ) {

            Log::info('ppv_commission_split - VideoTape Not Found');

            return false;
        }

        $ppv_details = PayPerView::find($payperview_id);

        if(count($ppv_details) == 0 ) {

            Log::info('ppv_commission_split - PayPerView Not Found');

            return false;

        }

        $total = $ppv_details->amount;

        // Commission split 

        $admin_commission = Setting::get('admin_ppv_commission')/100;

        $admin_ppv_amount = $total * $admin_commission;

        $user_ppv_amount = $total - $admin_ppv_amount;

        $user_total = $user_ppv_amount;

        Log::info("PPV - user_total".$user_total);


        // Referral Commission

        $referral_commission = Setting::get('referral_commission')/100;

        Log::info("PPV - referral_commission".$referral_commission);

        $referral_amount = $user_total * $referral_commission;

        Log::info("PPV - referral_amount".$referral_amount);

        $user_ppv_amount_after_referral_split = $user_total - $referral_amount;

        Log::info("PPV - user_ppv_amount_after_referral_split".$user_ppv_amount_after_referral_split);

        // Update video earnings

        $video_tape_details->admin_ppv_amount = $video_tape_details->admin_ppv_amount + $admin_ppv_amount;

        $video_tape_details->user_ppv_amount = $video_tape_details->user_ppv_amount+ $user_ppv_amount_after_referral_split;

        $video_tape_details->save();

        // Update PPV Details

        if($ppv_details) {

            Log::info("PPV DETAILS INSIDE");

            Log::info("PPV - referral_amount".$referral_amount);

            $ppv_details->currency = Setting::get('currency');

            $ppv_details->admin_ppv_amount = $admin_ppv_amount;

            $ppv_details->user_ppv_amount = $user_ppv_amount_after_referral_split;

            $ppv_details->referral_commission = $referral_amount;

            $ppv_details->save();

            self::referral_amount_update($ppv_details->user_id, $referral_amount);

        
        } else {

            Log::info("payperview_id".$payperview_id);

            Log::info("PPV DETAILS  - NOOOOOOO");

        }

        add_to_redeem($video_tape_details->user_id , $user_ppv_amount , $admin_ppv_amount);

        return true;

    }

    public static function referral_amount_update($user_id, $commission_amount = 0) {

        Log::info("referral_amount_update");

        Log::info("user_id".$user_id);

        Log::info("commission_amount".$commission_amount);

        $find_referrer_details = Referral::where('user_id', $user_id)->first();
 
        if($find_referrer_details) {

            $user_referrer_details = UserReferrer::find($find_referrer_details->user_referrer_id);
                
            if($user_referrer_details) {
              if($commission_amount==""){
                $commission_amount = 0;
              }
                $user_referrer_details->total_referrals_earnings = $user_referrer_details->total_referrals_earnings + $commission_amount;

                      $user_referrer_details->save();
                 add_to_redeem($user_referrer_details->user_id , $earnings = $commission_amount , $admin_commission = 0);

            } else {
                
                Log::info("user_referrer_details not found");

            }

        } else {
            Log::info("No referrals");
        }
    }

    public static function tier2_referral_amount_update($user_id, $commission_amount = 0) {

        Log::info("Tier 2 referral_amount_update");

        Log::info("user_id".$user_id);

        Log::info("commission_amount".$commission_amount);

        $find_referrer_details = Referral::where('user_id', $user_id)->first();
 
        if($find_referrer_details) {
            $find_referrer_details_tier2 = Referral::where('user_id', $find_referrer_details->parent_user_id)->first();
            if($find_referrer_details_tier2) {
                $user_referrer_details = UserReferrer::find($find_referrer_details_tier2->user_referrer_id);
                    
                if($user_referrer_details) {
                if($commission_amount==""){
                    $commission_amount = 0;
                }
                    $user_referrer_details->total_referrals_earnings = $user_referrer_details->total_referrals_earnings + $commission_amount;

                        $user_referrer_details->save();
                    add_to_redeem($user_referrer_details->user_id , $earnings = $commission_amount , $admin_commission = 0);

                } else {
                    
                    Log::info("user_referrer_details not found");

                }
            } else {
                Log::info("No referrals");
            }

        } else {
            Log::info("No referrals");
        }
    }

      public static function referral_token_update($userid){
       $user_token = new UserToken;
        $find_referrer_details =  Referral::where('user_id', $userid)->first();
        if($find_referrer_details) {
           $user_referrer_details = UserReferrer::find($find_referrer_details->user_referrer_id);
      
            if($user_referrer_details) {
            $user_token->token = 1;
            $user_token->user_id = $user_referrer_details->user_id;
              $user_token->save();
              Log::info("Token referrals update");
          }else{
             Log::info("user_referrer_details not found");
          }
          }else{
             Log::info("user_referrer_details not found");
          }
         }
    /**
     * @uses to store the PPV payment failure
     *
     * @param $user_id
     *
     * @param $admin_video_id
     *
     * @param $payment_id
     *
     * @param $reason
     *
     * @param $payment_id = After payment - if any configuration failture or timeout
     *
     * @return boolean response
     */

    public static function user_subscription_payment_failure_save($user_id = 0 , $user_subscription_id = 0 , $reason = "" , $payment_id = "") {

        /*********** DON't REMOVE LOGS **************/

        // Log::info("1- Subscription ID".$subscription_id);

        // Log::info("2- USER ID".$user_id);
        
        // Log::info("3- MESSAGE ID".$reason);

        // Check the user_id and subscription id not null

        /************ AFTER user paid, if any configuration failture  or timeout *******/

        if($payment_id) {

            $user_subscription_payment_details = UserSubscriptionPayment::where('payment_idPayment',$payment_id)->first();

            $user_subscription_payment_details->status = UNPAID;

            $user_subscription_payment_details->reason = "After_Payment"." - ".$reason;

            $user_subscription_payment_details->save();

            return true;

        }

        /************ Before user payment, if any configuration failture or TimeOut *******/

        if(!$user_id || !$user_subscription_id) {

            Log::info('Payment failure save - USER ID and Subscription ID not found');

            return false;

        }

        $user_subscription_payment_details = UserSubscriptionPayment::where('user_id' , $user_id)->where('user_subscription_id' , $user_subscription_id)->where('paid_amount', 0)->first();

        if(empty($user_subscription_payment_details)) {

            $user_subscription_payment_details = new PayPerView;

        }

        $user_subscription_payment_details->expiry_date = date('Y-m-d H:i:s');

        $user_subscription_payment_details->payment_id  = "Payment-Failed";

        $user_subscription_payment_details->user_id = $user_id;

        $user_subscription_payment_details->user_subscription_id = $user_subscription_id;

        $user_subscription_payment_details->reason = "BEFORE-".$reason;

        $user_subscription_payment_details->status = UNPAID;

        $user_subscription_payment_details->save();

        return true;
        

    }
}
