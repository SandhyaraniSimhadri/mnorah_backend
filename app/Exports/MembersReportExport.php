<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class MembersReportExport implements FromCollection, WithHeadings
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
                    if($row['membership_status']!="Other"){
                        $Membership_status = $row['membership_status'];
                    }else{
                        $Membership_status = $row['membership_status_other'];
                    }
                    if($row['hear_about_church']!="Other"){
                        $hear_about_church = $row['hear_about_church'];
                    }else{
                        $hear_about_church = $row['hear_about_church_other'];
                    }
                    $formattedData[] = [
                        'S.No' => $i, 
                        'Member Id' => $row['id'],
                        'Church Name' => $row['church_name'],
                        'Member Name' => $row['user_name'],
                        'Gender' => $row['gender'],
                        'Date of Birth' => $row['dob'],
                        'Phone Number' => $row['mobile_no'],
                        'Email' => $row['email'],
                        'City' => $row['location'],
                        'State' => $row['state'],
                        'Membership Status' =>$Membership_status,
                        'How did you hear about church ?' => $hear_about_church,
                        'Involvement and Interest' => $row['invovlement_interest'], 
                        'Additional Comments or Questions' => $row['comments'],
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Member Id',
            'Church Name',
            'Member Name',
            'Gender',
            'Date of Birth',
            'Phone Number',
            'Email',
            'City',
            'State',
            'Membership Status',
            'How did you hear about church ?',
            'Involvement and Interest',
            'Additional Comments or Questions',
        ];
    }
}
