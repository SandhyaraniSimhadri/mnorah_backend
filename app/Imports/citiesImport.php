<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class citiesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // return $row;
            // Process each row as needed
            // You can perform database inserts or any other logic here
            return [
                'id' => $row['0'],
                'name' => $row['1'],
                'state_id' => $row['2'],
                'state_code' => $row['3'],
                'state_name' => $row['4'],
                'country_id' => $row['5'],
            ];

           
        }
    }
}

