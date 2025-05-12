<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Support\Facades\Log;



class UpdateStockLevelStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $now = Carbon::now()->startOfDay();
        $day_as_hours = Carbon::now()->addHours(24);
		$stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])->with([
             'audits' => function ($query) use ($now, $day_as_hours) {
                   $query->where([['auditable_type','App\Models\StockLevel'],['url','LIKE', '%' . '/orders/store'] ])->whereBetween('updated_at', [$now, $day_as_hours]);
                   
                }
           ])->get();
		//Log::debug('$stock_levels: '. $stock_levels);

      $stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])
           ->with([
               'audits' => function ($query) use ($now, $day_as_hours) {
                  
               }
           ])
           ->get();
        $stock_levels->each(function ($stock_level) {
            $stock_level->audits->each(function ($audit) use ($stock_level) {
			
				
                $old_values =$audit->old_values;
					//dd($audit->old_values['current_stock_level']);// it gives this error: [2024-05-02 10:41:01] local.ERROR: json_decode(): Argument #1 ($json) must be of type string, array given {"exception":"[object] (TypeError(code: 0): json_decode(): Argument #1 ($json) must be of type string, array given at /var/www/vhosts/xo-textile.sy/httpdocs/app/Jobs/TestJob.php:60) [stacktrace]
                $new_values =$audit->new_values;

                if (isset ($old_values['current_stock_level'], $new_values['current_stock_level'])) {
                   $difference = $old_values['current_stock_level'] - $new_values['current_stock_level'];
                  $this->updateStockLevelStatus($stock_level, $difference);
               }
           });
      });
	}

    private function updateStockLevelStatus($stock_level, $difference)
    {
        //if ($difference > 0 && $difference <= 10) {
        if ($difference > 5) {
            $stock_level->status = 'fast-movement';
        } elseif ($difference > 0 && $difference <= 5) {
            $stock_level->status = 'slow-movement';
        }

        try {
            $stock_level->save();
        } catch (\Exception $e) {
            // Handle exception, e.g., log the error
        }
    }
}
