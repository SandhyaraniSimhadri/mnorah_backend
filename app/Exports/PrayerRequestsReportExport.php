<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class PrayerRequestsReportExport implements FromCollection, WithHeadings
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
                 
                    $formattedData[] = [
                        'S.No' => $i, 
                        'Prayer Request Id' => $row['id'],
                        'Church Id' => $row['church_id'],
                        'Church Name' => $row['church_name'],
                        'Name' => $row['user_name'],
                        'Prayer Request' => $row['prayer_request'],
                        'Description' => $row['description']
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Prayer Request Id',
            'Church Id',
            'Church Name',
            'Member Name',
            'Prayer Request',
            'Description'
        ];
    }
}
