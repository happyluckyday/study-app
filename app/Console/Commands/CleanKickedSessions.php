<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Signature('session:clean-kicked')]
#[Description('Clean up kicked sessions that are older than 24 hours')]
class CleanKickedSessions extends Command
{
    public function handle()
    {
        $deleted = DB::table('sessions')
            ->where('is_kicked', 1)
            ->where('last_activity', '<', Carbon::now()->subHours(24)->getTimestamp())
            ->delete();

        $this->info("Cleaned up {$deleted} old kicked sessions.");
    }
}
