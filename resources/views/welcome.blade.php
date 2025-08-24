<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/output.css" rel="stylesheet">
    <title>Mandalika KORPRI Fun Night Run 2025</title>
    <style>
        /* .button {
  position: relative;
  background-color: #04AA6D;
  border: none;
  font-size: 28px;
  color: #FFFFFF;
  padding: 20px;
  width: 200px;
  text-align: center;
  -webkit-transition-duration: 0.4s;
  transition-duration: 0.4s;
  text-decoration: none;
  overflow: hidden;
  cursor: pointer;
}

.button:after {
  content: "";
  background: #90EE90;
  display: block;
  position: absolute;
  padding-top: 300%;
  padding-left: 350%;
  margin-left: -20px!important;
  margin-top: -120%;
  opacity: 0;
  transition: all 0.8s
}

.button:active:after {
  padding: 0;
  margin: 0;
  opacity: 1;
  transition: 0s
} */

        /* Category selection styles */
        .category-card:hover {
            border-color: #2767BB66;
        }

        .category-card.selected {
            border-color: #2767BB !important;
            box-shadow: 0 0 0 2px #2767BB1a;
        }

        .category-card.selected .corner {
            border-color: #2767BB !important;
        }

        /* Ensure corners only show when selected and are more visible */
        .category-card .corner {
            display: none;
            width: 24px;
            /* bigger corners */
            height: 24px;
            border-width: 3px;
            /* thicker lines */
            border-color: #2767BB;
            /* default color for safety */
        }

        .category-card.selected .corner {
            display: block;
        }
    </style>
</head>

