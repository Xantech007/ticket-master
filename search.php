<?php
// search.php - Dedicated Real-Time Discovery Pipeline
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Establish structural backend connection
require_once 'config/db.php';

$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect(); 
    }
} catch (Exception $e) {
    // Graceful error silence to preserve structural front-end rendering
}

// 2. Capture the user input cleanly from the header search bar
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Arrays to safely containerize database response data allocations
$matched_artists = [];
$matched_events = [];

if (!empty($search_query) && $pdo !== null) {
    $wildcard_param = "%" . $search_query . "%";
    
    try {
        // Query A: Find Artists matching or nearest to the user input phrase string
        $artist_stmt = $pdo->prepare("SELECT * FROM artists WHERE name LIKE ? LIMIT 10");
        $artist_stmt->execute([$wildcard_param]);
        $matched_artists = $artist_stmt->fetchAll();
        
        // Query B: Find Events matching input criteria across Title, Venue, Location, Date, or Time metrics
        $event_sql = "SELECT e.*, a.name AS artist_name, a.artist_image AS artist_img 
                      FROM events e 
                      JOIN artists a ON e.artist_id = a.id 
                      WHERE e.title LIKE ? 
                         OR e.venue LIKE ? 
                         OR e.location LIKE ? 
                         OR e.date_string LIKE ? 
                         OR e.time_string LIKE ? 
                      ORDER BY e.id DESC 
                      LIMIT 20";
                      
        $event_stmt = $pdo->prepare($event_sql);
        // Bind the exact same wildcard search value across all field validation points safely
        $event_stmt->execute([$wildcard_param, $wildcard_param, $wildcard_param, $wildcard_param, $wildcard_param]);
        $matched_events = $event_stmt->fetchAll();
        
    } catch (Exception $e) {
        // Fallback error containment catch loop
    }
} else if ($pdo !== null) {
    // If search page is opened completely blank, load 20 default upcoming event instances as an initial presentation layer
    try {
        $default_stmt = $pdo->prepare("SELECT e.*, a.name AS artist_name, a.artist_image AS artist_img FROM events e JOIN artists a ON e.artist_id = a.id ORDER BY e.id DESC LIMIT 20");
        $default_stmt->execute();
        $matched_events = $default_stmt->fetchAll();
    } catch (Exception $e) {}
}

// 3. Static array container generating the top 20 requested high-traffic search options requested by layout specs
$top_searches_list = [
    "BTS", "Taylor Swift", "Coldplay", "MetLife Stadium", "Los Angeles", 
    "New York", "World Tour", "Blackpink", "Ariana Grande", "Drake", 
    "The Weeknd", "Beyoncé", "London", "Tokyo", "Bruno Mars", 
    "Ed Sheeran", "Billie Eilish", "Justin Bieber", "Post Malone", "Dua Lipa"
];
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.php"; ?>
<?php include "inc/navbar1.php"; ?> 
 
