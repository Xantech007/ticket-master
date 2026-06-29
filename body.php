<style>
/* Mobile-First + Ticketmaster Inspired */
* { box-sizing: border-box; }
.container {
    max-width: 1280px;
    margin: auto;
    padding: 15px;
}

/* Colors */
:root {
    --tm-blue: #024ddf;
    --tm-dark: #121212;
}

/* HERO */
.hero {
    width: 100vw;
    margin: 0;
    padding: 0;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    overflow: hidden;
}

/* Mobile: image decides the height */
.hero img {
    width: 100%;
    height: auto;
    display: block;
}

.hero-content {
    position: absolute;
    top: calc(50% + 30px);   /* moves content 15px down */
    left: 15px;
    transform: translateY(-50%);
    max-width: 580px;
    color: #fff;
    z-index: 3;
}

.hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(18,18,18,.45), rgba(18,18,18,.65));
    z-index: 1;
}

.hero-content {
    z-index: 2;
}

/* Desktop */
@media (min-width: 992px) {

    /* Show only the top 60% of the image */
    .hero {
        height: 60vh;      /* adjust if you want a taller/shorter hero */
    }

    .hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top; /* crop from the bottom, keep the top */
    }

    .hero-content {
        left: 60px;
    }
}

.btn-find {
    background: var(--tm-blue);
    color: white;
    padding: 11px 20px;   /* reduced horizontal padding */
    font-size: 16px;
    font-weight: 700;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    min-width: 125px;     /* optional fixed width */
    text-align: center;
    transition: all 0.3s;
}
.btn-find:hover {
    background: #024DDF;
    transform: scale(1.05);
}

/* SECTION TITLES */
.section-title {
    font-size: 28px;
    font-weight: 700;
    margin: 45px 0 20px;
    color: var(--tm-dark);
    display: flex;
    align-items: center;
    gap: 12px;
}

/* EVENT CARD - Ticketmaster Polish */
.event-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s;
    position: relative;
}
.event-card:hover {
    transform: translateY(-6px);
}
.event-card img {
    width: 100%;
    height: 170px;
    object-fit: cover;
}
.event-info {
    padding: 16px;
    margin-left: 0;
    padding-left: 0;
}
.event-info h3 {
    font-size: 17.5px;
    margin: 0 0 6px;
    line-height: 1.3;
}
.event-info p {
    color: #555;
    font-size: 14px;
    margin: 3px 0;
}
.date-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: white;
    color: var(--tm-blue);
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: 700;
    text-align: center;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.price {
    color: var(--tm-blue);
    font-weight: 700;
    font-size: 15px;
}

/* GRID */
.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
}

/* HORIZONTAL SCROLL CAROUSEL */
.carousel-wrapper {
    position: relative;
}
.carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 20px;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}
.carousel::-webkit-scrollbar { display: none; }
.carousel .event-card {
    min-width: 260px;
    flex-shrink: 0;
}

/* Carousel Arrows */
.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}
.carousel-btn.left { left: -18px; }
.carousel-btn.right { right: -18px; }

/* BUTTONS */
.btn {
    padding: 12px 24px;
    border-radius: 20px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}
.btn-primary {
    background: var(--tm-blue);
    color: white;
}
.btn-outline {
    border: 2px solid var(--tm-blue);
    color: var(--tm-blue);
}
.btn-primary:hover { background: #024DDF; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .hero h1 {
        font-size: 24px;
    }

    .section-title {
        font-size: 24px;
        margin: 35px 0 18px;
    }

    .carousel-btn {
        display: none;
    }
}
</style>

<!-- HERO (Outside .container) -->
<div class="hero">
    <img src="assets/images/image.png" alt="Hero Banner">

    <div class="hero-content">
        <h1>WICKED The Musical</h1>
        <p>Text</p>
        <a href="#" class="btn-find">Find Tickets</a>
    </div>
</div>


