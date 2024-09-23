<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class PhotosSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
{
    $images = ['kidsPhotos', 'menPhotos', 'homePhotos', 'womenPhotos'];
    $imageKeys = array_intersect_key($this->input(), array_flip($images));

    $rules = [
        'key' => 'required|string',
        'navBar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        'banner_header' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        'banner_middle_page' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        'banner_bottom_page' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ];

    // Dynamically add required rules for image inputs if they exist in the request
    foreach ($imageKeys as $key => $value) {
        $rules[$key] = 'required|json';
    }

    return $rules;
}

}
