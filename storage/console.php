<?php

//\Illuminate\Support\Facades\Schedule::command(\NormanHuth\Library\Commands\CleanupSoftDeletesCommand::class)->daily();
//\Illuminate\Support\Facades\Schedule::command(\NormanHuth\Library\Commands\UpdateDisposableEmailDomainsCommand::class)->daily();

//\Illuminate\Support\Facades\Schedule::command('queue:work', [
//    //'--queue' => 'default',
//    //'--timeout' => 30,
//    //'--sleep' => 3,
//    '--stop-when-empty',
//])->everyMinute()->withoutOverlapping()
//    ->when(function () {
//        return $this->app['config']->get('queue.default') == 'database';
//    });
