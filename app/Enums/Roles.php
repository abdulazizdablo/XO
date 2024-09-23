<?php



namespace App\Enums;
enum Roles: int
{

    const MAIN_ADMIN = 1;
    const DATA_ENTRY = 2;
    const WAREHOUSE_ADMIN = 3;
    const WAREHOUSE_MANAGER = 4;
    const DELIVERY_ADMIN = 5;
    const OPERATION_MANAGER = 6;
    const DELIVERY_BOY = 7;
}
