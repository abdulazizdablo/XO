<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Transaction;

use Illuminate\Support\Facades\Log;



class UpdateTransactionFailed implements ShouldQueue
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
		
		

$pending_transactions = Transaction::where('status', 'pending')->whereNot('transaction_uuid','refund')
    ->where('updated_at', '<', Carbon::now()->subMinutes(20))
    ->update(['status' => 'failed']);

      
}
}
