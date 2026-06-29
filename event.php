<?php
// event.php - Top of Page Data Layer
// Enable error displaying so we can pinpoint issues if database structural details are missing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Corrected Path: Load the connection file from the 'config' folder
require_once 'config/db.php';

// 2. Instantiate your "Database" class and invoke the connect() function to expose $pdo safely
$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect(); 
    }
} catch (Exception $e) {
    // Catch initial structural connection issues down below gracefully
}

// Safe URL Parameter Fetching (Fallback definitions if no ID is passed yet)
$artist_name = "BTS";
$event_title_overlay = "WORLD TOUR 'ARIRANG'";
$event_banner_image = "https://picsum.photos/id/1015/2000/800"; // Fallback presentation banner
$artist_image = "https://picsum.photos/id/64/400/400"; // Fallback circular artist image profile

// If an ID parameter is appended, load matching database row allocations
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        if (isset($pdo) && $pdo !== null) {
            $stmt = $pdo->prepare("SELECT e.*, a.name AS artist_name, a.artist_image AS artist_img FROM events e JOIN artists a ON e.artist_id = a.id WHERE e.id = ?");
            $stmt->execute([$id]);
            $event_data = $stmt->fetch();
            if ($event_data) {
                $artist_name = $event_data['artist_name'];
                $event_title_overlay = $event_data['title'];
                
                // Assigning uploaded graphic assets using column definitions from phpMyAdmin
                if (!empty($event_data['stadium_image'])) {
                    $event_banner_image = "uploads/" . $event_data['stadium_image'];
                }
                if (!empty($event_data['artist_img'])) {
                    $artist_image = "uploads/" . $event_data['artist_img'];
                }
            }
        }
    } catch (Exception $e) {
        // Safe silence mode to preserve page layout continuity
    }
}

// Simulated dynamic loop container populated to output the exact block layout sequence you requested
$concerts_results = [
    [
        'id' => 101,
        'month' => 'Aug',
        'day_num' => '01',
        'day_name' => 'Sat',
        'time' => '8:00 PM',
        'location' => 'East Rutherford, NJ',
        'venue' => 'MetLife Stadium',
        'title' => "BTS WORLD TOUR 'ARIRANG' IN EAST RUTHERFORD"
    ],
    [
        'id' => 102,
        'month' => 'Aug',
        'day_num' => '02',
        'day_name' => 'Sun',
        'time' => '08:00 PM',
        'location' => 'East Rutherford, NJ',
        'venue' => 'MetLife Stadium',
        'title' => "BTS WORLD TOUR 'ARIRANG' IN EAST RUTHERFORD"
    ]
];

$total_concerts_count = count($concerts_results);
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.php"; ?>
<?php include "inc/navbar.php"; ?> 
 
