<?php

// app/Imports/VisitorsImport.php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EventImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            return [
                'church_id' => $row[0],
                'event_name' =>$row[1],
                'event_type' => $row[2],
                'event_date' => $row[3],
                'event_time' =>$row[4],
                'venue' => $row[5],
                'speakers' => $row[6],
                'contact_person' => $row[7],
                'frequency' =>$row[8],
                'event_description' => $row[9],
                'agenda' => $row[10],
                'reg_info' => $row[11],
                'dress_code' => $row[12],
                'special_req' => $row[13],
                'additional_info' => $row[14],               
            ];
        }
    }
}

