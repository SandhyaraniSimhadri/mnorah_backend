<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // return $row;
            // Process each row as needed
            // You can perform database inserts or any other logic here
            return [
                'church_id' => $row[0],
                'full_name' =>$row[1],
                'gender' => $row[2],
                'dob' => $row[3],
                'phone_number' =>$row[4],
                'email'=>$row[5],
                'city' =>$row[6],
                'state' =>$row[7],
                'membership_status' => $row[8],
                'hear_about' => $row[9],
                'involvement' => $row[10],
                'comments' => $row[11],
            ];

           
        }
    }
}

