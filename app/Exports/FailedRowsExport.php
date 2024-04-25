<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FailedRowsExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {

        $this->rows = array_map(function ($row) {

            $data = $row['Data'];

            foreach ($data as $key => $value) {
                if (is_array($value)) {

                    unset($data[$key]);

                    foreach ($value as $nestedKey => $nestedValue) {
                        $data[$nestedKey] = $nestedValue;
                    }
                } else if (is_string($value) && $this->isJson($value)) {

                    $data[$key] = json_decode($value, true);
                }
            }


            if (isset($row['Errors'])) {
                $res = '';
                foreach ($row['Errors'] as $error) {
                    $res .= $error . ', ';
                }

                $data['Errors'] = $res;
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

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
