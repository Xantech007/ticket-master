<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Country & Language Selector</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <style>
    /* Completely hide the default ugly Google Translate top bar and original widget dropdown */
    .goog-te-banner-frame, .goog-te-gadget, #google_translate_element {
      display: none !important;
    }
    body {
      top: 0px !important;
    }
    /* Fixes potential layout shift anomalies caused by Google scripts */
    .skiptranslate {
      display: none !important;
    }
  </style>
</head>
<body class="bg-gray-50">

<div id="google_translate_element"></div>

<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-2xl shadow-lg border border-gray-100">
  <h2 class="text-xl font-black mb-4 text-center tracking-tight text-gray-900">
    <i class="fas fa-globe text-[#024DDF] mr-1.5"></i> Select Your Country
  </h2>
  
  <div class="relative">
    <select id="countrySelector" 
            onchange="changeCountryAndLanguage()"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm font-bold appearance-none bg-white focus:outline-none focus:border-[#024DDF] focus:ring-2 focus:ring-blue-100 cursor-pointer text-gray-800">
      <option value="">-- Select Country --</option>
    </select>
    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
  </div>

  <div id="languageDisplay" class="mt-6 text-center text-xs text-gray-500 font-bold uppercase tracking-wider">
    Active Language: <span id="currentLang" class="text-[#024DDF] font-black">English</span>
  </div>
</div>

<div class="max-w-md mx-auto mt-6 p-6 bg-white rounded-2xl border border-gray-200 shadow-sm space-y-2">
  <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Sample Website Content Test</h3>
  <p class="text-xs text-gray-600 leading-relaxed font-medium">
    Welcome to our international live presentation ticket booking matrix ledger. Selecting an alternative flag destination parameter from the dropdown selection tree above will instantly parse this text fragment into localized linguistic values.
  </p>
</div>

<script>
// Country configurations mapped to standardized international two-letter ISO language codes (Google Translate standard format tags)
const countries = [
    { name: "United States", code: "US", langName: "English", langCode: "en" },
    { name: "United Kingdom", code: "GB", langName: "English", langCode: "en" },
    { name: "France", code: "FR", langName: "French", langCode: "fr" },
    { name: "Germany", code: "DE", langName: "German", langCode: "de" },
    { name: "Spain", code: "ES", langName: "Spanish", langCode: "es" },
    { name: "Italy", code: "IT", langName: "Italian", langCode: "it" },
    { name: "Brazil", code: "BR", langName: "Portuguese", langCode: "pt" },
    { name: "Mexico", code: "MX", langName: "Spanish", langCode: "es" },
    { name: "Japan", code: "JP", langName: "Japanese", langCode: "ja" },
    { name: "China", code: "CN", langName: "Chinese (Simplified)", langCode: "zh-CN" },
    { name: "India", code: "IN", langName: "Hindi", langCode: "hi" },
    { name: "Russia", code: "RU", langName: "Russian", langCode: "ru" },
    { name: "South Korea", code: "KR", langName: "Korean", langCode: "ko" }
];

const select = document.getElementById('countrySelector');

// Populate dropdown programmatically
countries.forEach(country => {
    const option = document.createElement('option');
    option.value = country.code;
    option.textContent = `${country.name} (${country.langName})`;
    select.appendChild(option);
});

// 1. Google Translate API Initialization Hook Callback function 
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}

// 2. Core Translation Execution Driver Function
function changeCountryAndLanguage() {
    const selected = countries.find(c => c.code === select.value);
    
    if (selected) {
        // Update the visual tracker label
        document.getElementById('currentLang').textContent = selected.langName;
        
        // Target the internal hidden Google frame select item element architecture
        const googleSelectElement = document.querySelector('.goog-te-combo');
        if (googleSelectElement) {
            googleSelectElement.value = selected.langCode;
            
            // Dispatch a native browser event change notification loop to force the page modification re-render
            googleSelectElement.dispatchEvent(new Event('change'));
        } else {
            console.error("Translation framework initialization payload failed to respond to DOM hooks.");
        }
    }
}
</script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
