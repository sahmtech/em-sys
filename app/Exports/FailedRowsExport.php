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
            error_log(json_encode($row['Data']));
            $data = $row['Data'];

            foreach ($data as $key => $value) {
                if ($key == "allowance_data") {
                    unset($data[$key]);
                    foreach ($value as $nestedKey => $nestedValue) {
                        $tempArr = json_decode($nestedValue, true);
                        foreach ($tempArr as $tmpKey => $tmpValue) {
                            $data[$nestedKey . '_' . $tmpKey] = $tmpValue;
                        }
                    }
                } else if ($key == "bank_details") {
                    unset($data[$key]);
                    $tempArr = json_decode($value, true);
                    foreach ($tempArr as $tmpKey => $tmpValue) {
                        $data[$tmpKey] = $tmpValue;
                    }
                }
                // if (is_array($value)) {

                //     unset($data[$key]);

                //     foreach ($value as $nestedKey => $nestedValue) {
                //         $data[$nestedKey] = $nestedValue;
                //     }
                // } else if (is_string($value) && $this->isJson($value)) {

                //     $data[$key] = json_decode($value, true);
                // }
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
