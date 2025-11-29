const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');

const samplesDir = path.join(__dirname, 'public/samples');
const dataLengkapPath = path.join(samplesDir, 'data_lengkap.xlsx');

// 1. Load Reference Data
console.log(`Loading reference data from ${dataLengkapPath}...`);
const workbook = XLSX.readFile(dataLengkapPath);
const sheet = workbook.Sheets[workbook.SheetNames[0]];
const refData = XLSX.utils.sheet_to_json(sheet, { raw: false });

const existingNIPs = new Set();
const existingNames = new Set();

refData.forEach(row => {
    // Normalize keys
    // Headers: 'Nama', 'NIK/NIP', 'Email', 'No. HP', 'Asal Kota/Kab/Prov', 'Size'
    const name = row['Nama'] || row['NAMA'];
    const nip = row['NIK/NIP'] || row['NIP'] || row['NIK'];

    if (name) existingNames.add(String(name).trim().toLowerCase());
    if (nip) existingNIPs.add(String(nip).replace(/[^0-9]/g, '')); // Keep only digits for NIP
});

console.log(`Loaded ${existingNames.size} names and ${existingNIPs.size} NIPs from reference.`);

// 2. Scan CSVs
const files = fs.readdirSync(samplesDir).filter(f => f.endsWith('.csv'));
const missingRecords = [];

files.forEach(file => {
    const filePath = path.join(samplesDir, file);
    // console.log(`Processing ${file}...`);

    try {
        const wb = XLSX.readFile(filePath);
        const sh = wb.Sheets[wb.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sh, { raw: false });

        rows.forEach((row, index) => {
            // Find keys dynamically
            let name = '';
            let nip = '';

            Object.keys(row).forEach(key => {
                const k = key.toLowerCase();
                if (k.includes('nama') || k.includes('name')) name = row[key];
                if (k.includes('nip') || k.includes('nik')) nip = row[key];
            });

            const normName = String(name).trim().toLowerCase();
            const normNIP = String(nip).replace(/[^0-9]/g, '');

            let found = false;
            if (normNIP && existingNIPs.has(normNIP)) found = true;
            else if (normName && existingNames.has(normName)) found = true;

            if (!found && (name || nip)) {
                missingRecords.push({
                    source: file,
                    row: index + 2, // 1-based, +1 for header
                    name: name,
                    nip: nip
                });
            }
        });
    } catch (e) {
        console.error(`Error processing ${file}:`, e.message);
    }
});

// 3. Report
console.log(`\nFound ${missingRecords.length} missing records.`);
if (missingRecords.length > 0) {
    console.log('Missing Records:');
    missingRecords.forEach(r => {
        console.log(`[${r.source}] Row ${r.row}: ${r.name} (NIP: ${r.nip})`);
    });

    // Also save to a file for easier reading if too many
    const outPath = path.join(__dirname, 'missing_data_report.json');
    fs.writeFileSync(outPath, JSON.stringify(missingRecords, null, 2));

    // Save as CSV
    const csvHeader = 'Source,Row,Name,NIP\n';
    const csvContent = missingRecords.map(r => `"${r.source}",${r.row},"${r.name}","${r.nip}"`).join('\n');
    const csvPath = path.join(__dirname, 'missing_data_report.csv');
    fs.writeFileSync(csvPath, csvHeader + csvContent);

    console.log(`\nFull report saved to ${outPath} and ${csvPath}`);
}
