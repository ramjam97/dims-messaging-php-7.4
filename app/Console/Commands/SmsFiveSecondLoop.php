<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SmsFiveSecondLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sms-five-second-loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously runs the SMS processor every 5 seconds.';


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
        while (true) {
            try {
                Artisan::call('app:process-pending-sms');
            } catch (\Throwable $e) {
                Log::error("Error running SMS job: " . $e->getMessage());
            }

            sleep(5);
        }
        return 0;
    }
}
