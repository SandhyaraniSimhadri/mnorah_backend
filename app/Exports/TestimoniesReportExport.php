<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class TestimoniesReportExport implements FromCollection, WithHeadings
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
                        'Testimony Id' => $row['id'],
                        'Church Id' => $row['church_id'],
                        'Church Name' => $row['church_name'],
                        'Title' => $row['title'],
                        'Testimony' => $row['testimony']
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Testimony Id',
            'Church Id',
            'Church Name',
            'Title',
            'Testimony',
        ];
    }
}
