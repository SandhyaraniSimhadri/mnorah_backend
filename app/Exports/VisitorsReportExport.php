<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class VisitorsReportExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($data)
    {
        $this->rows = $data;
    }

    public function collection()
    {
        $formattedData = [];
        $i = 0;

        foreach ($this->rows as $row) {
                    $i=$i+1;
                 
                    if($row['hear_about']!="Other"){
                        $hear_about_church = $row['hear_about'];
                    }else{
                        $hear_about_church = $row['hear_about_other'];
                    }
                    $formattedData[] = [
                        'S.No' => $i, 
                        'Visitor Id' => $row['id'],
                        'Church Id' => $row['church_id'],
                        'Church Name' => $row['church_name'],
                        'Visitor Name' => $row['first_name'].' '.$row['last_name'],
                        'Child 1 Name' => $row['child1_name'],
                        'Child 2 Name' => $row['child2_name'],
                        'Child 3 Name' => $row['child3_name'],
                        'Child 4 Name' => $row['child4_name'],
                        'Phone Number' => $row['phone_number'],
                        'Email' => $row['email'],
                        'City' => $row['city'],
                        'Connection card' =>$row['connection'],
                        'Visit Date' => $row['visit_date'],
                        'experience' => $row['experience'],
                        'How did you hear about us ?' => $hear_about_church,
                        'Involvement and InterestWhat did you enjoy most about your visit?' => $row['about_visit'], 
                        'Suggestions for Improvement' => $row['suggestions'],
                        'Prayer Requests' => $row['prayer_request'],
                        'Additional Comments' => $row['comments'],
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No', 
            'Visitor Id',
            'Church Id',
            'Church Name',
            'Visitor Name',
            'Child 1 Name',
            'Child 2 Name',
            'Child 3 Name',
            'Child 4 Name',
            'Phone Number',
            'Email',
            'City',
            'Connection card',
            'Visit Date',
            'experience',
            'How did you hear about us ?',
            'Involvement and InterestWhat did you enjoy most about your visit?', 
            'Suggestions for Improvement',
            'Prayer Requests' ,
            'Additional Comments'
        ];
    }
}
