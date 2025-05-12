<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateStockLevelStatusJob;
use App\Jobs\PushUndeliveredOrdersJob;
use App\Jobs\SetOrderClosedJob;
use App\Jobs\SetNewProducts;
use App\Jobs\RemoveProductsFromFlash;
use App\Jobs\DeleteUnpaidOrders;
use App\Jobs\DeleteUnpaidGiftCards;
use App\Jobs\DeactivateCoupon;
use App\Jobs\UpdateTransactionFailed;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
	    $schedule->job(new UpdateStockLevelStatusJob)->dailyAt("00:00");
		$schedule->job(new PushUndeliveredOrdersJob)->dailyAt("00:00");
		$schedule->job(new SetOrderClosedJob)->dailyAt("00:00");
		$schedule->job(new SetNewProducts)->everyMinute();
		$schedule->job(new RemoveProductsFromFlash)->everyMinute();
		$schedule->job(new DeleteUnpaidOrders)->everyMinute();
		$schedule->job(new DeleteUnpaidGiftCards)->everyMinute();
		$schedule->job(new DeactivateCoupon)->everyMinute();
	    $schedule->job(new UpdateTransactionFailed)->everyMinute();

		
       // $schedule->command('adjust-product-movement')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
