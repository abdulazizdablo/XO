<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\HeadingRowImport;
use Throwable;

class ProductsImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    use Importable;

    protected $languages = [];
    private $isFirstRow = true;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        if ($this->isFirstRow) {
            foreach ($row as $value) {
                preg_match_all('/\((.*?)\)/', $value, $match);
                $this->languages = array_unique(array_merge($this->languages, $match[1]));
            }
            $this->isFirstRow = false;
        }

        // dd($row);
        if ($this->isFirstRow) {
            // dd($this->languages);
            dd($row);
            // Product::create([
            //     'sub_category_id' => $row['sub_category_id'],
            //     'item_no' => $row['item_no'],
            //     'name' => json_encode([
            //         'en' => $row['name_en'],
            //         'ar' => $row['name_ar'],
            //     ]),
            //     'description' => $row['description'],
            //     'price' => $row['price'],
            //     'material' => $row['material'],
            //     'composition' => $row['composition'],
            //     'fabric' => $row['fabric'],
            //     'care_instructions' => $row['care_instructions'],
            //     'fit' => $row['fit'],
            //     'style' => $row['style'],
            //     'season' => $row['season'],
            // ]);
        }

    }

    public function headingRow(): int
    {
        return 0;
    }

    public function onError(Throwable $error)
    {
        return $error->getMessage();
    }

    /**
     * Batch size for inserting records
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 1000; // Insert 1000 records at a time
    }

    /**
     * Chunk size for reading the
     * file
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Read 1000 rows at a time
    }
}
