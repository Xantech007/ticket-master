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
                <img src="https://picsum.photos/id/1015/600/400" alt="Taylor Swift">
                <div class="date-badge">JUL<br><strong>15</strong></div>
            </div>
            <div class="event-info">
                <h3>Taylor Swift - The Eras Tour</h3>
                <p>MetLife Stadium • East Rutherford, NJ</p>
                <p>Sat, Jul 15 • 7:00 PM</p>
                <p class="price">From $89</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="https://picsum.photos/id/201/600/400" alt="Drake">
                <div class="date-badge">JUL<br><strong>22</strong></div>
            </div>
            <div class="event-info">
                <h3>Drake - It's All A Blur Tour</h3>
                <p>Scotiabank Arena • Toronto, ON</p>
                <p>Wed, Jul 22 • 8:00 PM</p>
                <p class="price">From $65</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="https://picsum.photos/id/870/600/400" alt="Bad Bunny">
                <div class="date-badge">AUG<br><strong>05</strong></div>
            </div>
            <div class="event-info">
                <h3>Bad Bunny - Most Wanted Tour</h3>
                <p>SoFi Stadium • Los Angeles, CA</p>
                <p>Wed, Aug 5 • 8:30 PM</p>
                <p class="price">From $75</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="https://picsum.photos/id/133/600/400" alt="Billie Eilish">
                <div class="date-badge">AUG<br><strong>12</strong></div>
            </div>
            <div class="event-info">
                <h3>Billie Eilish - Hit Me Hard Tour</h3>
                <p>United Center • Chicago, IL</p>
                <p>Wed, Aug 12 • 7:30 PM</p>
                <p class="price">From $55</p>
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
                    <img src="https://picsum.photos/id/1015/600/400" alt="">
                    <div class="date-badge">JUL 15</div>
                </div>
                <div class="event-info">
                    <h3>Taylor Swift</h3>
                    <p>The Eras Tour • MetLife Stadium</p>
                    <p class="price">From $89</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="https://picsum.photos/id/201/600/400" alt="">
                    <div class="date-badge">JUL 22</div>
                </div>
                <div class="event-info">
                    <h3>Drake</h3>
                    <p>It's All A Blur • Scotiabank Arena</p>
                    <p class="price">From $65</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="https://picsum.photos/id/870/600/400" alt="">
                    <div class="date-badge">AUG 05</div>
                </div>
                <div class="event-info">
                    <h3>Bad Bunny</h3>
                    <p>Most Wanted Tour • SoFi Stadium</p>
                    <p class="price">From $75</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="https://picsum.photos/id/133/600/400" alt="">
                    <div class="date-badge">AUG 12</div>
                </div>
                <div class="event-info">
                    <h3>Billie Eilish</h3>
                    <p>Hit Me Hard • United Center</p>
                    <p class="price">From $55</p>
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
                <img src="https://picsum.photos/id/201/600/400" alt="Sabrina Carpenter">
                <div class="date-badge">JUL<br><strong>18</strong></div>
            </div>
            <div class="event-info">
                <h3>Sabrina Carpenter - Short n' Sweet Tour</h3>
                <p>Madison Square Garden • New York, NY</p>
                <p>Fri, Jul 18 • 7:30 PM</p>
                <p class="price">Presale from $49</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Presale Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="https://picsum.photos/id/870/600/400" alt="The Weeknd">
                <div class="date-badge">AUG<br><strong>08</strong></div>
            </div>
            <div class="event-info">
                <h3>The Weeknd - After Hours Til Dawn</h3>
                <p>Levi's Stadium • Santa Clara, CA</p>
                <p>Fri, Aug 8 • 8:00 PM</p>
                <p class="price">From $69 • Limited Offer</p>
                <a href="#" class="btn btn-primary" style="margin-top:12px; width:100%; text-align:center;">Get Tickets</a>
            </div>
        </div>

        <div class="event-card">
            <div style="position:relative;">
                <img src="https://picsum.photos/id/133/600/400" alt="Post Malone">
                <div class="date-badge">AUG<br><strong>20</strong></div>
            </div>
            <div class="event-info">
                <h3>Post Malone - F-1 Trillion Tour</h3>
                <p>Allegiant Stadium • Las Vegas, NV</p>
                <p>Wed, Aug 20 • 7:00 PM</p>
                <p class="price">Early Bird from $59</p>
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
                    <img src="https://picsum.photos/id/1015/600/400" alt="">
                    <div class="date-badge">JUL 15</div>
                </div>
                <div class="event-info">
                    <h3>Taylor Swift</h3>
                    <p>MetLife Stadium • East Rutherford</p>
                    <p class="price">From $89</p>
                </div>
            </div>
            <div class="event-card">
                <div style="position:relative;">
                    <img src="https://picsum.photos/id/60/600/400" alt="">
                    <div class="date-badge">JUL 25</div>
                </div>
                <div class="event-info">
                    <h3>Morgan Wallen</h3>
                    <p>Yankee Stadium • New York</p>
                    <p class="price">From $45</p>
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
                    <img src="https://picsum.photos/id/870/600/400" alt="">
                    <div class="date-badge">AUG 10</div>
                </div>
                <div class="event-info">
                    <h3>Olivia Rodrigo</h3>
                    <p>TD Garden • Boston</p>
                    <p class="price">From $55</p>
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
