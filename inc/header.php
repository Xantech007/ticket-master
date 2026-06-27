<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticketmaster Style Header</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <style>
    .nav-link {
      transition: all 0.2s ease;
    }
    .nav-link:hover {
      color: #fff;
      border-bottom: 3px solid #fff;
    }
  </style>
</head>
<body>

  <!-- Top Navbar - Increased Size -->
  <nav class="bg-[#024DDF] text-white border-b border-blue-800">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-20"> <!-- Increased height -->
        
        <!-- Left: Hamburger + Logo -->
        <div class="flex items-start gap-2">
          <!-- Hamburger (Mobile Only) -->
          <button class="block lg:hidden p-1 hover:bg-[#013ba8] rounded-md transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-6 h-6"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
        
          <!-- Logo -->
          <a href="#" class="flex items-start">
            <img src="assets/images/logo.png"
                 alt="Ticketmaster"
                 class="h-6 w-auto">
          </a>
        </div>

        <!-- Center Categories -->
        <ul class="hidden md:flex items-center gap-8 text-base font-medium">
          <li><a href="#" class="nav-link">Concerts</a></li>
          <li><a href="#" class="nav-link">Sports</a></li>
          <li><a href="#" class="nav-link">Arts, Theater &amp; Comedy</a></li>
          <li><a href="#" class="nav-link">Family</a></li>
          <li><a href="#" class="nav-link">Cities</a></li>
        </ul>

        <!-- Right: Sign In -->
        <div class="flex items-center">
            <a href="register.php"
               class="flex items-center gap-2 text-white hover:text-[#024DDF] transition-colors duration-200">
                <i class="fas fa-user text-2xl"></i>
                <span class="hidden md:inline font-medium text-sm">
                    Sign In / Register
                </span>
            </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Compact Search Bar - Reduced Size -->
  <div class="bg-[#024DDF] pb-5 pt-2">
    <div class="max-w-4xl mx-auto px-6"> <!-- Further reduced width -->
      <div class="bg-white text-gray-900 rounded-2xl shadow-lg p-1 max-w-full mx-auto">
        <form action="/search" class="flex items-center">
          
          <!-- Location -->
          <div class="flex items-center gap-3 px-5 py-2.5 flex-1 border-r border-gray-200">
            <i class="fas fa-map-marker-alt text-[#024DDF] text-xl"></i>
            <div>
              <label class="text-xs text-gray-500">Location</label>
              <input type="text" placeholder="City or Zip Code" class="bg-transparent outline-none w-full text-sm">
            </div>
          </div>

          <!-- Dates -->
          <div class="flex items-center gap-3 px-5 py-2.5 flex-1 border-r border-gray-200">
            <i class="fas fa-calendar-alt text-[#024DDF] text-xl"></i>
            <div>
              <label class="text-xs text-gray-500">Dates</label>
              <span class="text-sm">All Dates</span>
            </div>
            <i class="fas fa-chevron-down text-gray-400 ml-auto"></i>
          </div>

          <!-- Search -->
          <div class="flex items-center gap-3 px-5 py-2.5 flex-[1.5]">
            <i class="fas fa-search text-[#024DDF] text-xl"></i>
            <input type="text" placeholder="Artist, Event or Venue" class="bg-transparent outline-none flex-1 text-sm">
          </div>

          <!-- Search Button -->
          <button type="submit"
                  class="bg-[#024DDF] hover:bg-[#013ba8] text-white px-7 py-3 rounded-xl font-semibold flex items-center gap-2 transition-colors text-sm">
            Search
          </button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