<body class="min-h-screen w-screen overflow-x-hidden">
    <div class="-z-20 h-1/2 bg-white w-screen absolute">
        <img class="w-full h-full blur-lg" src="./assets/images/banner.png">
    </div>
    <div class="flex justify-center z-30 relative px-4">
        <main class="w-full max-w-md md:w-1/2 mt-6 md:mt-12 drop-shadow-lg bg-white relative">
            <form action="{{ route('beli') }}" method="post">
                @csrf
                @if (session('error'))
                    <div class="bg-red-100 text-red-800 border border-red-300 rounded p-3 mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                <input type="hidden" name="asn" id="asn" value="0">
                <input type="hidden" name="umum" id="umum" value="0">
                <!-- HEADER: START -->
                <div class="flex flex-col md:flex-row w-full bg-gradient-to-br from-orange-100 to-orange-50">
                    <div class="w-full md:w-3/5 md:mr-4 md:border-r-2 border-black border-dashed">
                        <img class="w-full" src="./assets/images/banner.png">
                    </div>
                    <div class="w-full md:w-2/5 p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium">
                                <h5>Sept</h5>
                                <h5>2-3</h5>
                            </div>
                            <div class="mt-6">
                                <h1 class="text-2xl font-medium">KORPRI MANDALIKA RUN 2025</h1>
                                <p>by KORPRI </p>
                            </div>
                        </div>
                        <div class="text-right text-xl font-medium">
                            <p>Rp 200K</p>
                        </div>
                    </div>
                </div>
                <!-- HEADER: END -->

                <!-- TITLE: START -->
                <div class="py-6 flex items-center flex-col border-x-2 border-b-2">
                    <h1 class="text-2xl font-medium mb-2">
                        KORPRI MANDALIKA RUN 2025
                    </h1>
                    <p>
                        Fri, Sept 2, 2022 3:00 PM - Sat, Sept 3, 2022 6:00 PM WITA
                    </p>
                </div>
                <!-- TITLE: END -->

                <!-- CONTENT: START -->
                <div class="flex flex-col md:flex-row justify-between w-full border-x-2 border-b-2 px-4">
                    <!-- FORM TICKET: START -->
                    <div class="w-full md:w-3/5">
                        <div class="category-card flex items-start justify-between w-full mt-6 gap-2 border-2 border-transparent rounded-lg p-4 cursor-pointer relative"
                            data-cat="asn">
                            <div>
                                <h1 class="text-xl">Kategori ASN<br><span class="text-base">Rp 200.000</span></h1>
                                <p>Tiket untuk peserta kategori ASN.</p>
                            </div>
                            <div class="text-sm text-gray-600">Pilih</div>
                            <span
                                class="corner tl hidden absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-transparent rounded-tl"></span>
                            <span
                                class="corner tr hidden absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-transparent rounded-tr"></span>
                            <span
                                class="corner bl hidden absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-transparent rounded-bl"></span>
                            <span
                                class="corner br hidden absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-transparent rounded-br"></span>
                        </div>

                        <div class="category-card flex items-start justify-between w-full mt-6 gap-2 border-2 border-transparent rounded-lg p-4 cursor-pointer relative"
                            data-cat="umum">
                            <div>
                                <h1 class="text-xl">Kategori UMUM<br><span class="text-base">Rp 200.000</span></h1>
                                <p>Tiket untuk peserta kategori UMUM.</p>
                            </div>
                            <div class="text-sm text-gray-600">Pilih</div>
                            <span
                                class="corner tl hidden absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-transparent rounded-tl"></span>
                            <span
                                class="corner tr hidden absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-transparent rounded-tr"></span>
                            <span
                                class="corner bl hidden absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-transparent rounded-bl"></span>
                            <span
                                class="corner br hidden absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-transparent rounded-br"></span>
                        </div>
                    </div>
                    <!-- FORM TICKET: END -->

                    <!-- ORDER SUMMARY: START -->
                    <div class="w-full md:w-2/5 p-4 md:ml-4 border-t-2 md:border-0 mt-6 md:mt-0">
                        <h1 class="text-xl font-medium">Order Summary</h1>
                        <div class="flex justify-between text-xl font-medium mt-4">
                            <p id="summaryName">Pilih kategori</p>
                            <p id="summaryPrice">Rp 0</p>
                        </div>
                        <div class="flex justify-between mt-8 border-t-2 pt-4">
                            <h1 class="text-xl font-medium">Total</h1>
                            <h1 class="text-xl font-medium" id="totalPrice">Rp 0</h1>
                        </div>
                        <div class="flex justify-between mt-8">
                            {{-- <h1 class="text-xl font-medium">Payment Method</h1>
                                  <select name="payment" id="payment">
                                    <option value="1">Bank Transfer</option>
                                    <option value="2">Credit Card</option>
                                    <option value="3">E-Wallet</option>
                                  </select> --}}
                            @guest
                                <a href="{{ route('register') }}" style="box-shadow: 20px 20px 50px grey;"
                                    class="btn btn-warning shadow bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center">
                                    Register to Buy
                                </a>
                            @else
                                <button type="submit" style="box-shadow: 20px 20px 50px grey;"
                                    class="btn btn-warning shadow bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">
                                    Buy Now
                                </button>
                            @endguest
                        </div>
                        <!-- ORDER SUMMARY: END -->
                    </div>
            </form>
            <!-- CONTENT: END -->
        </main>
    </div>
    <script>
        (function() {
            const hiddenAsn = document.getElementById('asn');
            const hiddenUmum = document.getElementById('umum');
            const form = document.querySelector('form[action="{{ route('beli') }}"]');
            const cards = document.querySelectorAll('.category-card');
            const summaryName = document.getElementById('summaryName');
            const summaryPrice = document.getElementById('summaryPrice');
            const totalPrice = document.getElementById('totalPrice');
            const PRICE = 200000;

            function formatRupiah(num) {
                return 'Rp ' + num.toLocaleString('id-ID');
            }

            function clearSelection() {
                hiddenAsn.value = '0';
                hiddenUmum.value = '0';
                cards.forEach(c => {
                    c.classList.remove('selected');
                    c.querySelectorAll('.corner').forEach(el => el.classList.add('hidden'));
                });
                summaryName.textContent = 'Pilih kategori';
                summaryPrice.textContent = formatRupiah(0);
                totalPrice.textContent = formatRupiah(0);
            }

            function selectCategory(cat) {
                clearSelection();
                const target = Array.from(cards).find(c => c.dataset.cat === cat);
                if (!target) return;
                target.classList.add('selected');
                target.querySelectorAll('.corner').forEach(el => el.classList.remove('hidden'));
                if (cat === 'asn') hiddenAsn.value = '1';
                if (cat === 'umum') hiddenUmum.value = '1';
                summaryName.textContent = `1 x Kategori ${cat.toUpperCase()}`;
                summaryPrice.textContent = formatRupiah(PRICE);
                totalPrice.textContent = formatRupiah(PRICE);
            }

            cards.forEach(card => {
                card.addEventListener('click', () => selectCategory(card.dataset.cat));
            });

            form && form.addEventListener('submit', function(ev) {
                const a = parseInt(hiddenAsn.value, 10) || 0;
                const u = parseInt(hiddenUmum.value, 10) || 0;
                if (a + u !== 1) {
                    ev.preventDefault();
                    alert('Pilih tepat 1 tiket (ASN atau UMUM).');
                }
            });
        })();
    </script>
</body>

</html>
