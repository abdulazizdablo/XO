<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Illuminate\Support\Arr;
use App\Traits\PhotoTrait;
//use App\Traits\CloudinaryTrait;
use App\Traits\TranslateFields;

class PackageService
{
    use PhotoTrait , TranslateFields;

    public function getAllPackages()
    {
        $packages = Package::valid()->paginate(10);

        if (!$packages) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }

        $package_fields = [
            'name',
            'type',
            'photo_url',

    ];

    return $this->getPaginatedTranslatedFields($packages, $package_fields);
    }

    public function getAllValidPackages()
    {
        $packages = Package::valid()->paginate(10);

        if (!$packages) {
            throw new InvalidArgumentException('There Is No Categories Available');
        }
        $package_fields = [
            'name',
            'type',
            'photo_url',

    ];
    return $this->getPaginatedTranslatedFields($packages, $package_fields);
    }

    public function getPackage($package_id) : Package
    {
        $package = Package::find($package_id);

        if (!$package) {
            throw new InvalidArgumentException('Package not found');
        }

        $package_fields = [
            'name',
            'type',
            'photo_url',

    ];
    return $package->getFields($package_fields);
    }

    public function createPackage(array $data): Package
    {
        $nameKeys = Arr::where(array_keys($data), function ($key) {
            return strpos($key, 'name_') === 0;
        });

        $names = [];
        foreach ($nameKeys as $key) {
            $locale = str_replace('name_', '', $key);
            $names[$locale] = $data[$key];
        }

        $photo_path = $this->saveImage($data['image'],'photo','images/packages');

        $package = Package::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'valid' => $data['valid'],
            'photo_url' => $photo_path,
            'thumbnail' => 'dasd',
        ]);

        if(!$package){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $package;
    }

    public function updatePackage(array $data, $package_id): Package
    {
        $package = Package::find($package_id);

        $package->update($data);

        if(!$package){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $package;
    }

    public function delete(int $package_id) : void
    {
        $package = Package::find($package_id);

        if (!$package) {
            throw new InvalidArgumentException('Package not found');
        }

        $package->delete();
    }

    public function forceDelete(int $package_id) : void
    {
        $package = Package::find($package_id);

        if (!$package) {
            throw new InvalidArgumentException('Package not found');
        }

        $package->forceDelete();
    }

}
