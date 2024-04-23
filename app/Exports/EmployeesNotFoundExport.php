<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesNotFoundExport implements FromCollection, WithHeadings
{
    protected $employeesNotFound;



    public function __construct($employeesNotFound)
    {
        $this->employeesNotFound = $employeesNotFound;
    }

    public function collection()
    {
        return collect($this->employeesNotFound);
    }

    public function headings(): array
    {
        return [
            'emp_number',
            'ID Proof Number',
        ];
    }
}
