<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticketmaster Style Header</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <style>
    .nav-link { transition: all 0.2s ease; }
    .nav-link:hover { color: #fff; border-bottom: 3px solid #fff; }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <nav class="bg-[#0099dd] text-white border-b border-blue-700">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-16">

        <!-- Left: Hamburger + Logo -->
        <div class="flex items-center gap-4">
          <button class="p-2 hover:bg-blue-700 rounded-md transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
          
          <a href="#">
            <img src="assets/images/logo.png" alt="Ticketmaster" class="h-9 w-auto">
          </a>
        </div>

        <!-- Center Categories -->
        <ul class="hidden md:flex items-center gap-7 text-sm font-medium">
          <li><a href="#" class="nav-link">Concerts</a></li>
          <li><a href="#" class="nav-link">Sports</a></li>
          <li><a href="#" class="nav-link">Arts, Theater &amp; Comedy</a></li>
          <li><a href="#" class="nav-link">Family</a></li>
          <li><a href="#" class="nav-link">Cities</a></li>
        </ul>

        <!-- Right: Sign In -->
        <button onclick="alert('Sign in would open here')"
                class="flex items-center gap-2 px-5 py-2 border border-white rounded-lg hover:bg-white hover:text-[#0099dd] transition-all font-medium text-sm">
          <i class="fas fa-user"></i>
          <span>Sign In / Register</span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Compact Search Bar -->
  <div class="bg-[#0099dd] pb-6 pt-3">
    <div class="max-w-6xl mx-auto px-6">
      <div class="bg-white text-gray-900 rounded-2xl shadow-lg p-2 max-w-5xl mx-auto"> <!-- Reduced width -->
        <form action="/search" class="flex items-center">
          
          <!-- Location -->
          <div class="flex items-center gap-3 px-6 py-3 flex-1 border-r border-gray-200">
            <i class="fas fa-map-marker-alt text-[#0099dd] text-2xl"></i>
            <div>
              <label class="text-xs text-gray-500">Location</label>
              <input type="text" placeholder="City or Zip Code" 
                     class="bg-transparent outline-none w-full text-sm">
            </div>
          </div>

          <!-- Dates -->
          <div class="flex items-center gap-3 px-6 py-3 flex-1 border-r border-gray-200">
            <i class="fas fa-calendar-alt text-[#0099dd] text-2xl"></i>
            <div>
              <label class="text-xs text-gray-500">Dates</label>
              <span class="text-sm">All Dates</span>
            </div>
            <i class="fas fa-chevron-down text-gray-400 ml-auto"></i>
          </div>

          <!-- Search -->
          <div class="flex items-center gap-3 px-6 py-3 flex-[1.8]">
            <i class="fas fa-search text-[#0099dd] text-2xl"></i>
            <input type="text" placeholder="Artist, Event or Venue" 
                   class="bg-transparent outline-none flex-1 text-sm">
          </div>

          <!-- Search Button -->
          <button type="submit" 
                  class="bg-[#0099dd] hover:bg-[#0088c2] text-white px-8 py-4 rounded-xl font-semibold flex items-center gap-2 transition-colors">
            Search
          </button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
