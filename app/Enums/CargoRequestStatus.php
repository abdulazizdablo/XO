<?php



namespace App\Enums;


enum CargoRequestStatus
{
    const OPEN = 1;
    const PENDING = 2;
    const CLOSED = 3;
    const CANCELED = 4;
}
