<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Participant;

$participants = Participant::where('status', '1')->take(5)->get();
echo "Checking first 5 participants:\n";
foreach ($participants as $p) {
    echo "ID: {$p->participant_id} | Name: {$p->name} | Blast: {$p->blast} | Invite: {$p->invite}\n";
}
