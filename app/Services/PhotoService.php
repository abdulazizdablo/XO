<?php

namespace App\Services;

use App\Models\Photo;
use InvalidArgumentException;

class PhotoService
{
    public function getPhoto(int $photo_id) : Photo
    {
        $photo = Photo::findOrFail($photo_id);

        return $photo;
    }

    public function createPhoto(array $data): Photo
    {
        $photo = Photo::create([
            'object_id' => $data['object_id'],
            'object_type' => $data['object_type'],
            'color' => $data['color'],
            'path' => $data['path'],
            'thumbnail' => $data['thumbnail'],
        ]);

        if(!$photo){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $photo;
    }

    public function updatePhoto(array $data,int $photo_id): Photo
    {
        $photo = Photo::findOrFail($photo_id);
       
        $photo->update([
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'],
        ]);

        return $photo;
    }

    public function show(int $photo_id): Photo
    {
        $photo = Photo::findOrFail($photo_id);

        return $photo;
    }

    public function delete(int $photo_id) : void
    {
        $photo = Photo::findOrFail($photo_id);

        $photo->delete();
    }

    public function forceDelete(int $photo_id) : void
    {
        $photo = Photo::findOrFail($photo_id);

        $photo->forceDelete();
    }
}
