<?php

namespace App\Console\Commands;

use App\Models\AttendancePhoto;
use Illuminate\Console\Command;

class PruneExpiredAttendancePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:prune-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete attendance scan photos older than 3 months automatically';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = AttendancePhoto::pruneExpired();
        $this->info("Pruned {$deleted} expired attendance photo(s) (> 3 months).");

        return self::SUCCESS;
    }
}
