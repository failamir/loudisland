<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/output.css" rel="stylesheet">
  <title>Loud island</title>
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
    <img class="w-full h-full blur-lg" src="./assets/images/banner.jpg">
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
              <p>by Loud Island </p>
            </div>
          </div>
          <div class="text-right text-xl font-medium">
            <p>Rp 210K - 280K</p>
          </div>
        </div>
      </div>
      <!-- HEADER: END -->

      <!-- TITLE: START -->
      <div class="py-6 flex items-center flex-col border-x-2 border-b-2">
        <h1 class="text-2xl font-medium mb-2">
          Vierratale Andika Mahesa Fiersa Besari
        </h1>
        <p>
          Fri, Sept 2, 2022 3:00 PM - Sat, Sept 3, 2022 6:00 PM WITA
        </p>
      </div>
      <!-- TITLE: END -->

      <!-- CONTENT: START -->
      <div class="flex flex-row justify-between w-full border-x-2 border-b-2 px-4">
        <!-- FORM TICKET: START -->
        <div class="w-3/5">
          <div class="flex flex-row items-center justify-between w-full mt-6">
            <div>
              <h1 class="text-xl">
                Day 1 - Friday, September 2022
                Rp 210.000
              </h1>
              <p>
                Sales end on Sept 2, 2022
                This ticket is only valid for Friday, 2 September 2022.
              </p>
            </div>
            <div>
              <select name="day 1" id="day_1">
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
              </select>
            </div>
          </div>
          <div class="flex flex-row items-center justify-between w-full mt-6">
            <div>
              <h1 class="text-xl">
                Day 2 - Saturday, September 2022
                Rp 210.000
              </h1>
              <p>
                Sales end on Sept 3, 2022
                This ticket is only valid for Saturday, 3 September 2022.
              </p>
            </div>
            <div>
              <select name="day 2" id="day_2">
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
              </select>
            </div>
          </div>
          <div class="flex flex-row items-center justify-between w-full mt-6">
            <div>
              <h1 class="text-xl">
                Day 1 & 2 - Friday & Saturday, September 2022
                Rp 280.000
              </h1>
              <p>
                Sales end on Sept 3, 2022
                This ticket is valid for Friday & Saturday, 2 & 3 September 2022.
              </p>
            </div>
            <div>
              <select name="day 3" id="day_3">
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
              </select>
            </div>
          </div>
        </div>
        <!-- FORM TICKET: END -->

        <!-- ORDER SUMMARY: START -->
        <div class="w-2/5 p-4 ml-4">
          <h1 class="text-xl font-medium">Order Sumary</h1>
          <div class="flex justify-between text-xl font-medium mt-4">
            <p>1 x Day 1 - Friday, 2 September 2022</p>
            <p>Rp 210.000</p>
          </div>
          <div class="flex justify-between mt-8 border-t-2 pt-4">
            <h1 class="text-xl font-medium">Total</h1>
            <h1 class="text-xl font-medium">Rp 210.000</h1>
          </div>
          <div class="flex justify-between mt-8">
            {{-- <h1 class="text-xl font-medium">Payment Method</h1>
            <select name="payment" id="payment">
              <option value="1">Bank Transfer</option>
              <option value="2">Credit Card</option>
              <option value="3">Debit Card</option>
            </select> --}}
            <button type="submit" style="box-shadow: 20px 20px 50px grey;" class="btn btn-warning shadow bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
              Buy Now
            </button>
        </div>
        <!-- ORDER SUMMARY: END -->
      </div>
    </form>
      <!-- CONTENT: END -->
    </main>
  </div>
</body>
</html>