<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestimonyImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // return $row;
            // Process each row as needed
            // You can perform database inserts or any other logic here
            return [
                'church_id' => $row[0],
                'select_member' =>$row[1],
                'title' =>$row[2],
                'testimony' => $row[3]
            ];

           
        }
    }
}

