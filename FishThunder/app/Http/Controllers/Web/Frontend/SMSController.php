<?php 

namespace VanguardLTE\Http\Controllers\Web\Frontend
{
    use Twilio\Rest\Client;
    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class SMSController extends \VanguardLTE\Http\Controllers\Controller
    {
        public function index(\Illuminate\Http\Request $request)
        {
            $dataSet = $request->all();
            $sms = \VanguardLTE\SMS::where('message_id', $dataSet['messageId'])->first();
            if( $sms ) 
            {
                $sms->update(['status' => $dataSet['status']]);
            }
        }

        public function sendSMS(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            if($user == null)
            {
                return json_encode(['status' => 'fail', 'message' => 'unauthorized user']);
            }
            $data = $request->all();
            $user_id = $user->id;
            $token = $data['message_id'];
            $phone = $data['phone'];
            $code = random_int(100000, 999999);

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");

            $client = new Client($account_sid, $auth_token);
            $message = 'Fish Thunder : Use this one-time verification code to complete the bonus sign up. Code: ' . $code;
            $client->messages->create($phone, ['from' => $twilio_number, 'body' => $message] );

            //save into sms list
            $sms_data = [
                'message' => $code,
                'user_id' => $user_id,
                'type' => 'verification',
                'message_id' => $token,
                'phone' => $phone,
                'status' => 0
            ];
            
            \VanguardLTE\SMS::create($sms_data);
        }

        public function verifySMS(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            if($user == null)
            {
                return json_encode(['status' => 'fail', 'message' => 'unauthorized user']);
            }
            $data = $request->all();
            $code = $data['code'];
            $message_id = $data['message_id'];

            $sms = \VanguardLTE\SMS::where('message', '=', $code)->where('message_id', '=', $message_id)->where('status','=',0)->get();
            if(count($sms) > 0)
            {
                $sms = $sms[0];
                $phone = $sms->phone;
                
                $bonusWon = false;
                if($user->phone == '')
                {
                    //first phone registration and phone signup bonus
                    $bonusWon = true;
                    $bonus_amount = 5;
                    $last_balance = $user->balance;
                    $user->increment('balance', $bonus_amount);
                    $result_balance = $last_balance + $bonus_amount;
                    
                    //save statistics log
                    \VanguardLTE\Statistic::create([
                        'title' => 'Bonus',
                        'user_id' => $user->id,
                        'payeer_id' => 1,
                        'description' => 'Phone Signup Bonus',
                        'system' => 'bonus',
                        'sum' => $bonus_amount,
                        'last_balance' => $last_balance,
                        'result_balance' => $result_balance,
                        'shop_id' => $user->shop_id,
                        'item_id' => 2 //phone signup bonus
                    ]);
                }
                $user->phone = $phone;                
                $user->save();
                $sms->status = 1;
                $sms->save();

                return json_encode(['status' => 'success', 'data' => ['bonusWon' => $bonusWon, 'amount' => $bonus_amount, 'balance' => $user->balance]]);
            }
            else
            {
                return json_encode(['status' => 'fail', 'message' => 'Code Invalid']);
            }
        }

        public function verifyReferral(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            if($user == null)
            {
                return json_encode(['status' => 'fail', 'message' => 'unauthorized user']);
            }
            if($user->phone_ref != '')
            {
                return json_encode(['status' => 'fail', 'message' => 'You already registered referral phone number']);
            }
            $data = $request->all();
            $ref_phone = $data['ref_phone'];
            $ref_users = \VanguardLTE\User::where('phone', '=', $ref_phone)->get();
            if(count($ref_users) > 0)
            {
                $ref_user = $ref_users[0];
                $bonus_amount = 5;
                $last_balance = $ref_user->balance;
                $ref_user->increment('balance', $bonus_amount);
                $result_balance = $last_balance + $bonus_amount;
                
                //save statistics log
                \VanguardLTE\Statistic::create([
                    'title' => 'Bonus',
                    'user_id' => $ref_user->id,
                    'payeer_id' => 1,
                    'description' => 'Referral Bonus',
                    'system' => 'bonus',
                    'sum' => $bonus_amount,
                    'last_balance' => $last_balance,
                    'result_balance' => $result_balance,
                    'shop_id' => $ref_user->shop_id,
                    'item_id' => 3 //referral code bonus
                ]);

                $user->phone_ref = $ref_phone;
                $user->save();
                return json_encode(['status' => 'success', 'message' => 'Referral phone number registered successfully.']);
            }
            else
            {
                return json_encode(['status' => 'success', 'message' => 'Referral phone number does not exist.']);
            }
        }
    }

}
