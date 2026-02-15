<?php

namespace App\Support;

use Carbon\Carbon;

class ListPengawasanLate
{
    public static function isLate(?string $status, ?string $deadline, string $doneStatus): bool
    {
        if (!$deadline) {
            return false;
        }

        if (($status ?? '') === $doneStatus) {
            return false;
        }

        $deadlineDate = Carbon::parse($deadline)->startOfDay();
        $today = Carbon::today();

        return $deadlineDate->lt($today);
    }
}

