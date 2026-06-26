<style>
/* Mobile-First Approach */
* {
    box-sizing: border-box;
}
.container {
    max-width: 1200px;
    margin: auto;
    padding: 15px;
}

/* TICKETMASTER-INSPIRED COLORS */
:root {
    --tm-red: #e4002b;
    --tm-dark: #121212;
    --tm-blue: #024ddf;
}

/* HERO SECTION - Ticketmaster style */
.hero {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    margin-top: 40px;
    padding: 40px 0;
    background: linear-gradient(rgba(18,18,18,0.85), rgba(18,18,18,0.75)), url('https://picsum.photos/id/1015/2000/1200') center/cover no-repeat; /* Replace with concert image */
    color: white;
    border-radius: 16px;
    position: relative;
}
.hero-text {
    flex: 1;
    min-width: 280px;
    padding-left: 30px;
}
.hero h1 {
    font-size: 42px;
    line-height: 1.1;
    margin-bottom: 16px;
    font-weight: 900;
}
.hero p {
    color: #ddd;
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 25px;
    max-width: 500px;
}

/* Search bar like Ticketmaster */
.hero-search {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}
.hero-search input {
    flex: 1;
    padding: 16px 20px;
    border: none;
    border-radius: 50px;
    font-size: 17px;
    min-width: 280px;
}
.hero-search .btn-primary {
    padding: 16px 32px;
    border-radius: 50px;
}

/* SECTION STYLING */
.section {
    margin-top: 70px;
}
.section h2 {
    font-size: 32px;
    margin-bottom: 24px;
    font-weight: 700;
    color: var(--tm-dark);
}

/* ICON COLORS (updated for Ticketmaster vibe) */
.icon-red { color: var(--tm-red); }
.icon-blue { color: var(--tm-blue); }
.icon-green { color: #22c55e; }
.icon-purple { color: #8b5cf6; }

/* CARDS & STEPS */
.cards, .steps, .reviews {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}
.card, .step, .review {
    background: #fff;
    padding: 28px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #eee;
}
.card:hover, .step:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 35px rgba(228, 0, 43, 0.12);
}
.card i, .step i {
    font-size: 42px;
    margin-bottom: 18px;
    display: block;
}
.card h3, .step h3 {
    margin: 8px 0 14px;
    font-size: 22px;
    font-weight: 700;
}

/* EVENT CARDS - More Ticketmaster-like */
.event-card {
    position: relative;
    overflow: hidden;
}
.event-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 12px 12px 0 0;
}
.event-card .event-info {
    padding: 20px;
}
.event-card .date {
    position: absolute;
    top: 15px;
    left: 15px;
    background: white;
    color: var(--tm-red);
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* STATS */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    background: #121212;
    color: white;
    padding: 50px 30px;
    border-radius: 16px;
    margin-top: 30px;
}
.stat-item {
    text-align: center;
}
.stat-item h3 {
    font-size: 38px;
    margin: 0;
    color: var(--tm-red);
}
.stat-item p {
    color: #bbb;
    font-size: 16px;
    margin-top: 8px;
}

/* CTA */
.cta {
    text-align: center;
    padding: 60px 30px;
    background: linear-gradient(135deg, var(--tm-red), #c40022);
    color: white;
    border-radius: 16px;
    margin-top: 70px;
}
.cta h2 {
    font-size: 36px;
    margin-bottom: 16px;
}

/* BUTTONS */
.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 50px;
    font-size: 16.5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-weight: 600;
}
.btn-primary {
    background: var(--tm-red);
    color: white;
}
.btn-primary:hover { 
    background: #c40022; 
    transform: scale(1.03);
}

