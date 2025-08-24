<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Wizard Pendaftaran - KORPRI Mandalika Run 2025</title>
    <style>
        .category-card:hover { border-color: #2767BB66; }
        .category-card.selected { border-color: #2767BB !important; box-shadow: 0 0 0 2px #2767BB1a; }
        .category-card .corner { display: none; width: 24px; height: 24px; border-width: 3px; border-color: #2767BB; }
        .category-card.selected .corner { display: block; }
        .step { display: none; }
        .step.active { display: block; }
        .disabled { opacity: .5; pointer-events: none; }
    </style>
</head>
<body class="min-h-screen w-screen overflow-x-hidden bg-gray-50">
<div class="-z-20 h-1/2 bg-white w-screen absolute">
    <img class="w-full h-full blur-lg" src="/assets/images/banner.png" alt="banner">
</div>
<div class="flex justify-center z-30 relative px-4">
    <main class="w-full max-w-3xl mt-6 md:mt-12 drop-shadow-lg bg-white relative rounded-md overflow-hidden">
        <form id="wizardForm" action="{{ route('bayar') }}" method="POST">
            @csrf
            <!-- hidden fields populated by step 1 -->
            <input type="hidden" name="kategori" id="kategori" value="">
            <input type="hidden" name="qty" id="qty" value="1">
            <input type="hidden" name="amount" id="amount" value="0">
            <input type="hidden" name="total_bayar" id="total_bayar" value="0">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row w-full bg-gradient-to-br from-orange-100 to-orange-50">
                <div class="w-full md:w-3/5 md:mr-4 md:border-r-2 border-black border-dashed">
                    <img class="w-full" src="/assets/images/banner.png" alt="banner">
                </div>
                <div class="w-full md:w-2/5 p-4 flex flex-col justify-between">
                    <div>
                        <div class="font-medium">
                            <h5>Sept</h5>
                            <h5>2-3</h5>
                        </div>
                        <div class="mt-6">
                            <h1 class="text-2xl font-medium">KORPRI MANDALIKA RUN 2025</h1>
                            <p>by KORPRI</p>
                        </div>
                    </div>
                    <div class="text-right text-xl font-medium">
                        <p id="headerPrice">Rp 0</p>
                    </div>
                </div>
            </div>

            <!-- STEPPER -->
            <div class="w-full border-x-2 border-b-2">
                <ol class="flex items-center justify-center gap-6 py-3 text-sm">
                    <li id="stepper-1" class="font-medium text-blue-700">1. Pilih Kategori</li>
                    <li id="stepper-2" class="text-gray-500">2. Data Diri</li>
                    <li id="stepper-3" class="text-gray-500">3. Review & Bayar</li>
                </ol>
            </div>

            <!-- CONTENT -->
            <div class="w-full border-x-2 border-b-2 p-4">
                <!-- Step 1: Category selection -->
                <section class="step active" id="step-1">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="category-card flex-1 border-2 border-transparent rounded-lg p-4 cursor-pointer relative" data-cat="ASN" data-price="200000">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h1 class="text-xl">Kategori ASN<br><span class="text-base">Rp 200.000</span></h1>
                                    <p class="text-sm text-gray-600">Tiket untuk peserta kategori ASN.</p>
                                </div>
                                <div class="text-sm text-gray-600">Pilih</div>
                            </div>
                            <span class="corner tl absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-transparent rounded-tl"></span>
                            <span class="corner tr absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-transparent rounded-tr"></span>
                            <span class="corner bl absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-transparent rounded-bl"></span>
                            <span class="corner br absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-transparent rounded-br"></span>
                        </div>
                        <div class="category-card flex-1 border-2 border-transparent rounded-lg p-4 cursor-pointer relative" data-cat="UMUM" data-price="200000">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h1 class="text-xl">Kategori UMUM<br><span class="text-base">Rp 200.000</span></h1>
                                    <p class="text-sm text-gray-600">Tiket untuk peserta kategori UMUM.</p>
                                </div>
                                <div class="text-sm text-gray-600">Pilih</div>
                            </div>
                            <span class="corner tl absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-transparent rounded-tl"></span>
                            <span class="corner tr absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-transparent rounded-tr"></span>
                            <span class="corner bl absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-transparent rounded-bl"></span>
                            <span class="corner br absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-transparent rounded-br"></span>
                        </div>
                    </div>
                </section>

                <!-- Step 2: Personal data -->
                <section class="step" id="step-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium mb-1">Nama Lengkap</label>
                            <input id="nama" name="nama" type="text" required class="w-full border rounded px-3 py-2" placeholder="Nama Anda">
                        </div>
                        <div>
                            <label for="nik" class="block text-sm font-medium mb-1">NIK</label>
                            <input id="nik" name="nik" type="text" required class="w-full border rounded px-3 py-2" placeholder="NIK">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">Email</label>
                            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-2" placeholder="you@email.com">
                        </div>
                        <div>
                            <label for="no_hp" class="block text-sm font-medium mb-1">No HP</label>
                            <input id="no_hp" name="no_hp" type="text" required class="w-full border rounded px-3 py-2" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </section>

                <!-- Step 3: Review -->
                <section class="step" id="step-3">
                    <div class="space-y-4">
                        <div class="border rounded p-4">
                            <h2 class="font-medium mb-2">Ringkasan Pesanan</h2>
                            <div class="flex justify-between">
                                <span id="summaryName">Pilih kategori</span>
                                <span id="summaryPrice">Rp 0</span>
                            </div>
                            <div class="flex justify-between mt-4 border-t pt-3">
                                <span class="font-medium">Total</span>
                                <span id="totalPrice" class="font-medium">Rp 0</span>
                            </div>
                        </div>
                        <div class="border rounded p-4">
                            <h2 class="font-medium mb-2">Data Diri</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div><span class="text-gray-500">Nama:</span> <span id="rev_nama">-</span></div>
                                <div><span class="text-gray-500">NIK:</span> <span id="rev_nik">-</span></div>
                                <div><span class="text-gray-500">Email:</span> <span id="rev_email">-</span></div>
                                <div><span class="text-gray-500">No HP:</span> <span id="rev_no_hp">-</span></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-6">
                    <button type="button" id="prevBtn" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Kembali</button>
                    <div class="flex gap-3">
                        <button type="button" id="nextBtn" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Lanjut</button>
                        <button type="submit" id="submitBtn" class="px-4 py-2 rounded bg-orange-500 text-white hover:bg-orange-600 hidden">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
(function() {
    const PRICE_DEFAULT = 200000;
    const steps = Array.from(document.querySelectorAll('.step'));
    const stepperEls = [document.getElementById('stepper-1'), document.getElementById('stepper-2'), document.getElementById('stepper-3')];
    let current = 0;

    const kategori = document.getElementById('kategori');
    const amount = document.getElementById('amount');
    const total_bayar = document.getElementById('total_bayar');

    const cards = document.querySelectorAll('.category-card');
    const headerPrice = document.getElementById('headerPrice');
    const summaryName = document.getElementById('summaryName');
    const summaryPrice = document.getElementById('summaryPrice');
    const totalPrice = document.getElementById('totalPrice');

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    const elNama = document.getElementById('nama');
    const elNik = document.getElementById('nik');
    const elEmail = document.getElementById('email');
    const elNoHp = document.getElementById('no_hp');

    const revNama = document.getElementById('rev_nama');
    const revNik = document.getElementById('rev_nik');
    const revEmail = document.getElementById('rev_email');
    const revNoHp = document.getElementById('rev_no_hp');

    function formatRupiah(num) { return 'Rp ' + (num||0).toLocaleString('id-ID'); }

    function goto(idx) {
        steps.forEach((s, i) => s.classList.toggle('active', i === idx));
        stepperEls.forEach((el,i)=>{
            el.classList.toggle('text-blue-700', i===idx);
            el.classList.toggle('text-gray-500', i!==idx);
            el.classList.toggle('font-medium', i===idx);
        });
        prevBtn.classList.toggle('disabled', idx===0);
        nextBtn.classList.toggle('hidden', idx===steps.length-1);
        submitBtn.classList.toggle('hidden', idx!==steps.length-1);
        current = idx;
    }

    function selectCard(card){
        cards.forEach(c=>{ c.classList.remove('selected'); c.querySelectorAll('.corner').forEach(el=>el.classList.add('hidden')); });
        card.classList.add('selected');
        card.querySelectorAll('.corner').forEach(el=>el.classList.remove('hidden'));
        const cat = card.dataset.cat; const price = parseInt(card.dataset.price,10) || PRICE_DEFAULT;
        kategori.value = cat; amount.value = price; total_bayar.value = price;
        headerPrice.textContent = formatRupiah(price);
        summaryName.textContent = `1 x Kategori ${cat}`;
        summaryPrice.textContent = formatRupiah(price);
        totalPrice.textContent = formatRupiah(price);
    }

    cards.forEach(card=> card.addEventListener('click', ()=> selectCard(card)));

    prevBtn.addEventListener('click', ()=> goto(Math.max(0, current-1)));
    nextBtn.addEventListener('click', ()=>{
        if(current===0){
            if(!kategori.value){ alert('Silakan pilih 1 kategori tiket.'); return; }
            goto(1);
        } else if(current===1){
            if(!elNama.value || !elNik.value || !elEmail.value || !elNoHp.value){ alert('Lengkapi data diri terlebih dahulu.'); return; }
            revNama.textContent = elNama.value; revNik.textContent = elNik.value; revEmail.textContent = elEmail.value; revNoHp.textContent = elNoHp.value;
            goto(2);
        }
    });

    // init
    headerPrice.textContent = formatRupiah(0);
    goto(0);
})();
</script>
</body>
</html>
