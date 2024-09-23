<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SizeGuide;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SizeGuideImport implements ToCollection, WithHeadingRow
{

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'name' => 'required|string',
            'values' => 'required|json',
        ];
    }

    public function collection(Collection $rows)
    {
        $headers = $rows->shift()->toArray();
        dd($headers);
        $sizeGuides = [];
        $size_values = [];

        foreach ($rows as $row) {
            dd($row);
            if($row[0] == "name"){
                $size_values = array_slice($row->toArray(), 1);
                continue;
            }

            $category = Category::where('name->en', $row['name'])->first();
            // dd($category);
            $sizeGuide = new SizeGuide([
                'category_id' => $category->id,
                'name' => $row['name'],
                'values' => null
            ]);

            $values = [];

            foreach ($size_values as $columnName => $value) {
                if ($columnName !== 'name') {
                    $values[$columnName] = [
                        $columnName => $value,
                    ];
                }
            }

            $sizeGuide->values = $values;

            $sizeGuides[] = $sizeGuide;
        }

        SizeGuide::insert($sizeGuides);
    }
}
