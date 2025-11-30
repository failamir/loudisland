<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Participant;

class CheckParticipantTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'participants:check-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if participants have successful transactions and export failed ones to CSV';

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
        $this->info('Checking participants...');

        // Eager load transaction to avoid N+1 problem
        $participants = Participant::with('transaction')->get();
        $failedParticipants = [];

        $bar = $this->output->createProgressBar(count($participants));
        $bar->start();

        foreach ($participants as $participant) {
            $transaction = $participant->transaction;

            // Check if transaction exists and status is 'success'
            // Note: Transaksi model defines STATUS_SELECT with 'success' => 'success'
            if (!$transaction || $transaction->status !== 'success') {
                $failedParticipants[] = [
                    'participant_id' => $participant->id,
                    'name' => $participant->name,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'transaction_id' => $transaction ? $transaction->id : 'N/A',
                    'transaction_status' => $transaction ? $transaction->status : 'Missing',
                    'amount' => $participant->amount,
                    'created_at' => $participant->getAttributes()['created_at'] ?? 'N/A',
                ];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (empty($failedParticipants)) {
            $this->info('All participants have successful transactions.');
            return 0;
        }

        $this->warn('Found ' . count($failedParticipants) . ' participants without successful transactions.');

        $fileName = 'failed_participants_' . date('Y-m-d_H-i-s') . '.csv';
        // Ensure reports directory exists
        $directory = public_path('reports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $fileName;

        $fp = fopen($filePath, 'w');
        // Add BOM for Excel compatibility
        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, ['Participant ID', 'Name', 'Email', 'Phone', 'Transaction ID', 'Transaction Status', 'Amount', 'Created At']);

        foreach ($failedParticipants as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        $this->info('Report generated successfully at:');
        $this->line($filePath);

        return 0;
    }
}
