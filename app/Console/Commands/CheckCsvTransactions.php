<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;

class CheckCsvTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:check-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check transaction status from a CSV file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $csvFile = public_path('samples/cek-data-testing.csv');

        if (!file_exists($csvFile)) {
            $this->error("File not found: $csvFile");
            return 1;
        }

        $this->info("Reading file: $csvFile");

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            $this->error("Could not open file: $csvFile");
            return 1;
        }

        $results = [];
        $rowCount = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $rowCount++;
            // Skip empty lines or lines with insufficient columns
            if (count($data) < 2) {
                continue;
            }

            $transactionId = trim($data[1]);

            // Skip header if it looks like a header (optional, but good practice if the file has one)
            // In this specific file, it seems to start with data immediately based on the view_file output, 
            // but let's assume if it's not numeric it might be a header or invalid.
            // However, the user said "kolom ke 2 id transaction nya".
            // Let's just process it.

            if (empty($transactionId)) {
                continue;
            }

            $name = isset($data[4]) ? trim($data[4]) : 'Unknown';

            $transaction = Transaksi::find($transactionId);

            $status = $transaction ? $transaction->status : 'Not Found';
            $amount = $transaction ? $transaction->amount : 'N/A';

            $results[] = [
                'csv_row' => $rowCount,
                'transaction_id' => $transactionId,
                'name' => $name,
                'status' => $status,
                'amount' => $amount,
                'original_line' => implode(';', $data) // Keep context if needed
            ];
        }

        fclose($handle);

        $this->info("Checked " . count($results) . " transactions.");

        // Generate Report
        $fileName = 'transaction_check_report_with_names_' . date('Y-m-d_H-i-s') . '.csv';
        $directory = public_path('reports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $filePath = $directory . '/' . $fileName;

        $fp = fopen($filePath, 'w');
        // Add BOM
        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, ['CSV Row', 'Transaction ID', 'Name', 'Status', 'Amount']);

        foreach ($results as $row) {
            fputcsv($fp, [
                $row['csv_row'],
                $row['transaction_id'],
                $row['name'],
                $row['status'],
                $row['amount']
            ]);
        }

        fclose($fp);

        $this->info("Report generated at: $filePath");

        // Also display summary of non-success transactions
        $nonSuccess = array_filter($results, function ($r) {
            return $r['status'] !== 'success';
        });

        if (count($nonSuccess) > 0) {
            $this->warn("Found " . count($nonSuccess) . " transactions that are NOT success:");
            $headers = ['CSV Row', 'Transaction ID', 'Name', 'Status'];
            $data = array_map(function ($r) {
                return [$r['csv_row'], $r['transaction_id'], $r['name'], $r['status']];
            }, $nonSuccess); // Show all

            $this->table($headers, $data);
        } else {
            $this->info("All checked transactions are successful!");
        }

        return 0;
    }
}
