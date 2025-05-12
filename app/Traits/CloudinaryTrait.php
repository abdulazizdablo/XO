<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary as CloudinaryUpload;
use Cloudinary\Transformation\Delivery;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Quality;
use Cloudinary\Transformation\Effect;
use Cloudinary\Cloudinary;

trait CloudinaryTrait
{
  protected function saveImage($photo, $folder): string
  {
    // Check if the photo is null
    if ($photo == null) return '';
	  $path = $folder;
	  $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
	  $uniqueName = $originalName . '_' . time() . '_' . uniqid();
	  $result = $photo->storeOnCloudinaryAs($path, $uniqueName);
	  $publicId = $result->getPublicId();
	
    $cld = new Cloudinary();
	  
	  
    return   $cld->image($publicId)
      // here I have add more than one example for resize the image
    // ->resize(Resize::thumbnail())
       // ->resize(Resize::scale(200, 200))
    // ->resize(Resize::fit())
      ->quality(Quality::auto())//there is more than one choice you can configure [autoBest()autoGood() autoEco()||autoLow() ]
      ->delivery(
        Delivery::format(
        Format::auto()
      ))->toUrl();
  }
	
	
	/*function getPublicIdFromUrl($url) {
    // Remove the protocol and domain
    $path = parse_url($url, PHP_URL_PATH);

    // Split the path into parts
    $parts = explode('/', $path);

    // The public ID is the part of the path that comes after "upload"
    // and before the file extension.
    // We'll find the index of "upload" and then get the next part.
    $uploadIndex = array_search('upload', $parts);
    if ($uploadIndex !== false && isset($parts[$uploadIndex + 1])) {
        // If there's a transformation, we need to skip it
        if (strpos($parts[$uploadIndex + 1], '.') === false) {
            $publicId = $parts[$uploadIndex + 1];
        } else {
            $publicId = $parts[$uploadIndex + 2];
        }
        return $publicId;
    }

    return null;
}
*/
	

 protected function getImageUrl($publicId, $width = null, $height = null): string
  {

/*if($width = 1080 || $height =1920){
$width = 1180;
	$height = 1920 + 100;
	
	
	 $aspect_ratio =( $width ) / ($height + 200 );
	
 $cld = new Cloudinary();
    $resize = new Resize('fill',$width,$height);
	 $resize->aspectRatio($aspect_ratio);

if (isset($width) && isset($height)) {

    $url = $cld->image($publicId)
      ->resize($resize)
        ->quality(Quality::auto())
        ->delivery(Delivery::format(Format::auto()))
        ->toUrl();
}
return $url;

}
*/
	 
	 
//dd($width , $height );
	 
	/* $aspect_ratio = 0.0;
	 $cld = 0;
	 $resize = 0;
	 $url = 0;
	 
	 
	 if($width == 841 && $height == 1642 ){
	 
	  $width= $width + 300;
	 
	 $height = $height + 300;
	 
$aspect_ratio =( $width + 400 ) / ($height + 200 );
	 
	
 $cld = new Cloudinary();
    $resize = new Resize('fill',$width,$height);
	 $resize->aspectRatio($aspect_ratio);

if (isset($width) && isset($height)) {

    $url = $cld->image($publicId)
      ->resize($resize)
        ->quality(Quality::auto())
        ->delivery(Delivery::format(Format::auto()))
        ->toUrl();
} else {
    $url = $cld->image($publicId)
        ->quality(Quality::auto())
        ->delivery(Delivery::format(Format::auto()))
        ->toUrl();
}

return $url;
  }
	 
	 
	 }
	 */

	 
$aspect_ratio =( $width ) / ($height + 200 );
	 
	
 $cld = new Cloudinary();
    $resize = new Resize('fill',$width,$height);
	 $resize->aspectRatio($aspect_ratio);

if (isset($width) && isset($height)) {

    $url = $cld->image($publicId)
      ->resize($resize)
        ->quality(Quality::auto())
        ->delivery(Delivery::format(Format::auto()))
        ->toUrl();
} else {
    $url = $cld->image($publicId)
        ->quality(Quality::auto())
        ->delivery(Delivery::format(Format::auto()))
        ->toUrl();
}

return $url;
  }
  protected function getFolderContaints($folder)
  {
    //this function return an array of folder images or folder containts
    $cld = new Cloudinary();

    $searchApi = $cld->SearchApi();
    $searchFoldersApi = $cld->SearchFoldersApi();
    // Retrieve the folder details
      // $folder = $searchFoldersApi->expression($folder)->execute();
    
      // Retrieve the images within the folder
         $images = $searchApi->expression("folder=$folder")->maxResults(100)->execute();
    // Extract the URLs from the images
      $imageUrls = collect($images['resources'])->pluck('public_id'); //here you can retrive public_id or secure path
    // Return the list of image URLs
    return $imageUrls;
  }
  
  
  /*public function getPublicIdFromUrl($url) {
    // Define the regex pattern to match a word followed by a number
    $pattern = '/(\w+)\/(\d+)/';

    // Perform the regex search on the URL
    if (preg_match($pattern, $url, $matches)) {
        // Combine the matched word and number to form the public ID
        $publicId = $matches[1] . '/' . $matches[2];

        return $publicId;
    } else {
        // No match found
        return null;
    }
}


  
  */
  public function getPublicIdFromUrl($url) {
    // Define the regex pattern to match a word followed by a number
    $pattern = '/v1\/(.*?)\/(.*?)\?/';

    // Perform the regex search on the URL
    if (preg_match($pattern, $url, $matches)) {
		

        // Combine the matched word and number to form the public ID
        $publicId = $matches[1] . '/' . $matches[2];

        return $publicId;
    } else {
        // No match found
        return null;
    }
}


}