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
    </style>
</head>

<body class="h-screen w-screen">
    <div class="-z-20 h-1/2 bg-white w-screen absolute">
        <img class="w-full h-full blur-lg" src="./assets/images/banner.png">
    </div>
    <div class="flex justify-center z-30 relative">
        <main class="w-1/2 mt-12 drop-shadow-lg bg-white relative">
            <form action="beli" method="post">
                @csrf
                <!-- HEADER: START -->
                <div class="flex flex-row w-full bg-gradient-to-br from-orange-100 to-orange-50">
                    <div class="w-3/5 mr-4 border-r-2 border-black border-dashed">
                        <img class="w-full" src="./assets/images/banner.png">
                    </div>
                    <div class="w-2/5 p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium">
                                <h5>Sept</h5>
                                <h5>2-3</h5>
                            </div>
                            <div class="mt-6">
                                <h1 class="text-2xl font-medium">Vierratale Andika Mahesa Fiersa Besari 2022</h1>
                                <p>by Mandalika KORPRI Fun Night Run 2025 </p>
                            </div>
                        </div>
                        <div class="text-right text-xl font-medium">
                            {{-- <p>Rp 210K - 280K</p> --}}
                        </div>
                    </div>
                </div>
                <!-- HEADER: END -->

                <!-- TITLE: START -->
                <div class="py-6 flex items-center flex-col border-x-2 border-b-2">
                    <h1 class="text-2xl font-medium mb-2">
                        Maaf, Pembayaran Anda Gagal.
                    </h1>
                    {{-- <p>
          Fri, Sept 2, 2022 3:00 PM - Sat, Sept 3, 2022 6:00 PM WITA
        </p> --}}
                </div>
                <!-- TITLE: END -->

                <!-- CONTENT: START -->

            </form>
            <!-- CONTENT: END -->
        </main>
    </div>
</body>

</html>
