<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    /**
     * Handle the YourModel "created" event.
     *
     * @param \App\Models\Setting $Setting
     * @return void
     */
    public function created(Setting $setting)
    {
        // Clear the cache when a new model is created
        Cache::forget('settings');
        Cache::forget($setting->key);
    }

    /**
     * Handle the Setting "updated" event.
     *
     * @param \App\Models\Setting $yourModel
     * @return void
     */
    public function updated(Setting $setting)
    {
        // Clear the cache when a model is updated
        Cache::forget('settings');
        Cache::forget($setting->key);
    }

    /**
     * Handle the YourModel "deleted" event.
     *
     * @param \App\Models\Setting $yourModel
     * @return void
     */
    public function deleted(Setting $setting)
    {
        // Clear the cache when a model is deleted
        Cache::forget('settings');
        Cache::forget($setting->key);
    }
}
