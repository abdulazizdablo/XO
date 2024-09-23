<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\StockLevel;

class AdjustProductMovement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:adjust-product-movement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command aims to adjust stock levels status for each product variation based on how much it will be requested from other warehouses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->startOfDay();
        $day_as_hours = Carbon::now()->addHours(24);

        $stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])
            ->with([
                'audits' => function ($query) use ($now, $day_as_hours) {
                    $query->whereHas('stockLevel', function ($query) use ($now, $day_as_hours) {
                        $query->whereBetween('updated_at', [$now, $day_as_hours]);
                    });
                }
            ])
            ->get();
        $stock_levels->each(function ($stock_level) {
            $stock_level->audits->each(function ($audit) use ($stock_level) {
                $old_values = json_decode($audit->old_values, true);
                $new_values = json_decode($audit->new_values, true);

                if (isset ($old_values['current_stock_level'], $new_values['current_stock_level'])) {
                    $difference = $old_values['current_stock_level'] - $new_values['current_stock_level'];
                    $this->updateStockLevelStatus($stock_level, $difference);
                }
            });
        });
    }

    private function updateStockLevelStatus($stock_level, $difference)
    {
        if ($difference > 0 && $difference >= 10) {
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
