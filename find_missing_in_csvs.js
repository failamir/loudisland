const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');

const samplesDir = path.join(__dirname, 'public/samples');
const dataLengkapPath = path.join(samplesDir, 'data_lengkap.xlsx');

// Helper to normalize strings
const normalize = (str) => String(str || '').trim().toLowerCase();
const normalizeNIP = (str) => String(str || '').replace(/[^0-9]/g, '');

// 1. Load All CSV Data first (The "Universe" of CSV records)
console.log(`Loading data from all CSVs in ${samplesDir}...`);
const files = fs.readdirSync(samplesDir).filter(f => f.endsWith('.csv'));

const csvNIPs = new Set();
const csvNames = new Set();

files.forEach(file => {
    const filePath = path.join(samplesDir, file);
    try {
        const wb = XLSX.readFile(filePath);
        const sh = wb.Sheets[wb.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sh, { raw: false });

        rows.forEach(row => {
            let name = '';
            let nip = '';

            Object.keys(row).forEach(key => {
                const k = key.toLowerCase();
                if (k.includes('nama') || k.includes('name')) name = row[key];
                if (k.includes('nip') || k.includes('nik')) nip = row[key];
            });

            if (nip) csvNIPs.add(normalizeNIP(nip));
            if (name) csvNames.add(normalize(name));
        });
    } catch (e) {
        console.error(`Error processing ${file}:`, e.message);
    }
});

console.log(`Loaded ${csvNames.size} unique names and ${csvNIPs.size} unique NIPs from CSVs.`);

// 2. Load Reference Data (data_lengkap.xlsx) and check against CSV data
console.log(`Loading reference data from ${dataLengkapPath}...`);
const workbook = XLSX.readFile(dataLengkapPath);
const sheet = workbook.Sheets[workbook.SheetNames[0]];
const refData = XLSX.utils.sheet_to_json(sheet, { raw: false });

const missingInCSVs = [];

refData.forEach((row, index) => {
    // Headers: 'Nama', 'NIK/NIP', 'Email', 'No. HP', 'Asal Kota/Kab/Prov', 'Size'
    const name = row['Nama'] || row['NAMA'];
    const nip = row['NIK/NIP'] || row['NIP'] || row['NIK'];
    const email = row['Email'] || row['EMAIL'] || '';
    const phone = row['No. HP'] || row['NO HP'] || '';
    const shirtSize = row['Size'] || row['SIZE'] || row['UKURAN BAJU'] || '';

    const normName = normalize(name);
    const normNIP = normalizeNIP(nip);

    let found = false;

    // Check by NIP first (more reliable)
    if (normNIP && csvNIPs.has(normNIP)) {
        found = true;
    }
    // If no NIP match, check by Name
    else if (normName && csvNames.has(normName)) {
        found = true;
    }

    if (!found) {
        missingInCSVs.push({
            row: index + 2, // 1-based, +1 for header
            name: name,
            nip: nip,
            email: email,
            phone: phone,
            shirt_size: shirtSize
        });
    }
});

// 3. Report
console.log(`\nFound ${missingInCSVs.length} records in data_lengkap.xlsx that are NOT in any CSV.`);

if (missingInCSVs.length > 0) {
    // Save to JSON
    const outPath = path.join(__dirname, 'missing_in_csvs_report.json');
    fs.writeFileSync(outPath, JSON.stringify(missingInCSVs, null, 2));

    // Save to CSV
    const csvHeader = 'Row,Name,NIP,Email,Phone,Shirt Size\n';
    const csvContent = missingInCSVs.map(r => `${r.row},"${r.name}","${r.nip}","${r.email}","${r.phone}","${r.shirt_size}"`).join('\n');
    const csvPath = path.join(__dirname, 'missing_in_csvs_report.csv');
    fs.writeFileSync(csvPath, csvHeader + csvContent);

    console.log(`Full report saved to ${outPath} and ${csvPath}`);

    // Preview first few
    console.log('\nPreview of missing records:');
    missingInCSVs.slice(0, 5).forEach(r => {
        console.log(`Row ${r.row}: ${r.name} (NIP: ${r.nip})`);
    });
}
