<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FailedRowsExportAttac implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {

        $this->rows = array_map(function ($row) {

            $data = $row['Data'];

            if (isset($row['Errors'])) {
                $data['Errors'] = implode(', ', $row['Errors']);
            }

            return $data;
        }, $rows);
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {

        return array_keys($this->rows[0]);
    }
}
