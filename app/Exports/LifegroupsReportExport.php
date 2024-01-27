<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LifegroupsReportExport implements FromCollection, WithHeadings
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
                    $userIds = explode(',', $row['members']);
                    $userNames = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->pluck('user_name');
            
                    $userNames_details =$userNames->implode(','); 

                    $formattedData[] = [
                        'S.No' => $i, 
                        'Lifegroup Id' => $row['id'],
                        'Church Name' => $row['church_name'],
                        'Country' => $row['country'],
                        'City' => $row['city'],
                        'Area' => $row['area'],
                        'Members' => $userNames_details
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Lifegroup Id',
            'Church Name',
            'Country',
            'City',
            'Area',
            'Members'
        ];
    }
}
