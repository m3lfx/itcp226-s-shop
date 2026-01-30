<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Item;

class ItemStockImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $item = Item::create([
                'description' => $row["product_name"],
                'cost_price' => $row["cost_price"],
                'sell_price' => $row["sell_price"],
                'img_path' => $row["image"],

            ]);
        }
    }
}
