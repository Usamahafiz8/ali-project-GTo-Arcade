<?php

namespace VanguardLTE\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utils:deletelogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete logs';

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
        $dateFrom = date("Y-m-d", strtotime("-3 days"));
        $timeFrom = ' 00:00';
        $dateFrom = $dateFrom . $timeFrom;

        $this->info('Started deleting bet win log ealier than ' . $dateFrom);
        DB::statement(DB::raw('delete from w_stat_game where date_time < :dateFrom'), ['dateFrom' => $dateFrom]);
        DB::statement(DB::raw('delete from w_game_log where time < :dateFrom'), ['dateFrom' => $dateFrom]);
        $this->info('Finished deleting bet win log');
        return 0;
    }
}
