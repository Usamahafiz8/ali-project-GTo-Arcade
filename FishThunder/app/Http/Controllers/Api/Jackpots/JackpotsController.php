<?php 
namespace VanguardLTE\Http\Controllers\Api\Jackpots
{

    use Carbon\Carbon;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class JackpotsController extends \VanguardLTE\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
        }
        public function index(\Illuminate\Http\Request $request)
        {
            $jackpots = \VanguardLTE\JPG::where('shop_id', auth()->user()->shop_id)->orderBy('date_time', 'DESC');
            if( $request->id != '' ) 
            {
                $ids = explode('|', $request->id);
                $jackpots = $jackpots->whereIn('id', (array)$ids);
            }
            if( $request->search != '' ) 
            {
                $jackpots = $jackpots->where('name', 'like', '%' . $request->search . '%');
            }
            $jackpots = $jackpots->paginate(100000);
            return $this->respondWithPagination($jackpots, new \VanguardLTE\Transformers\JackpotTransformer());
        }

        public function jackpot_status(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $username = $data['username'];
            $token = $data['token'];
            $users = \VanguardLTE\User::where('username', '=', $username)->get();
            $user = $users[0];
            if($user->api_token != $token)
                return $this->respondWithArray(['status'=>'failure', 'error'=>'invalid authroization']);

            //save login status of player to redis
            $player_key = "player_time_";
            $redis = app()->make('redis');
            $data = ["time" => time(), "ip" => request()->ip()];

            $redis->set($player_key . $user->id, json_encode($data));
            
            //calc bonuses
            $jNames = [
                'bronze',
                'silver',
                'gold',
                'platinum'
            ];
            $jCnt = 0;
            $res = [
                'status' => 'success', 
                'content' => [], 
                'i' => 1
            ];
            
            $data = \VanguardLTE\JPG::where('shop_id', $user->shop_id)->get();
            foreach( $data as $jackpot ) 
            {
                $res['content'][] = [
                    'name' => $jNames[$jCnt], 
                    'jackpot' => $jackpot->balance                    
                ];
                $jCnt++;
                if( $jCnt > 5 ) 
                {
                    break;
                }
            }

            $date = Carbon::now()->addMinutes(-1)->format('Y-m-d H:i:s');
            $jackpot_wins = \VanguardLTE\StatGame::where('game', 'like', '% JPG %')
                            ->where('user_id', '=', $user->id)
                            ->where('date_time', '>=', $date)->get();
            
            $res['jackpots'] = [];
            foreach($jackpot_wins as $jackpot_win)
            {
                $game = $jackpot_win->game;
                $game = explode(' ', $game);
                $jackpot_id = $game[2];                
                $jackpot_name = \VanguardLTE\JPG::where('id', '=', $jackpot_id)->get()[0]->name;
                $res['jackpots'][] = ['name' => $jackpot_name, 'win' => $jackpot_win->win, 'date' => $jackpot_win->date_time];
            }

            //cash back bonus
            $cashback_wins = \VanguardLTE\Statistic::where('title', '=', 'CB')
                            ->where('user_id', '=', $user->id)
                            ->where('created_at', '>=', $date)->get();
            $res['cashback'] = [];
            foreach($cashback_wins as $cashback)
            {
                $res['cashback'][] = ['name' => 'cashback', 'win' => $cashback->sum, 'date' => $cashback->created_at];
            }

            $res['reward_available'] = false;
            if($user->reward_base > 0)
                $res['reward_available'] = true;
            $res['user_balance'] = $user->balance;
            return json_encode($res);
        }
    }

}
