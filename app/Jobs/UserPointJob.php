<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\UserPoint;

use App\User;

use Setting;

use Log;

class UserPointJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $inputRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputRequest)
    {
        $this->inputRequest = $inputRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

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

        $userpointss = $userpoints->count();
        
        if($userpointss == 0){
        if($user_details) {

            $user_point_details = new UserPoint;
                                  
            $user_point_details->user_id = $this->inputRequest['user_id'];

            $user_point_details->owner_user_id = $this->inputRequest['owner_user_id'];

            $user_point_details->point_type = $this->inputRequest['point_type'];

            $user_point_details->video_tape_id = isset($this->inputRequest['video_tape_id']) ? $this->inputRequest['video_tape_id'] : 0;

            $user_point_details->channel_id = isset($this->inputRequest['channel_id']) ? $this->inputRequest['channel_id'] : 0;

            // $user_points = Setting::get('user_points') ?: 0;
            $user_points = $this->inputRequest['points'] ?: 0;


            if($this->inputRequest['point_type'] == POINT_TYPE_DISLIKE_VIDEO) {

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
}
