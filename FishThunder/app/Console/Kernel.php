<?php 
namespace VanguardLTE\Console
{
    class Kernel extends \Illuminate\Foundation\Console\Kernel
    {
        protected $commands = [
            Commands\BetWinLog::class,
            Commands\ProcessBonus::class,
            Commands\DeleteLogs::class
        ];
        protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
        {
            $schedule->command('utils:betwinlog')->dailyAt('00:00');
            $schedule->command('utils:processbonus')->dailyAt('00:00');
            
            // $schedule->command('queue:work --daemon')->everyMinute()->withoutOverlapping();
            // $schedule->call(function()
            // {
            //     \Spatie\DbDumper\Databases\MySql::create()->setDbName(config('database.connections.mysql.database'))->setUserName(config('database.connections.mysql.username'))->setPassword(config('database.connections.mysql.password'))->dumpToFile(base_path() . '/backups/' . date('Hi_dmY') . '.sql');
            // })->daily();
            // $_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332 = 45;
            // $schedule->call(new Schedules\Tournaments($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\SMSBonuses($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\Securities($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyFiveMinutes();
            // $schedule->call(new Schedules\ShopCreates($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\ShopDeletes($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\Synchronization($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\QuickShops($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\HierarchyUsersCache($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyFiveMinutes();
            // $schedule->call(new Schedules\TreeCache($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyFiveMinutes();
            // $schedule->call(new Schedules\HotGamesCache($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyThreeHours();
            // $schedule->call(new Schedules\BankDecrease($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyThreeHours();
            // $schedule->call(new Schedules\Notifications($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\ClearLogs($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\GameEvents($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyFiveMinutes();
            // $schedule->call(new Schedules\RemoveGamesWithoutFolder($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\SMSMailings($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
            // $schedule->call(new Schedules\EveryFiveMinutesCleanUp($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyFiveMinutes();
            // $schedule->call(new Schedules\EveryMinuteCleanUp($_obf_0D2F242F2D052B0938193F2D0D2F192F27160616153332))->everyMinute();
        }

        

        protected function commands()
        {
            require(base_path('routes/console.php'));

            \Artisan::command('utils:newgame {categoryid} {originalid}', function ($categoryid, $originalid) {
                set_time_limit(0);
                $this->info("Begin adding new game to all shop");
                
                $buffgame = \VanguardLTE\Game::where('id', $originalid)->first();
                if (!$buffgame)
                {
                    $this->error('Can not find original game of new game');
                    return;
                }
                $shop_ids = \VanguardLTE\Shop::all()->pluck('id')->toArray();
                $data = $buffgame->toArray();
                foreach ($shop_ids as $id)
                {
                    if (\VanguardLTE\Game::where(['shop_id'=> $id, 'original_id' => $originalid])->first())
                    {
                        $this->info("Game already exist in " . $id . " shop");
                    }
                    else{
                        $data['shop_id'] = $id;
                        $game = \VanguardLTE\Game::create($data);
                        $cat = \VanguardLTE\Category::where(['shop_id' => $id, 'original_id' => $categoryid])->first();
                        if ($cat){
                            \VanguardLTE\GameCategory::create(['game_id'=>$game->id, 'category_id'=>$cat->id]);
                        }
                    }
                }
                $this->info('End');
            });
        }
    }

}
