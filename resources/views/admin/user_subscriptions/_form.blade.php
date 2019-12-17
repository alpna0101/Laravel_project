@include('notification.notify')

<div class="row">

    <div class="col-md-10 ">

        <div class="box box-primary">

            <div class="box-header label-primary">

                <b>@yield('title')</b>

                <a href="{{route('admin.user_subscriptions.index')}}" style="float:right" class="btn btn-default">{{tr('view_subscriptions')}}</a>
            </div>

            <form action="{{route('admin.user_subscriptions.save')}}" method="POST" enctype="multipart/form-data" role="form">

                <input type="hidden" name="user_subscription_id" value="{{$user_subscription_details->id}}">

                <input type="hidden" name="unique_id" value="{{$user_subscription_details->unique_id}}">

                <div class="box-body">

                    <div class="form-group col-md-6">

                        <label for="channel_id">{{ tr('choose_channel') }}*</label>

                        <select id="channel_id" name="channel_id" class="form-control select2" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ tr('choose_channel') }}" required>

                            <option>{{ tr('choose_channel') }}</option>

                            @foreach($channels as $channel_details)

                                <option value="{{ $channel_details->id}}" @if($channel_details->is_selected == YES) selected @endif>
                                    {{ $channel_details->name}} ({{$channel_details->username}})
                                </option>

                            @endforeach
                        </select>
                    </div>                    

                    <div class="form-group col-md-6">
                        <label for="title" class="">{{tr('title')}}</label>
                        <input type="text" required name="title" class="form-control" id="title" value="{{$user_subscription_details->title ? $user_subscription_details->title : old('title')}}" placeholder="{{tr('title')}}">
                    </div>
   
                    <div class="form-group col-md-8">
                    
                        <label for="plan" class="">{{tr('plan')}}:
                            
                            <span class="text-danger"><b>( {{tr('plan_note')}} )</b></span>

                        </label>

                        <input type="number" min="1" max="12" required name="plan" class="form-control" id="plan" value="{{($user_subscription_details->plan) ? $user_subscription_details->plan : old('plan')}}" title="{{tr('month_of_plans')}}" placeholder="{{tr('plans')}}">

                    </div>

                    <div class="form-group col-md-4">
                        <label for="amount" class="">{{tr('amount')}}</label>

                        <input type="text" required name="amount" class="form-control" id="amount" placeholder="{{tr('amount')}}" step="any" value="{{($user_subscription_details->amount) ? $user_subscription_details->amount : old('amount')}}" pattern="[0-9]{1,5}" maxlength="5">
                    </div>

                    <div class="form-group col-md-8">

                        <label for="description" class="">{{tr('description')}}</label>
                        
                        <textarea name="description" required class="form-control" placeholder="{{tr('description')}}">{{($user_subscription_details->description) ? $user_subscription_details->description : old('description')}}</textarea>
                    </div>
                    
                    </div>               

                </div>

                <div class="box-footer">
                    <a href="" class="btn btn-danger">{{tr('cancel')}}</a>
                    <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                </div>
            </form>
        
        </div>

    </div>

</div>