<div class="container">

    <!-- 1. NO REDIRECT EVENTS - 1 Grid -->
    <div class="grid-4">
        <div style="position:relative;">
            <img src="assets/images/summer-lawn.jpeg" alt="Make it a Summer of Live music">
        </div>
    <div class="event-info">
        <p>4 LAWN TICKETS FOR $99</p>
        <h3>Make it a Summer of Live music</h3>
    </div>


    <!-- 2. UPCOMING EVENTS - 4 Grid -->
    <div class="grid-4">
        <div style="position:relative;">
            <img src="assets/images/bts.jpg" alt="Taylor Swift">
        </div>
    <div class="event-info">
        <p>MetLife Stadium • East Rutherford, NJ</p>
        <h3>BTS - The ARIRANG WORLD TOUR</h3>
    </div>

    <div class="grid-4">
        <div style="position:relative;">
            <img src="assets/images/bts.jpg" alt="Taylor Swift">
        </div>
    <div class="event-info">
        <p>MetLife Stadium • East Rutherford, NJ</p>
        <h3>BTS - The ARIRANG WORLD TOUR</h3>
    </div>

    <div class="grid-4">
        <div style="position:relative;">
            <img src="assets/images/bts.jpg" alt="Taylor Swift">
        </div>
    <div class="event-info">
        <p>MetLife Stadium • East Rutherford, NJ</p>
        <h3>BTS - The ARIRANG WORLD TOUR</h3>
    </div>


    <!-- 3. TRENDING SEARCHES -->
    <h2 class="section-title"><i class="fa-solid fa-fire icon-red"></i> Trending Searches</h2>
    <div class="carousel-wrapper">
        <button class="carousel-btn left" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), -280)">←</button>
        <div class="carousel" id="trending-carousel">
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Olivia.jpg" alt="">
                    <div class="date-badge">OCT 29</div>
                </div>
                <div class="event-info">
                    <h3>Olivia Rodrigo</h3>
                    <p>The Unraveled Tour • Value City Arena</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Benson-Boone.jpg" alt="">
                    <div class="date-badge">JUL 07</div>
                </div>
                <div class="event-info">
                    <h3>Benson Boone</h3>
                    <p>Wanted Man Tour • PPG Paints Arena</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Bad-Bunny.jpg" alt="">
                    <div class="date-badge">AUG 05</div>
                </div>
                <div class="event-info">
                    <h3>Bad Bunny</h3>
                    <p>DeBÍ TiRAR MáS FOToS World Tour • Strawberry Arena</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Gracie-Abrams.jpg" alt="">
                    <div class="date-badge">DEC 02</div>
                </div>
                <div class="event-info">
                    <h3>Gracie Abrams</h3>
                    <p>The Look At My Life Tour • Ball Arena</p>
                </div>
            </div>
        </div>
        <button class="carousel-btn right" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), 280)">→</button>
    </div>


        <!-- add "RECENTLY VIEWED" section here, hide if no data exists for the users -->
    

    <!-- 4. SPONSORED PRESALES & OFFERS - 3 Events -->
    <h2 class="section-title"><i class="fa-solid fa-star icon-red"></i> Sponsored Presales &amp; Offers</h2>
    <div class="grid-4" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Gracie-Abrams.jpg" alt="Sabrina Carpenter">
                <div class="date-badge">DEC<br><strong>02</strong></div>
            </div>
            <div class="event-info">
                <p>Wed, Dec 02 • 6:30 PM</p> <!-- add day_time column-->
                <h3>Gracie Abrams - The Look At My Life Tour</h3> <!-- title column-->
                <p>Ball Arena • DENVER, CO</p> <!-- add location column • venue column-->
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Gracie-Abrams.jpg" alt="Sabrina Carpenter">
                <div class="date-badge">DEC<br><strong>02</strong></div>
            </div>
            <div class="event-info">
                <p>Wed, Dec 02 • 6:30 PM</p> <!-- add day_time column-->
                <h3>Gracie Abrams - The Look At My Life Tour</h3> <!-- title column-->
                <p>Ball Arena • DENVER, CO</p> <!-- add location column • venue column-->
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Gracie-Abrams.jpg" alt="Sabrina Carpenter">
                <div class="date-badge">DEC<br><strong>02</strong></div>
            </div>
            <div class="event-info">
                <p>Wed, Dec 02 • 6:30 PM</p> <!-- add day_time column-->
                <h3>Gracie Abrams - The Look At My Life Tour</h3> <!-- title column-->
                <p>Ball Arena • DENVER, CO</p> <!-- add location column • venue column-->
            </div>
        </div>
    </div>

    <!-- 5. POPULAR NEAR YOU -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin:45px 0 20px;">
        <h2 class="section-title" style="margin:0;"><i class="fa-solid fa-location-dot icon-red"></i> Popular Near You</h2>
        <a href="#" class="btn btn-outline">See All Events</a>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-btn left" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), -280)">←</button>
        <div class="carousel" id="near-carousel">
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/ChappellRoan.jpg" alt="">
                    <div class="date-badge">AUG 29</div>
                </div>
                <div class="event-info">
                    <p>POP</p> <!-- add genre column under artists table-->
                    <h3>Chappell Roan</h3>  <!-- add artist_name column under artists table-->
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Benson-Boone.jpg" alt="">
                    <div class="date-badge">JUL 07</div>
                </div>
                <div class="event-info">
                    <h3>Benson Boone</h3>
                    <p>Wanted Man Tour • PPG Paints Arena</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="https://picsum.photos/id/201/600/400" alt="">
                    <div class="date-badge">AUG 02</div>
                </div>
                <div class="event-info">
                    <h3>Drake</h3>
                    <p>Scotiabank Arena • Toronto</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="assets/images/Olivia.jpg" alt="">
                    <div class="date-badge">OCT 29</div>
                </div>
                <div class="event-info">
                    <h3>Olivia Rodrigo</h3>
                    <p>The Unraveled Tour • Value City Arena</p>
                </div>
            </div>
        </div>
        <button class="carousel-btn right" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), 280)">→</button>
    </div>

</div>

<script>
function scrollCarousel(carousel, offset) {
    carousel.scrollBy({ left: offset, behavior: 'smooth' });
}
</script>
