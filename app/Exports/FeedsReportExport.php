<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class FeedsReportExport implements FromCollection, WithHeadings
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
                        'Feed Id' => $row['id'],
                        'Church Name' => $row['church_name'],
                        'Title' => $row['title'],
                        'Author' => $row['author'],
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
            'Feed Id',
            'Church Name',
            'Title',
            'Author',
            'Description',
        ];
    }
}
