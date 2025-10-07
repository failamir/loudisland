<?php
// build_new_no_hp_to_blast.php
// Generate peserta_id,nama,no_hp into routes/new_no_hp_to_blast.normalized.csv
// - peserta_id: unique random 6-digit
// - nama: from routes/Daftar Peserta Pelatihan.csv (second column), aligned by line number
// - no_hp: from routes/new_no_hp_to_blast.normalized.csv (already normalized), aligned by line number

$baseDir = __DIR__;
$normalizedPath = $baseDir . '/new_no_hp_to_blast.normalized.csv';
$daftarPath     = $baseDir . '/Daftar Peserta Pelatihan.csv';

if (!file_exists($normalizedPath)) {
    fwrite(STDERR, "File not found: $normalizedPath\n");
    exit(1);
}
if (!file_exists($daftarPath)) {
    fwrite(STDERR, "File not found: $daftarPath\n");
    exit(1);
}

// Read normalized numbers (no_hp), ignore empty lines
$noHp = [];
$lines = file($normalizedPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $noHp[] = $line;
}

// Read daftar names (second CSV column), aligned by row index
$names = [];
$fh = fopen($daftarPath, 'r');
if ($fh === false) {
    fwrite(STDERR, "Unable to open: $daftarPath\n");
    exit(1);
}

// Use fgetcsv with default delimiter ',' and enclosure '"'
while (($row = fgetcsv($fh)) !== false) {
    // Expect at least 2 columns: [index, nama, ...]
    if (count($row) < 2) {
        $names[] = '';
        continue;
    }
    $names[] = trim($row[1]);
}
fclose($fh);

// Build output rows aligned by index
$count = min(count($noHp), count($names));
if ($count === 0) {
    fwrite(STDERR, "No rows to process. Check input files.\n");
    exit(1);
}

// Generate unique random 6-digit IDs
$ids = [];
$used = [];
for ($i = 0; $i < $count; $i++) {
    do {
        $id = random_int(100000, 999999);
    } while (isset($used[$id]));
    $used[$id] = true;
    $ids[] = (string)$id;
}

// Prepare CSV lines with header
$out = [];
$out[] = 'peserta_id,nama,no_hp';
for ($i = 0; $i < $count; $i++) {
    $nama = $names[$i] ?? '';
    $hp   = $noHp[$i] ?? '';

    // CSV safe: wrap nama if contains comma or quote
    $namaCsv = $nama;
    if (strpos($namaCsv, '"') !== false) {
        $namaCsv = str_replace('"', '""', $namaCsv);
    }
    if (strpos($namaCsv, ',') !== false || strpos($namaCsv, '"') !== false) {
        $namaCsv = '"' . $namaCsv . '"';
    }

    $out[] = $ids[$i] . ',' . $namaCsv . ',' . $hp;
}

// Backup original normalized file
$backupPath = $normalizedPath . '.bak';
@copy($normalizedPath, $backupPath);

// Overwrite normalized file with new 3-column CSV
$ok = file_put_contents($normalizedPath, implode("\n", $out) . "\n");
if ($ok === false) {
    fwrite(STDERR, "Failed to write output file: $normalizedPath\n");
    exit(1);
}

// Build $dataPeserta array and export to PHP file for easy include
$dataPeserta = [];
for ($i = 0; $i < $count; $i++) {
    $dataPeserta[] = [
        'peserta_id' => $ids[$i],
        'nama' => $names[$i] ?? '',
        'no_hp' => $noHp[$i] ?? '',
    ];
}

$phpPath = $baseDir . '/data_peserta.php';
$phpOut = "<?php\nreturn ";
$phpOut .= var_export($dataPeserta, true);
$phpOut .= ";\n";
$ok2 = file_put_contents($phpPath, $phpOut);
if ($ok2 === false) {
    fwrite(STDERR, "Failed to write PHP array file: $phpPath\n");
    exit(1);
}

echo "Wrote $count rows to $normalizedPath (backup: $backupPath) and exported array to $phpPath\n";
