<?php 
namespace VanguardLTE\Http\Controllers\Api\Games
{

    use VanguardLTE\User;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class GamesController extends \VanguardLTE\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
        }
        
        public function index(\Illuminate\Http\Request $request)
        {
            if( !auth()->user()->hasRole('user') ) 
            {
                return $this->errorWrongArgs(trans('app.no_permission'));
            }
            $take = 1000000;
            $games = \VanguardLTE\Game::select('games.*')->where('shop_id', auth()->user()->shop_id)->orderBy('name', 'ASC');
            if( $request->id != '' ) 
            {
                $games = $games->where('games.id', $request->id);
            }
            if( $request->name != '' ) 
            {
                $names = explode('|', $request->name);
                $games = $games->whereIn('games.name', (array)$names);
            }
            if( $request->search != '' ) 
            {
                $games = $games->where('games.title', 'like', '%' . $request->search . '%');
            }
            if( $request->device != '' ) 
            {
                $devices = explode('|', $request->device);
                $games = $games->whereIn('games.device', (array)$devices);
            }
            if( $request->view != '' ) 
            {
                $games = $games->where('games.view', $request->view);
            }
            if( $request->labels != '' ) 
            {
                $labels = explode('|', $request->labels);
                $games = $games->whereIn('games.label', (array)$labels);
            }
            if( $request->category != '' ) 
            {
                $categories = explode('|', $request->category);
                foreach( $categories as $cat ) 
                {
                    $inner = \VanguardLTE\Category::where(['parent' => $cat])->get();
                    if( $inner ) 
                    {
                        $categories = array_merge($categories, $inner->pluck('id')->toArray());
                    }
                }
                $games = $games->join('game_categories', 'game_categories.game_id', '=', 'games.original_id');
                $games = $games->whereIn('game_categories.category_id', (array)$categories);
            }
            else if( !isset($request->subcat) ) 
            {
                $take = 25;
            }
            if( $request->subcat != '' && ($request->subcat == 'new' || $request->subcat == 'hot') ) 
            {
                $games = $games->where('games.label', $request->subcat);
            }
            $games = $games->paginate($take);
            return $this->respondWithPagination($games, new \VanguardLTE\Transformers\GameTransformer());
        }

        public function gamelist(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $username = $data['username'];
            $token = $data['token'];
            $users = \VanguardLTE\User::where('username', '=', $username)->get();
            $user = $users[0];
            if($user->api_token != $token)
                return $this->respondWithArray(['status'=>'failure', 'error'=>'invalid authroization']);
            
            //get game list
            $games = \VanguardLTE\Game::where([
                'view' => 1, 
                'shop_id' => $user->shop_id
            ]);
            $games_firelink = (clone $games)->where('category_temp', '=', 1)->get();
            $games_fish = (clone $games)->where('category_temp', '=', 2)->get();
            $games_slot = (clone $games)->where('category_temp', '=', 3)->get();
            $gameData = [
                'slots' => [],
                'fishes' => [],
                'firelinks' => []
            ];

            foreach($games_slot as $game)
            {
                $data = [
                    'id' => $game->id, 
                    'src' => '/frontend/Default/ico/'. $game->name .'.png',
                    'size' => 'small',
                    'tag' => 'hot',
                    'url' => '/game/open/'.$game->name.'?api_exit=/',
                    'orientation' => $game->orientation
                ];
                $gameData['slots'][] = $data;
            }

            foreach($games_fish as $game)
            {
                $data = [
                    'id' => $game->id, 
                    'src' => '/frontend/Default/ico/'. $game->name .'.png',
                    'size' => 'big',
                    'tag' => 'new',
                    'url' => '/game/open/'.$game->name.'?api_exit=/',
                    'orientation' => $game->orientation
                ];
                $gameData['fishes'][] = $data;
            }

            foreach($games_firelink as $game)
            {
                $data = [
                    'id' => $game->id, 
                    'src' => '/frontend/Default/ico/'. $game->name .'.png',
                    'size' => 'big',
                    'tag' => 'hot',
                    'url' => '/game/open/'.$game->name.'?api_exit=/',
                    'orientation' => $game->orientation
                ];
                $gameData['firelinks'][] = $data;
            }
            return $this->respondWithArray($gameData);
        }
    }

}
