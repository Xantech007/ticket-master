<style>
/* Mobile-First + Ticketmaster Inspired styling safely embedded */
* { box-sizing: border-box; }
.container {
    max-width: 1280px;
    margin: auto;
    padding: 15px;
}
:root {
    --tm-red: #e4002b;
    --tm-dark: #121212;
}
.hero {
    height: 380px;
    background: linear-gradient(rgba(18,18,18,0.65), rgba(18,18,18,0.75)), 
                url('https://picsum.photos/id/1015/2000/1200') center/cover no-repeat;
    border-radius: 16px;
    display: flex;
    align-items: center;
    color: white;
    position: relative;
    margin-bottom: 50px;
}
.hero-content {
    max-width: 580px;
    padding-left: 50px;
}
.hero h1 {
    font-size: 42px;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 12px;
}
.hero p {
    font-size: 18px;
    margin-bottom: 25px;
    opacity: 0.95;
}
.carousel-wrapper {
    position: relative;
    margin-bottom: 40px;
}
.carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 10px 5px;
}
.carousel::-webkit-scrollbar { display: none; }
.event-card {
    min-width: 260px;
    max-width: 260px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.event-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
}
.date-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: white;
    color: black;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 800;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.event-info { padding: 15px; }
.event-info h3 { font-size: 16px; font-weight: 700; margin: 0 0 6px 0; color: #111; }
.event-info p { font-size: 13px; margin: 2px 0; color: #666; }
.event-info .price { font-weight: 700; color: #024DDF; margin-top: 8px; }
.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 1px solid #ddd;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 10;
}
.carousel-btn.left { left: -15px; }
.carousel-btn.right { right: -15px; }
</style>

<div class="container" id="main-content">
    <div class="hero">
        <div class="hero-content">
            <h1>Experience Live Entertainment</h1>
            <p>Discover and book tickets for your favorite global performers instantly.</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black tracking-tight text-gray-900">Trending Events</h2>
    </div>

    <div class="carousel-wrapper">
        <button class="carousel-btn left" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), -280)">←</button>
        <div class="carousel">
            <?php if (empty($dynamic_events)): ?>
                <div class="w-full text-center py-12 text-gray-400 font-medium border-2 border-dashed border-gray-200 rounded-2xl">
                    No active events found. Access the administration terminal panel to add records.
                </div>
            <?php else: ?>
                <?php foreach ($dynamic_events as $event): 
                    $timestamp = strtotime($event['event_date']);
                    $badgeDate = strtoupper(date('M d', $timestamp));
                ?>
                    <div class="event-card" onclick="window.location.href='section_selection.php?event_id=<?php echo (int)$event['id']; ?>'">
                        <div style="position:relative;">
                            <img src="uploads/<?php echo htmlspecialchars($event['artist_image']); ?>" onerror="this.src='https://picsum.photos/id/625/600/400';" alt="Artist Image">
                            <div class="date-badge"><?php echo htmlspecialchars($badgeDate); ?></div>
                        </div>
                        <div class="event-info">
                            <h3><?php echo htmlspecialchars($event['artist_name']); ?></h3>
                            <p><?php echo htmlspecialchars($event['title']); ?> • <?php echo htmlspecialchars($event['venue']); ?></p>
                            <p class="price">View Seats</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="carousel-btn right" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), 280)">→</button>
    </div>
</div>

<script>
function scrollCarousel(carousel, offset) {
    carousel.scrollBy({ left: offset, behavior: 'smooth' });
}
</script>
