<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Top Navigation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <style>
    .nav-link {
      transition: color 0.2s ease;
    }
    .nav-link:hover {
      color: #000;
    }
    .country-btn {
      transition: all 0.2s ease;
    }
    .country-btn:hover {
      background-color: #f3f4f6;
    }
  </style>
</head>
<body class="bg-white text-gray-900">

  <!-- Top Navigation Bar -->
  <div class="bg-white border-b border-gray-200 py-3 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">

      <!-- Left: Country Selector -->
      <div>
        <button onclick="alert('Country selector would open here in real site')"
                class="country-btn flex items-center gap-2 px-5 py-2 rounded-lg border border-gray-300 hover:border-gray-400 text-sm font-medium">
          <svg width="24" height="24" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M503.2 322.8c5.7-21.3 8.8-43.7 8.8-66.8l-8.8-66.8a254.6 254.6 0 0 0-28.8-66.8l-59-66.7A255 255 0 0 0 256 0h-.2A255 255 0 0 0 96.6 55.7l-59 66.7a254.6 254.6 0 0 0-28.8 66.8L0 256v.1c0 23 3 45.4 8.8 66.7l28.8 66.8a257.3 257.3 0 0 0 59 66.7L256 512l159.4-55.7a257.3 257.3 0 0 0 59-66.7z" fill="#FFF"/>
            <path d="M503.2 189.2c5.7 21.3 8.8 43.7 8.8 66.8H0c0-23.1 3-45.5 8.8-66.8zM415.4 55.7a257.3 257.3 0 0 1 59 66.7H37.6a257.3 257.3 0 0 1 59-66.7zm59 333.9c12.6-20.6 22.4-43 28.8-66.8H8.8a254.6 254.6 0 0 0 28.8 66.8zm-59 66.7H96.6A255 255 0 0 0 255.8 512h.4a255 255 0 0 0 159.2-55.7" fill="#D80027"/>
            <path d="M0 245.6A256 256 0 0 1 256 0v256H0z" fill="#0052B4"/>
          </svg>
          <span class="font-semibold">US</span>
          <i class="fas fa-chevron-down text-xs ml-1"></i>
        </button>
      </div>

      <!-- Right Side: All Links + PayPal -->
      <div class="flex items-center gap-8">
        
        <!-- Navigation Links -->
        <nav class="flex items-center gap-7 text-sm font-medium">
          <a href="#" class="nav-link flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 01-2-2H7a2 2 0 01-2 2v16m14 0h2m-2 0h-5m-4 0H3" />
            </svg>
            Hotels
          </a>
          <a href="#" class="nav-link">Sell</a>
          <a href="#" class="nav-link flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-1a2 2 0 01-2-2H9a2 2 0 01-2-2v-1a2 2 0 012-2m0 0V9a2 2 0 012-2" />
            </svg>
            Gift Cards
          </a>
          <a href="#" class="nav-link">Help</a>
          <a href="#" class="nav-link text-amber-600 font-semibold">VIP</a>
        </nav>

        <!-- Preferred Payments Partner -->
        <div class="flex items-center gap-3 pl-6 border-l border-gray-300">
          <span class="text-xs text-gray-500 whitespace-nowrap">Preferred Payments Partner</span>
          <a href="https://www.paypal.com" target="_blank">
            <!-- Relative to current file -->
<img src="assets/images/Paypalnavbar1.png" alt="PayPal" class="h-7">

<!-- Or root-relative (works from any folder on the site) -->
<img src="/assets/images/Paypalnavbar1.png" alt="PayPal" class="h-7">

          </a>
        </div>

      </div>

    </div>
  </div>

</body>
</html>
