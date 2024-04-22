<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ValidWorkersExport;

class ProcessExcelFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:process {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process an Excel file and store data accordingly';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = $this->argument('file');

        $rows = Excel::toCollection([], $file)->first();

        $validWorkers = [];
        $nonExistentWorkers = [];

        foreach ($rows as $row) {
            $number = $row[24];
            if (is_numeric($number)) {
                $validWorkers[] = $row;
            } else {
                $nonExistentWorkers[] = $row;
            }
        }






        try {

            $validWorkersExport = new ValidWorkersExport($validWorkers);
            $excelFilePath = 'C:\\Users\\dell.DESKTOP-TDK0A9A\\Downloads\\Updated_workers_data_2024.xls'; // File path

            // Save the generated Excel file
            Excel::store($validWorkersExport, $excelFilePath);

            error_log('Valid workers Excel file stored successfully.');
        } catch (\Exception $e) {
            Log::error('Error storing valid workers Excel file: ' . $e->getMessage());
            error_log('Error storing valid workers Excel file: ' . $e->getMessage());
        }



        try {
            // Save to non-existent workers Excel file
            Excel::store(collect($nonExistentWorkers), 'non_existent_workers2.xlsx', 'public');
        } catch (\Exception $e) {
            Log::error('Error storing non-existent workers Excel file: ' . $e->getMessage());
        }

        $this->info('Excel file processed successfully.');
    }
}
