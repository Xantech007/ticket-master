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

  <!-- Main Navigation -->
  <nav class="bg-[0099DD] border-b border-blue-600 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-16">

        <!-- Left Section: Hamburger + Logo -->
        <div class="flex items-center gap-4">
          <!-- Hamburger Menu -->
          <button onclick="alert('Mobile menu would open here')" 
                  class="p-2 hover:bg-[#024DDF] rounded-md transition-colors text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>

          <!-- Logo -->
          <a href="#" class="flex items-center">
            <img src="assets/images/logo.png" 
                 alt="Ticketmaster" 
                 class="h-9 w-auto">
          </a>
        </div>

        <!-- Center Navigation Links -->
        <ul class="hidden md:flex items-center gap-8 text-sm font-bold text-white">
          <li><a href="#" class="nav-link">Concerts</a></li>
          <li><a href="#" class="nav-link">Sports</a></li>
          <li><a href="#" class="nav-link">Arts, Theater &amp; Comedy</a></li>
          <li><a href="#" class="nav-link">Family</a></li>
          <li><a href="#" class="nav-link">Cities</a></li>
        </ul>

        <!-- Right Side: Sign In / Register -->
        <div class="flex items-center">
          <button onclick="alert('Sign in modal would open here')"
                  class="flex items-center gap-2 px-6 py-2 border-2 border-white text-white rounded-lg hover:bg-white hover:text-[#00AEEF] transition-all font-medium text-sm">
            <i class="fas fa-user"></i>
            <span>Sign In / Register</span>
          </button>
        </div>

      </div>
    </div>
  </nav>

</body>
</html>
