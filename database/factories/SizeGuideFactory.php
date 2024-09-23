<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SizeGuide>
 */
class SizeGuideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $category = Category::inRandomORder()->first();
        return [
            'category_id' => $category->id ,
            'name' => $category->getTranslation('name', 'en'),
            'values' => json_encode([
                'sizes' => [
                    [
                        "value" => "XXS"
                    ],
                    [
                        "value" => "XS"
                    ],
                    [
                        "value" => "S"
                    ],
                    [
                        "value" => "M"
                    ],
                    [
                        "value" => "L"
                    ],
                    [
                        "value" => "XL"
                    ],
                    [
                        "value" => "XXL"
                    ],
                    [
                        "value" => "1XL"
                    ],
                    [
                        "value" => "2XL"
                    ],
                    [
                        "value" => "3XL"
                    ],
                    [
                        "value" => "4XL"
                    ]
                ],
                "Bust" => [
                    [
                        "value" => "78.0"
                    ],
                    [
                        "value" => "82.0"
                    ],
                    [
                        "value" => "86.0"
                    ],
                    [
                        "value" => "92.0"
                    ],
                    [
                        "value" => "98.0"
                    ],
                    [
                        "value" => "104.0"
                    ],
                    [
                        "value" => "110.0"
                    ],
                    [
                        "value" => "116.0"
                    ],
                    [
                        "value" => "124.0"
                    ],
                    [
                        "value" => "132.0"
                    ],
                    [
                        "value" => "140.0"
                    ]
                ],
                "Waist" => [
                    [
                        "value" => "59.0"
                    ],
                    [
                        "value" => "63.0"
                    ],
                    [
                        "value" => "66.0"
                    ],
                    [
                        "value" => "72.0"
                    ],
                    [
                        "value" => "78.0"
                    ],
                    [
                        "value" => "85.0"
                    ],
                    [
                        "value" => "92.0"
                    ],
                    [
                        "value" => "99.0"
                    ],
                    [
                        "value" => "108.0"
                    ],
                    [
                        "value" => "117.0"
                    ],
                    [
                        "value" => "126.0"
                    ]
                ]
            ], true),
        ];
    }
}
