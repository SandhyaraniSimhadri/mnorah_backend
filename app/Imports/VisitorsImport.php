<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VisitorsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // return $row;
            // Process each row as needed
            // You can perform database inserts or any other logic here
            return [
                'church_id' => $row[0],
                'first_name' =>$row[1],
                'last_name' => $row[2],
                'spouse_name' => $row[3],
                'child1_name' => $row[4],
                'child2_name' => $row[5],
                'child3_name' => $row[6],
                'child4_name' => $row[7],
                'email'=>$row[8],
                'phone_number' =>$row[9],
                'address' => $row[10],
                'city' =>$row[11],
                'hear_about' => $row[12],
                'hear_about_other' => $row[13],
                'visit_date' => $row[14],
                'experience' => $row[15],
                'about_visit' => $row[16],
                'suggestions' => $row[17],
                'prayer_request' => $row[18],
                'comments' => $row[19],
                'connection' => $row[20],
            ];

           
        }
    }
}

