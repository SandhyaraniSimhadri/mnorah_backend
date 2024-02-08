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
                'leader' => $row[1],
                'country' =>$row[2],
                'city' => $row[3],
                'area' => $row[4],
                'members' => $row[5],

            ];

           
        }
    }
}