/* Mobile Optimizations */
@media (max-width: 768px) {
    .hero h1 {
        font-size: 34px;
    }
    .hero {
        padding: 30px 20px;
        background-position: center;
    }
    .section h2 {
        font-size: 28px;
    }
    .cta h2 {
        font-size: 30px;
    }
    .cards, .steps {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<div class="container">
    <!-- HERO SECTION - Ticketmaster Style -->
    <div class="hero">
        <div class="hero-text">
            <h1>Find & Buy Concert Tickets</h1>
            <p>
                Discover thousands of events. Buy verified tickets for your favorite artists, tours, and festivals. 
                Secure seats, no surprises.
            </p>
            
            <!-- Ticketmaster-like search -->
            <div class="hero-search">
                <input type="text" placeholder="Search artists, teams, or venues...">
                <a href="#" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Search Events
                </a>
            </div>
            
            <br><br>
            <a href="#" class="btn btn-primary" style="background:#fff; color:#121212; font-weight:700;">
                Browse All Concerts
            </a>
        </div>
        <div class="hero-img" style="flex:1; min-width:300px;">
            <!-- Hero image handled in background -->
        </div>
    </div>

    <!-- HOW IT WORKS -->
    <div class="section">
        <h2><i class="fa-solid fa-ticket icon-red"></i> How It Works - 3 Simple Steps</h2>
        <div class="steps">
            <div class="step">
                <i class="fa-solid fa-search icon-blue"></i>
                <h3>1. Discover Events</h3>
                <p>Browse upcoming concerts, festivals, and tours near you or worldwide. Filter by artist, date, or venue.</p>
            </div>
            <div class="step">
                <i class="fa-solid fa-seat icon-red"></i>
                <h3>2. Choose Your Seats</h3>
                <p>Select from interactive seating charts with real-time availability and pricing.</p>
            </div>
            <div class="step">
                <i class="fa-solid fa-credit-card icon-green"></i>
                <h3>3. Buy Securely & Enjoy</h3>
                <p>Complete your purchase with confidence. Get tickets instantly via mobile or email.</p>
            </div>
        </div>
    </div>

    <!-- WHY CHOOSE US -->
    <div class="section">
        <h2><i class="fa-solid fa-shield-halved icon-blue"></i> Why Fans Trust Us</h2>
        <div class="cards">
            <div class="card">
                <i class="fa-solid fa-check-circle icon-green"></i>
                <h3>Verified Tickets</h3>
                <p>100% guaranteed authentic tickets. No fakes, no stress.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-clock icon-blue"></i>
                <h3>Instant Delivery</h3>
                <p>Receive tickets immediately after purchase — mobile tickets or print-at-home.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-lock icon-green"></i>
                <h3>Secure Checkout</h3>
                <p>Bank-level encryption and multiple payment options including Apple Pay.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-headset icon-blue"></i>
                <h3>24/7 Support</h3>
                <p>Dedicated fan support for any questions before or after your event.</p>
            </div>
        </div>
    </div>

    <!-- FEATURED CONCERTS / EVENTS -->
    <div class="section">
        <h2><i class="fa-solid fa-fire icon-red"></i> Featured Concerts</h2>
        <div class="cards">
            <div class="card event-card">
                <img src="https://picsum.photos/id/1015/600/300" alt="Taylor Swift">
                <div class="event-info">
                    <div class="date">
                        <strong>JUL</strong><br>15
                    </div>
                    <h3>Taylor Swift | The Eras Tour</h3>
                    <p>MetLife Stadium • East Rutherford, NJ</p>
                    <br>
                    <a href="#" class="btn btn-primary">Get Tickets</a>
                </div>
            </div>
            
            <div class="card event-card">
                <img src="https://picsum.photos/id/201/600/300" alt="Drake">
                <div class="event-info">
                    <div class="date">
                        <strong>JUL</strong><br>22
                    </div>
                    <h3>Drake • It's All A Blur</h3>
                    <p>Scotiabank Arena • Toronto, ON</p>
                    <br>
                    <a href="#" class="btn btn-primary">Get Tickets</a>
                </div>
            </div>
            
            <div class="card event-card">
                <img src="https://picsum.photos/id/870/600/300" alt="Bad Bunny">
                <div class="event-info">
                    <div class="date">
                        <strong>AUG</strong><br>05
                    </div>
                    <h3>Bad Bunny • Most Wanted Tour</h3>
                    <p>SoFi Stadium • Los Angeles, CA</p>
                    <br>
                    <a href="#" class="btn btn-primary">Get Tickets</a>
                </div>
            </div>
        </div>
    </div>

    <!-- PLATFORM STATS -->
    <div class="section">
        <h2><i class="fa-solid fa-chart-line icon-blue"></i> By The Numbers</h2>
        <div class="stats">
            <div class="stat-item">
                <h3>150M+</h3>
                <p>Tickets Sold Yearly</p>
            </div>
            <div class="stat-item">
                <h3>50,000+</h3>
                <p>Events Live</p>
            </div>
            <div class="stat-item">
                <h3>10,000+</h3>
                <p>Artists & Teams</p>
            </div>
            <div class="stat-item">
                <h3>99.9%</h3>
                <p>Successful Deliveries</p>
            </div>
        </div>
    </div>

    <!-- FINAL CTA -->
    <div class="cta">
        <h2>Ready for your next unforgettable night?</h2>
        <p>Millions of fans can't be wrong. Start searching for concerts now.</p>
        <a href="#" class="btn btn-primary" style="background:white; color:var(--tm-red); font-size:18px; padding:18px 40px;">
            Browse All Events →
        </a>
    </div>
</div>
