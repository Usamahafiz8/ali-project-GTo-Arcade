<?php

namespace VanguardLTE\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use VanguardLTE\Game;
use VanguardLTE\GameWinSetting;
use VanguardLTE\StatGame;

class BetWinLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utils:betwinlog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save bet win log of users everyday';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Started saving bet win log for all users');
        $dateFrom = date("Y-m-d", strtotime("-1 days"));
        $timeFrom = ' 00:00';
        $dateFrom = $dateFrom . $timeFrom; 

        $dateTill = date("Y-m-d", strtotime("-1 days"));
        $timeTill = ' 23:59';
        $dateTill = $dateTill . $timeTill;

        $data = DB::select(DB::raw("select A.*, B.parents, B.parent_id, B.shop_id FROM  (select sum(bet) as bet, sum(win) as win, user_id from w_stat_game
        where date_time BETWEEN :dateFrom  and :dateTill group by user_id) A inner join w_users B on B.id = A.user_id"), ['dateFrom' => $dateFrom, 'dateTill' => $dateTill]);
        foreach($data as $player_data)
        {
            \VanguardLTE\BetWin::Create((array)$player_data);
        }
        $this->info('Finished saving bet win log for all users');  
    }
}
