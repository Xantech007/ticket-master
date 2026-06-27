<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Country & Language Selector</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-2xl shadow-lg">
  <h2 class="text-xl font-semibold mb-4 text-center">Select Your Country</h2>
  
  <div class="relative">
    <select id="countrySelector" 
            onchange="changeCountryAndLanguage()"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-lg appearance-none focus:outline-none focus:border-blue-500 cursor-pointer">
      <option value="">-- Select Country --</option>
    </select>
    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
  </div>

  <div id="languageDisplay" class="mt-6 text-center text-sm text-gray-600">
    Current Language: <span id="currentLang" class="font-semibold text-blue-600">English</span>
  </div>
</div>

<script>
// Country list with languages
const countries = [
    { name: "United States", code: "US", lang: "English" },
    { name: "United Kingdom", code: "GB", lang: "English" },
    { name: "France", code: "FR", lang: "French" },
    { name: "Germany", code: "DE", lang: "German" },
    { name: "Spain", code: "ES", lang: "Spanish" },
    { name: "Italy", code: "IT", lang: "Italian" },
    { name: "Brazil", code: "BR", lang: "Portuguese" },
    { name: "Mexico", code: "MX", lang: "Spanish" },
    { name: "Japan", code: "JP", lang: "Japanese" },
    { name: "China", code: "CN", lang: "Chinese" },
    { name: "India", code: "IN", lang: "Hindi / English" },
    { name: "Russia", code: "RU", lang: "Russian" },
    { name: "South Korea", code: "KR", lang: "Korean" },
    { name: "Canada", code: "CA", lang: "English / French" },
    { name: "Australia", code: "AU", lang: "English" },
    // Add more as needed
];

const select = document.getElementById('countrySelector');

// Populate dropdown
countries.forEach(country => {
    const option = document.createElement('option');
    option.value = country.code;
    option.textContent = country.name;
    select.appendChild(option);
});

function changeCountryAndLanguage() {
    const selected = countries.find(c => c.code === select.value);
    
    if (selected) {
        document.getElementById('currentLang').textContent = selected.lang;
        
        // Here you can add real language translation logic (e.g., i18n library)
        alert(`Country changed to ${selected.name}\nLanguage set to: ${selected.lang}`);
        
        // Example: You could reload page with ?lang=xx or use JS i18n
    }
}
</script>

</body>
</html>
