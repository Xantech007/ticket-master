<!DOCTYPE html>
<html lang="en">
  <?php include "../config/db.php"; ?>
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

  <nav class="bg-[#024DDF] text-white border-b border-blue-800">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-10 lg:h-[88px]">
  
        <div class="flex items-center gap-2 lg:gap-2">
          <button class="block lg:hidden hover:bg-[#013ba8] rounded-md transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-8 h-8"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
  
          <a href="event.php" class="flex items-center">
            <img src="assets/images/logo.png"
                 alt="Ticketmaster"
                 class="h-6 lg:h-[26px] w-auto">
          </a>

          <ul class="hidden md:flex items-center gap-8 lg:gap-5 text-base lg:text-1.5xl font-bold">
            <li><a href="search.php?q=Concerts" class="nav-link">Concerts</a></li>
            <li><a href="search.php?q=Sports" class="nav-link">Sports</a></li>
            <li><a href="search.php?q=Arts" class="nav-link">Arts, Theater &amp; Comedy</a></li>
            <li><a href="search.php?q=Family" class="nav-link">Family</a></li>
            <li><a href="search.php?q=Cities" class="nav-link">Cities</a></li>
          </ul>
          
        </div>
  
        <div class="flex items-center">
          <a href="register.php"
             class="flex items-center gap-2 lg:gap-3 text-white hover:text-[#024DDF] transition-colors duration-200">
            <i class="fa-regular fa-user text-1xl lg:text-2xl"></i>
            <span class="hidden md:inline font-bold text-sm lg:text-1.5xl">
              Sign In / Register
            </span>
          </a>
        </div>
  
      </div>
    </div>
  </nav>

  <div class="bg-[#024DDF] pt-2 pb-2 md:bg-[#024DDF] bg-white">
    <div class="max-w-4xl mx-auto px-0 md:px-6">
  
      <div class="bg-transparent md:bg-white text-gray-900 md:rounded-2xl md:shadow-lg p-0 md:p-1 max-w-full mx-auto relative">
  
        <form action="search.php" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center">
  
          <div class="flex md:flex-row flex-row w-full">
  
            <div class="flex items-center gap-3 px-4 md:px-5 py-1.5 flex-1 border-r border-gray-200">
              <i class="fas fa-map-marker-alt text-[#024DDF] text-xl"></i>
              <div>
                <label class="text-xs text-gray-500">Location</label>
                <input type="text" 
                       name="location" 
                       value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>"
                       placeholder="City or Zip Code"
                       class="bg-transparent outline-none w-full text-sm">
              </div>
            </div>
  
            <div class="flex items-center gap-3 px-4 md:px-5 py-2.5 flex-1">
              <i class="fas fa-calendar-alt text-[#024DDF] text-xl"></i>
              <div>
                <label class="text-xs text-gray-500">Dates</label>
                <span class="text-sm block text-gray-700 font-medium">All Dates</span>
              </div>
              <i class="fas fa-chevron-down text-gray-400 ml-auto"></i>
            </div>
  
          </div>
  
          <div class="flex items-center gap-3 px-4 md:px-5 py-2.5 flex-1 md:flex-[1.5] border-t md:border-t-0 border-gray-100">
            <i class="fas fa-search text-[#024DDF] text-xl"></i>
            <input type="text"
                   name="q"
                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                   placeholder="Artist, Event or Venue"
                   class="bg-transparent outline-none flex-1 text-sm font-medium"
                   required>
          </div>
  
          <div class="p-2 md:p-0">
            <button type="submit"
                    class="w-full md:w-auto bg-[#024DDF] hover:bg-[#013ba8] text-white px-7 py-3 rounded-xl font-semibold flex items-center justify-center gap-2 transition-colors text-sm">
              <i class="fas fa-search md:hidden"></i> Search
            </button>
          </div>
  
        </form>
      </div>
    </div>
  </div>

</body>
</html>
