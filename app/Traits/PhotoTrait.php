<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;


trait PhotoTrait
{
    protected function saveImage($photo, $alias_name, $folder): string
    {
        // Check if the photo is null
        if ($photo == null) return '';

        // Generate a random UUID string
        $random = Str::uuid()->toString();

        // Get the file extension of the uploaded photo
        $file_extension = $photo->getClientOriginalExtension();

        // Create a unique file name for the uploaded photo
        $file_name = $alias_name . $random . '.' . $file_extension;

        // Set the path where the file will be saved
        $path = $folder;

        // Move the uploaded photo to the specified folder with the created file name
        $photo->move($path, $file_name);

        // Return the path to the saved file
         return 'https://api.xo-textile.sy/public/'. $path . '/' . $file_name;
    }

    protected function saveThumbnail($photo, $alias_name, $thumbnailfolder, $format, $quality = 80, $width = 300, $height = 300): string
    {
        // Check if the photo is null
        if ($photo == null) return '';

        // Get the file extension of the uploaded photo
        $file_extension = $photo->getClientOriginalExtension();

        // Create an instance of the `Image` class from the uploaded photo
        if ($file_extension == 'webp') {
            $photo = imagecreatefromwebp($photo);
            $photo = Image::make($photo);
        } else {
            $photo = Image::make($photo);
        }

        // Generate a random UUID string
        $random = Str::uuid()->toString();

        // Create a unique file name for the thumbnail image
        $file_name = $alias_name . $random;

        // Set the path where the thumbnail image will be saved
        $path = $thumbnailfolder;

        // $photo->save($path,$file_name);
        /**
         * Generate Thumbnail Image Upload on Folder Code
         */
        // $photo->insert('public/watermark.png');

        // Resize the image to the specified width and height
        $photo->resize($width, $height);

        // Save the thumbnail image to the specified folder with the created file name and format
        $photo->save($path.'/'.$file_name.".$format", $quality, $format);

        // Return the path to the saved thumbnail image
        return $path.'/'.$file_name.".$format";
    }

    protected function saveBase64File($base64, $alias_name, $folder): string
    {
        // Check if the base64 encoded string is null
        if ($base64 == null) return '';

        // Determine the file extension based on the first character of the base64 encoded string
        switch (mb_substr($base64, 0, 1)) {
            case '/':
              $extension = 'jpeg';
              break;
            case 'i':
              $extension = 'png';
              break;
            case 'R':
              $extension = 'gif';
              break;
            case 'U':
              $extension = 'webp';
              break;
            case 'J':
              $extension = 'pdf';
              break;
            case "A":
              $extension = "mp4";
              break;
            case 'S':
              $extension = 'mp3';
              break;
            default:
              $extension = 'unknown';
          }

        // Get the base64 encoded string and generate a random ULID string
        $file = $base64;
            // $extension = explode('/', mime_content_type($file))[1];
            // $file = str_replace("data:image/$extension;base64,", '', $file);
            // $file = str_replace(' ', '+', $file);
        $random = Str::ulid();

        // Create the file name with the appropriate extension
        $fileName = $alias_name.$random.'.'.$extension;

        // Save the file to the specified folder
        File::put($folder.'/'.$fileName, base64_decode($file));

        // Return the path to the saved file
        return 'https://api.xo-textile.sy/public/'.$folder.'/'.$fileName;
    }

    // public function verifyAndUpload(Request $request, $fieldname = 'image', $directory = 'images' ) {

    //     if( $request->hasFile( $fieldname ) ) {

    //         if (!$request->file($fieldname)->isValid()) {

    //             flash('Invalid Image!')->error()->important();

    //             return redirect()->back()->withInput();

    //         }

    //         return $request->file($fieldname)->store($directory, 'public');

    //     }

    //     return null;

    // }

}
