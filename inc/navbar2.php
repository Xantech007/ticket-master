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
      color: #e11d48;
      border-bottom: 3px solid #e11d48;
    }
    .logo-svg path {
      fill: #000;
    }
  </style>
</head>
<body class="bg-white">

  <!-- Main Navigation -->
  <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-16">

        <!-- Left Section: Hamburger + Logo -->
        <div class="flex items-center gap-4">
          <!-- Hamburger Menu -->
          <button onclick="alert('Mobile menu would open here')" 
                  class="p-2 hover:bg-gray-100 rounded-md transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>

          <!-- Ticketmaster Logo -->
          <a href="#" class="flex items-center">
            <svg class="logo-svg w-40 h-8" viewBox="0 0 135 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M41.57 6.27c-4.02 0-6.97 3.63-6.97 7.4 0 3.62 2.38 5.32 5.9 5.32 1.3 0 2.66-.3 3.9-.68l.4-2.5a8.98 8.98 0 0 1-3.75.86c-2.04 0-3.23-.71-3.39-2.62l-.02-.34v-.1a6.46 6.46 0 0 1 .52-2.41c.61-1.55 1.48-2.62 3.36-2.62 1.33 0 2.02.73 2.02 2.03 0 .28-.02.54-.07.83H39.1a7.57 7.57 0 0 0-.34 2.17h7.5c.2-.9.32-1.8.32-2.72 0-3.09-2-4.62-5.02-4.62z" fill="#000"/>
              <!-- Full logo path is long - this is a representative version -->
            </svg>
          </a>
        </div>

        <!-- Center Navigation Links -->
        <ul class="hidden md:flex items-center gap-8 text-sm font-medium">
          <li><a href="#" class="nav-link text-gray-800">Concerts</a></li>
          <li><a href="#" class="nav-link text-gray-800">Sports</a></li>
          <li><a href="#" class="nav-link text-gray-800">Arts, Theater &amp; Comedy</a></li>
          <li><a href="#" class="nav-link text-gray-800">Family</a></li>
          <li><a href="#" class="nav-link text-gray-800">Cities</a></li>
          <li>
            <button onclick="alert('More dropdown would appear here')" 
                    class="nav-link text-gray-800 flex items-center gap-1">
              More 
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
          </li>
        </ul>

        <!-- Right Section: Search + Account -->
        <div class="flex items-center gap-4">
          
          <!-- Search Button -->
          <button onclick="alert('Search bar would toggle here')" 
                  class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 01-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden md:inline text-sm font-medium">Search</span>
          </button>

          <!-- Sign In / Register -->
          <button onclick="alert('Sign in modal would open here')"
                  class="flex items-center gap-2 px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
            <i class="fas fa-user"></i>
            <span>Sign In / Register</span>
          </button>
        </div>
      </div>
    </div>
  </nav>

</body>
</html>
