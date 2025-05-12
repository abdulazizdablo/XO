<?php

namespace App\Services;

use App\Models\Branch;
use InvalidArgumentException;

class BranchService
{
    public function getBranchesByCityId($city_id)
    {
        $branches = Branch::where('city_id', $city_id)->get();
        if (!$branches) {
            throw new InvalidArgumentException('There is no branches');
        }

        return $branches;
    }
}
