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
    --tm-red: #e4002b;
    --tm-dark: #121212;
}

/* SMALLER HERO BANNER */
.hero {
    height: 380px;
    background: linear-gradient(rgba(18,18,18,0.65), rgba(18,18,18,0.75)), 
                url('https://picsum.photos/id/1015/2000/1200') center/cover no-repeat;
    border-radius: 0px;
    display: flex;
    align-items: center;
    color: white;
    position: relative;
    margin-bottom: 50px;
}
.hero-content {
    max-width: 580px;
    padding-left: 0px;
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
.btn-find {
    background: var(--tm-red);
    color: white;
    padding: 16px 40px;
    font-size: 18px;
    font-weight: 700;
    border-radius: 50px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}
.btn-find:hover {
    background: #c40022;
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
    color: var(--tm-red);
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: 700;
    text-align: center;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.price {
    color: var(--tm-red);
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
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}
.btn-primary {
    background: var(--tm-red);
    color: white;
}
.btn-outline {
    border: 2px solid var(--tm-red);
    color: var(--tm-red);
}
.btn-primary:hover { background: #c40022; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .hero { height: 320px; }
    .hero h1 { font-size: 34px; }
    .section-title { font-size: 24px; margin: 35px 0 18px; }
    .carousel-btn { display: none; }
}
</style>

<div class="container">


<!-- 1. HERO BANNER - CORRECTED PATH -->
<div class="hero" style="background: linear-gradient(rgba(18,18,18,0.65), rgba(18,18,18,0.75)), url('assets/images/hero-main.jpg') center/cover no-repeat;">
    <div class="hero-content">
        <h1>Live Music. Live Moments.</h1>
        <p>Buy verified tickets to the hottest concerts, tours &amp; festivals.</p>
        <a href="#" class="btn-find">Find Tickets</a>
    </div>
</div>


    <!-- 2. UPCOMING EVENTS - 4 Grid -->
    <h2 class="section-title"><i class="fa-solid fa-calendar icon-red"></i> Upcoming Events</h2>
    <div class="grid-4">
        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/bts.jpg" alt="Taylor Swift">
                <div class="date-badge">AUG<br><strong>01-02</strong></div>
            </div>
            <div class="event-info">
                <h3>BTS - The ARIRANG WORLD TOUR</h3>
                <p>MetLife Stadium • East Rutherford, NJ</p>
                <p>Sat, AUG 01 • 8:00 PM</p>
                <p>Sun, AUG 02 • 8:00 PM</p>
                <p class="price">From $150</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/straykids.jpg" alt="Drake">
                <div class="date-badge">JUL<br><strong>25-29</strong></div>
            </div>
            <div class="event-info">
                <h3>Stray kids - RUN IT (Part 1) World Tour</h3>
                <p>KSPO Dome • South Korea, ON</p>
                <p>Sat, Jul 25 • 6:00 PM</p>
                <p>Sun, Jul 26 • 5:00 PM</p>
                <p>Wed, Jul 29 • 6:00 PM</p>
                <p>Sat, AUG 01 • 5:00 PM</p>
                <p class="price">From $75</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Bad-Bunny.jpg" alt="Bad Bunny">
                <div class="date-badge">JUL<br><strong>10-11</strong></div>
            </div>
            <div class="event-info">
                <h3>Bad Bunny - DeBÍ TiRAR MáS FOToS World Tour</h3>
                <p>Strawberry Arena • Stocholm, SW</p>
                <p>Fri, JUL 10 • 7:00 PM</p>
                <p>Sat, JUL 11 • 7:00 PM</p>
                <p class="price">From $40</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/My-Chemical-Romance.jpg" alt="Billie Eilish">
                <div class="date-badge">JUL-AUG<br><strong>08-30</strong></div>
            </div>
            <div class="event-info">
                <h3>My Chemical Romance - Long Live The Black Parade Tour</h3>
                <p>Wembley Stadium • London, UK</p>
                <p>Wed, JUL 8 • 5:00 PM</p>
                <p>Fri, JUL 10 • 5:00 PM</p>
                <p class="price">From $50</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>
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
                    <p class="price">From $250</p>
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
                    <p class="price">From $55</p>
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
                    <p class="price">From $40</p>
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
                    <p class="price">From $95</p>
                </div>
            </div>
        </div>
        <button class="carousel-btn right" onclick="scrollCarousel(this.parentElement.querySelector('.carousel'), 280)">→</button>
    </div>

    <!-- 4. SPONSORED PRESALES & OFFERS - 3 Events -->
    <h2 class="section-title"><i class="fa-solid fa-star icon-red"></i> Sponsored Presales &amp; Offers</h2>
    <div class="grid-4" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Gracie-Abrams.jpg" alt="Sabrina Carpenter">
                <div class="date-badge">DEC<br><strong>02</strong></div>
            </div>
            <div class="event-info">
                <h3>Gracie Abrams - The Look At My Life Tour</h3>
                <p>Ball Arena • DENVER, CO</p>
                <p>Wed, Dec 02 • 6:30 PM</p>
                <p class="price">Presale from $90</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Presale Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/Weeknd.jpg" alt="The Weeknd">
                <div class="date-badge">AUG<br><strong>08</strong></div>
            </div>
            <div class="event-info">
                <h3>The Weeknd - After Hours Til Dawn</h3>
                <p>STADE DE FRANCE • St Denis, 93, France</p>
                <p>Wed, JUL 8 • 7:00 PM</p>
                <p class="price">From $69 • Limited Offer</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="assets/images/PostMalone.jpg" alt="Post Malone">
                <div class="date-badge">AUG<br><strong>20</strong></div>
            </div>
            <div class="event-info">
                <h3>Post Malone -  The BIG ASS Stadium Tour</h3>
                <p>Raymond James Stadium • Tampa, FL</p>
                <p>Wed, Jul 08 • 7:00 PM</p>
                <p class="price">Early Bird from $45</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
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
                    <h3>Chappell Roan</h3>
                    <p>Daisy Chain Fields • Irvine, CA</p>
                    <p class="price">From $310</p>
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
                    <p class="price">From $50</p>
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
                    <p class="price">From $65</p>
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
                    <p class="price">From $180</p>
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