<body class="bg-white text-gray-900 font-sans antialiased">
    <div id="__next">
        <div class="sc-d727d306-0 llCpYZ">
         Your browser is not supported. For the best experience, use any of these supported browsers:
         <a class="Link__StyledLink-sc-pudy0l-0 bfasNL" href="https://www.google.com/chrome/">Chrome</a>, 
         <a class="Link__StyledLink-sc-pudy0l-0 bfasNL" href="https://www.mozilla.org/firefox/new/">Firefox</a>, 
         <a class="Link__StyledLink-sc-pudy0l-0 bfasNL" href="https://support.apple.com/downloads/safari">Safari</a>, 
         <a class="Link__StyledLink-sc-pudy0l-0 bfasNL" href="https://www.microsoft.com/edge">Edge</a>.
        </div>
        <section class="sc-7f6df46b-0 frSDhw">
         <a class="Link__StyledLink-sc-pudy0l-0 eYZQRC sc-7f6df46b-1 firzHb" href="#main-content">
          Skip to main content
         </a>
        </section>    
        
        <?php include "inc/header.php"; ?>

        <div class="relative w-full h-[360px] md:h-[480px] bg-black overflow-hidden select-none">
            <img src="<?php echo htmlspecialchars($event_banner_image); ?>" 
                 onerror="this.src='https://picsum.photos/id/625/2000/1000';" 
                 alt="Full Event Banner" 
                 class="w-full h-full object-cover opacity-80">
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            
            <div class="absolute bottom-0 left-0 w-full p-6 md:p-12 text-white max-w-4xl flex items-center gap-4 md:gap-6">
                <img src="<?php echo htmlspecialchars($artist_image); ?>" 
                     onerror="this.src='https://picsum.photos/id/64/400/400';" 
                     alt="Artist Profile Image" 
                     class="w-16 h-16 md:w-24 md:h-24 rounded-full object-cover border-2 border-white/40 shadow-lg shrink-0">
                
                <div>
                    <p class="text-xs uppercase tracking-widest font-black text-blue-400 mb-1">Live Presentation Spot</p>
                    <h1 class="text-3xl md:text-6xl font-black tracking-tight leading-none drop-shadow-md">
                        <?php echo htmlspecialchars($artist_name); ?>
                    </h1>
                    <h2 class="text-xl md:text-2xl font-bold mt-2 text-gray-200 opacity-95 drop-shadow-sm">
                        <?php echo htmlspecialchars($event_title_overlay); ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm overflow-x-auto select-none">
            <div class="max-w-7xl mx-auto flex items-center px-4 md:px-8 space-x-1 whitespace-nowrap min-w-max h-14">
                <a href="#concerts-section" class="px-5 py-2 text-sm font-bold text-[#024DDF] border-b-2 border-[#024DDF] hover:text-blue-800 transition-colors">Concerts</a>
                <a href="#vip-section" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent transition-colors">VIP Experience</a>
                <a href="#about-section" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent transition-colors">About</a>
                <a href="#faqs-section" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent transition-colors">FAQs</a>
                <a href="#reviews-section" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent transition-colors">Reviews</a>
                <a href="#gifts-section" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent transition-colors">Gifts</a>
            </div>
        </div>

        <main id="main-content" class="max-w-7xl mx-auto px-4 md:px-8 py-10 space-y-16">
            
            <div id="concerts-section" class="scroll-mt-16">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-3xl font-black tracking-tight text-gray-900">
                        "<strong>CONCERTS</strong> • <?php echo $total_concerts_count; ?> RESULTS"
                    </h3>
                </div>

                <div class="space-y-4">
                    <?php foreach ($concerts_results as $concert): ?>
                        <div class="bg-white border border-gray-200 rounded-xl p-4 md:p-6 shadow-sm hover:shadow-md hover:border-gray-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                            
                            <div class="flex items-center gap-4 border-b md:border-b-0 border-gray-100 pb-3 md:pb-0 shrink-0 min-w-[130px]">
                                <div class="text-center bg-gray-50 rounded-lg p-2 min-w-[64px]">
                                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500"><?php echo htmlspecialchars($concert['month']); ?></span>
                                    <span class="block text-2xl font-black text-gray-900 leading-none my-0.5"><?php echo htmlspecialchars($concert['day_num']); ?></span>
                                    <span class="block text-xs font-bold text-gray-400 uppercase"><?php echo htmlspecialchars($concert['day_name']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-gray-900 block"><i class="far fa-clock text-blue-600 mr-1"></i> <?php echo htmlspecialchars($concert['time']); ?></span>
                                    <span class="text-[11px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded uppercase mt-1 inline-block">Verified Option</span>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-tight flex items-center gap-1.5 truncate">
                                    <i class="fas fa-map-marker-alt text-[#024DDF]"></i> 
                                    <?php echo htmlspecialchars($concert['location']); ?> - <?php echo htmlspecialchars($concert['venue']); ?>
                                </p>
                                <h4 class="text-base md:text-lg font-extrabold text-gray-900 tracking-tight mt-1 truncate" title="<?php echo htmlspecialchars($concert['title']); ?>">
                                    <?php echo htmlspecialchars($concert['title']); ?>
                                </h4>
                            </div>

                            <div class="shrink-0 text-right">
                                <a href="booking.php?event_id=<?php echo $concert['id']; ?>" 
                                   class="block text-center w-full md:w-auto bg-[#024DDF] hover:bg-blue-800 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Find Tickets
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="vip-section" class="scroll-mt-16 bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-8 shadow-xl border border-slate-800">
                <div class="max-w-2xl">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-400 bg-blue-950/60 px-3 py-1 rounded-full">Category Box B</span>
                    <h3 class="text-3xl font-black tracking-tight mt-3">VIP Experience Premium Packages</h3>
                    <p class="text-sm text-gray-300 mt-2 leading-relaxed">
                        Unlock early access, behind-the-scenes premium field lounge passes, artist merchandise, and structural soundcheck entries. Package details will initialize dynamically during the individual seating configuration allocation step.
                    </p>
                </div>
            </div>

            <div id="reviews-section" class="scroll-mt-16 bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <span class="text-xs font-bold uppercase tracking-widest text-gray-400 block mb-2">Category Box C</span>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-4">Verified Fan Reviews</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-center text-yellow-400 text-xs mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="text-xs text-gray-600 font-medium italic">"An absolute phenomenal audio arrangement execution framework. Clean tracking updates from start to finish."</p>
                        <span class="text-[10px] font-bold text-gray-400 block mt-3">— Ticketmaster Verified Fan</span>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-center text-yellow-400 text-xs mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="text-xs text-gray-600 font-medium italic">"The stadium configuration matches perfectly. Seamless order confirmation response pipeline."</p>
                        <span class="text-[10px] font-bold text-gray-400 block mt-3">— Secondary Market Ticket Holder</span>
                    </div>
                </div>
            </div>

            <div id="about-section" class="scroll-mt-16">
                <span class="text-xs font-bold uppercase tracking-widest text-gray-400 block mb-1">Category Box D</span>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">About the Production Program</h3>
                <p class="text-sm text-gray-600 mt-2 leading-relaxed max-w-4xl">
                    Experience this generation's masterclass musical performance running globally. This show brings advanced synchronized visual arts choreography directly to high-capacity arenas. Book verified tickets securely through our real-time seating coordinate ledger system.
                </p>
            </div>

            <div id="faqs-section" class="scroll-mt-16 bg-gray-50 rounded-2xl p-8 border border-gray-100 space-y-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-400 block mb-1">Category Box E</span>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-4">Frequently Asked Questions</h3>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-900">Are the ticket allocations verified in real time?</h4>
                    <p class="text-xs text-gray-500 mt-1">Yes. All sections, row listings, and concrete seat configurations are synced instantly with our administration inventory ledger validation system loops.</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-900">Can I transfer my tickets on the secondary market after purchase?</h4>
                    <p class="text-xs text-gray-500 mt-1">Yes, purchases are linked directly to your user account dashboard, enabling you to manage verification uploads cleanly.</p>
                </div>
            </div>

            <div id="gifts-section" class="scroll-mt-16 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center bg-gray-50/50">
                <span class="text-xs font-bold uppercase tracking-widest text-gray-400 block mb-1">Category Box F</span>
                <i class="fas fa-gift text-3xl text-blue-600 mb-2 block"></i>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Souvenirs & Collectors Commemorative Items</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">Digital tokens, unique visual prints, and memorabilia tokens are accessible inside your confirmation wallet framework upon finalizing checkouts.</p>
            </div>

        </main>

        <?php include "inc/footer.php"; ?>
    </div>

    <div id="modals" data-testid="modals"></div>
    
    <style>
        html { scroll-behavior: smooth; }
        body { overflow-x: hidden; }
        .sticky::-webkit-scrollbar { display: none; }
    </style>
</body>
</html>
