<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ValidWorkersExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }
}