<body class="bg-white text-gray-900 font-sans antialiased">
    <div id="__next">
        <?php include "inc/header.php"; ?>

        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white py-10 px-4 md:px-8 shadow-md">
            <div class="max-w-7xl mx-auto">
                <span class="text-xs font-black uppercase tracking-widest text-blue-400 bg-blue-950/60 px-3 py-1 rounded-full">Discovery Portal Ledger Matrix</span>
                <h1 class="text-3xl md:text-5xl font-black tracking-tight mt-3">
                    <?php if (!empty($search_query)): ?>
                        Search Results for "<span class="text-blue-400 font-extrabold"><?php echo htmlspecialchars($search_query); ?></span>"
                    <?php else: ?>
                        Explore Live Presentations & Events
                    <?php endif; ?>
                </h1>
                <p class="text-xs md:text-sm text-gray-300 mt-2 max-w-xl leading-relaxed font-medium">
                    Query parsing algorithm matched instantly against active artists, arena locations, structural venue definitions, schedules, and dynamic calendar allocations securely.
                </p>
            </div>
        </div>

        <div class="border-b border-gray-200 bg-gray-50/50 shadow-sm overflow-x-auto select-none sticky top-0 z-40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto flex items-center px-4 md:px-8 space-x-2 whitespace-nowrap h-14">
                <span class="text-xs font-black uppercase tracking-wider text-gray-400 shrink-0 mr-2 flex items-center gap-1">
                    <i class="fas fa-fire text-amber-500"></i> Top 20 Searches:
                </span>
                <?php foreach ($top_searches_list as $trending_node): ?>
                    <a href="search.php?q=<?php echo urlencode($trending_node); ?>" 
                       class="px-4 py-1.5 text-xs font-bold bg-white text-gray-700 hover:text-[#024DDF] hover:bg-blue-50 hover:border-blue-300 border border-gray-200 rounded-full shadow-sm transition-all inline-block">
                        <?php echo htmlspecialchars($trending_node); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <main id="main-content" class="max-w-7xl mx-auto px-4 md:px-8 py-10 space-y-12">

            <?php if (!empty($search_query) && !empty($matched_artists)): ?>
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h3 class="text-lg font-black tracking-tight text-gray-900 uppercase flex items-center gap-2">
                            <i class="fas fa-user-music text-[#024DDF]"></i> Nearest Artist Profile Matches
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach ($matched_artists as $artist): 
                            $artist_pic = !empty($artist['artist_image']) ? "uploads/" . $artist['artist_image'] : "https://picsum.photos/id/64/400/400";
                        ?>
                            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition-all flex flex-col items-center">
                                <img src="<?php echo htmlspecialchars($artist_pic); ?>" 
                                     onerror="this.src='https://picsum.photos/id/64/400/400';" 
                                     alt="Artist Circle Thumbnail" 
                                     class="w-20 h-20 rounded-full object-cover border border-gray-100 shadow-sm mb-3">
                                <h4 class="text-sm font-black text-gray-900 tracking-tight truncate w-full"><?php echo htmlspecialchars($artist['name']); ?></h4>
                                <a href="search.php?q=<?php echo urlencode($artist['name']); ?>" 
                                   class="mt-3 text-[11px] font-bold text-[#024DDF] bg-blue-50 px-3 py-1 rounded-md hover:bg-[#024DDF] hover:text-white transition-all uppercase tracking-wider w-full text-center">
                                    View Shows
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-3 flex justify-between items-center">
                    <h3 class="text-lg font-black tracking-tight text-gray-900 uppercase flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-[#024DDF]"></i> Matched Production Concert Events (Max 20)
                    </h3>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-md">
                        <?php echo count($matched_events); ?> entry item(s) parsed
                    </span>
                </div>

                <?php if (!empty($matched_events)): ?>
                    <div class="space-y-4">
                        <?php foreach ($matched_events as $event): 
                            // Extract parts from the database matrix fallback string if structural columns differ
                            // Expected schema formats: date format structural attributes mapping safely
                            $month_val = !empty($event['month_short']) ? $event['month_short'] : 'AUG';
                            $day_num_val = !empty($event['day_number']) ? $event['day_number'] : '01';
                            $day_name_val = !empty($event['day_string']) ? $event['day_string'] : 'SAT';
                            $time_val = !empty($event['time_string']) ? $event['time_string'] : '8:00 PM';
                        ?>
                            <div class="bg-white border border-gray-200 rounded-xl p-4 md:p-6 shadow-sm hover:shadow-md hover:border-gray-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                                
                                <div class="flex items-center gap-4 border-b md:border-b-0 border-gray-100 pb-3 md:pb-0 shrink-0 min-w-[130px]">
                                    <div class="text-center bg-gray-50 rounded-lg p-2 min-w-[64px] border border-gray-100">
                                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-500"><?php echo htmlspecialchars($month_val); ?></span>
                                        <span class="block text-2xl font-black text-gray-900 leading-none my-0.5"><?php echo htmlspecialchars($day_num_val); ?></span>
                                        <span class="block text-xs font-bold text-gray-400 uppercase"><?php echo htmlspecialchars($day_name_val); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 block"><i class="far fa-clock text-blue-600 mr-1"></i> <?php echo htmlspecialchars($time_val); ?></span>
                                        <span class="text-[11px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded uppercase mt-1 inline-block">Available Ticket</span>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-tight flex items-center gap-1.5 truncate">
                                        <i class="fas fa-map-marker-alt text-[#024DDF]"></i> 
                                        <?php echo htmlspecialchars($event['location']); ?> — <?php echo htmlspecialchars($event['venue']); ?>
                                    </p>
                                    <h4 class="text-base md:text-lg font-extrabold text-gray-900 tracking-tight mt-1 truncate" title="<?php echo htmlspecialchars($event['title']); ?>">
                                        <span class="text-[#024DDF] font-black"><?php echo htmlspecialchars($event['artist_name']); ?></span>: <?php echo htmlspecialchars($event['title']); ?>
                                    </h4>
                                </div>

                                <div class="shrink-0 text-right">
                                    <a href="booking.php?event_id=<?php echo $event['id']; ?>" 
                                       class="block text-center w-full md:w-auto bg-[#024DDF] hover:bg-blue-800 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-lg transition-colors shadow-sm focus:outline-none">
                                        Find Tickets
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center bg-gray-50/50">
                        <i class="fas fa-search-minus text-4xl text-gray-300 mb-3 block"></i>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">No Direct Ledger Coordinates Found</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                            We couldn't locate data items matching your explicit parameter entries right now. Try checking alternative spellings, city codes, or arena names.
                        </p>
                        <a href="search.php" class="mt-4 inline-block text-xs font-bold text-white bg-[#024DDF] hover:bg-blue-800 px-4 py-2 rounded-lg transition-all uppercase tracking-wider">
                            Clear Search Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </main>

        <?php include "inc/footer.php"; ?>
    </div>
    
    <style>
        html { scroll-behavior: smooth; }
        body { overflow-x: hidden; }
        .overflow-x-auto::-webkit-scrollbar { display: none; }
    </style>
</body>
</html>
