<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class EventsReportExport implements FromCollection, WithHeadings
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
                        'Event Id' =>$row['id'],
                        'Church Id' => $row['church_id'],
                        'Church Name' => $row['church_name'],
                        'Event Name' => $row['event_name'],
                        'Event Type' => $row['event_type'],
                        'Event Date' => $row['event_date'],
                        'Event Time' => $row['event_time'],
                        'Venue' => $row['venue'],
                        'Speakers' => $row['speakers'],
                        'Contact Person' => $row['contact_person'],
                        'Frequency' => $row['frequency'],
                        'Event Description' => $row['event_description'],
                        'Agenda' => $row['agenda'],
                        'Registration Information' =>$row['reg_info'],
                        'Dress Code' => $row['dress_code'],
                        'Special Requirements' => $row['special_req'],
                        'Additional Information' => $row['additional_info'],
                    ];
                  
                }

        // Return a Collection instance
        return new Collection($formattedData);
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Event Id',
            'Church Id',
            'Church Name',
            'Event Name',
            'Event Type',
            'Event Date',
            'Event Time',
            'Venue',
            'Speakers',
            'Contact Person',
            'Frequency',
            'Event Description',
            'Agenda',
            'Registration Information',
            'Dress Code' ,
            'Special Requirements',
            'Additional Information'
        ];
    }
}
