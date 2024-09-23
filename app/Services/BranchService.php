<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use InvalidArgumentException;

class BranchService
{
    public function getAllBranches()
    {
        $branches = Branch::paginate();

        if (!$branches) {
            throw new InvalidArgumentException('There Is No Branches Available');
        }

        return $branches;
    }

    public function getBranchesByCityId($city_id)
    {
        $branches = Branch::where('city_id', $city_id)->get();
        if (!$branches) {
            throw new InvalidArgumentException('There is no branches');
        }

        return $branches;
    }

    public function getBranch($branch_id)
    {
        $branch = Branch::find($branch_id);

        if (!$branch) {
            throw new InvalidArgumentException('branch not found');
        }

        return $branch;
    }

    public function createBranch(array $data): Branch
    {
        $branch = Branch::create($data);


        if (!$branch) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $branch;
    }

    public function updateBranch(array $data): Branch
    {
        $branch = Branch::find($data['id']);

        $branch->update($data);
        if (!$branch) {
            throw new InvalidArgumentException('There Is No Branchs Available');
        }
        return $branch;
    }

    public function show(int $branch_id): Branch
    {
        $branch = Branch::findOrFail($branch_id);

        return $branch;
    }

    public function delete(int $branch_id): void
    {
        $branch = Branch::findOrFail($branch_id);
		
        $branch->delete();
    }

    public function forceDelete(int $branch_id): void
    {
        $branch = Branch::findOrFail($branch_id);

     

        $branch->forceDelete();
    }
}
