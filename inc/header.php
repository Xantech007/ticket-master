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

  <!-- Main Header with Search Bar -->
  <header class="bg-[#0099dd] text-white sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-6">

      <!-- Top Navigation Bar -->
      <div class="flex items-center justify-between h-16 border-b border-blue-600">
        
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

        <!-- Main Categories -->
        <nav class="hidden md:flex items-center gap-7 text-sm font-medium">
          <a href="#" class="nav-link">Concerts</a>
          <a href="#" class="nav-link">Sports</a>
          <a href="#" class="nav-link">Arts, Theater &amp; Comedy</a>
          <a href="#" class="nav-link">Family</a>
          <a href="#" class="nav-link">Cities</a>
        </nav>

        <!-- Right: Sign In -->
        <button onclick="alert('Sign in would open here')"
                class="flex items-center gap-2 px-5 py-2 border border-white rounded-lg hover:bg-white hover:text-[#0099dd] transition-all font-medium">
          <i class="fas fa-user"></i>
          <span>Sign In / Register</span>
        </button>
      </div>

      <!-- Search Bar Section -->
      <div class="py-5">
        <div class="bg-white text-gray-900 rounded-2xl shadow-lg p-2">
          <form action="/search" class="flex items-center gap-2">
            
            <!-- Location -->
            <div class="flex-1 flex items-center gap-3 px-5 py-3 border-r border-gray-200">
              <i class="fas fa-map-marker-alt text-[#0099dd] text-2xl"></i>
              <div class="flex-1">
                <label class="text-xs text-gray-500 block">Location</label>
                <input type="text" 
                       placeholder="City or Zip Code" 
                       class="w-full bg-transparent outline-none text-base placeholder-gray-400">
              </div>
            </div>

            <!-- Dates -->
            <div class="flex-1 flex items-center gap-3 px-5 py-3 border-r border-gray-200">
              <i class="fas fa-calendar-alt text-[#0099dd] text-2xl"></i>
              <div class="flex-1">
                <label class="text-xs text-gray-500 block">Dates</label>
                <span class="text-base">All Dates</span>
              </div>
              <i class="fas fa-chevron-down text-gray-400"></i>
            </div>

            <!-- Search Input -->
            <div class="flex-[2] flex items-center gap-3 px-5 py-3">
              <i class="fas fa-search text-[#0099dd] text-2xl"></i>
              <input type="text" 
                     placeholder="Artist, Event or Venue" 
                     class="flex-1 bg-transparent outline-none text-base placeholder-gray-400">
            </div>

            <!-- Search Button -->
            <button type="submit"
                    class="bg-[#0099dd] hover:bg-[#0088c2] text-white px-10 py-4 rounded-xl font-semibold transition-colors flex items-center gap-2">
              <span>Search</span>
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>

    </div>
  </header>

</body>
</html>
