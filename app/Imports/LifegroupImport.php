<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LifegroupImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // return $row;
            // Process each row as needed
            // You can perform database inserts or any other logic here
            return [
                'church_id' => $row[0],
                'country' =>$row[1],
                'city' => $row[2],
                'area' => $row[3],
                'members' => $row[4],

            ];

           
        }
    }
}

