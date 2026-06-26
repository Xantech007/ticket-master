<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid #eaeaea;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* LEFT SIDE */
.nav-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Logo */
.nav-left img {
    height: 38px;
}

/* Earnings UI */
.nav-earnings {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Balance */
.balance {
    background: #eaf6ff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    color: #0077aa;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Claim Button */
.claim-btn {
    background: #00aaff;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    display: none; /* hidden by default */
}

.claim-btn.active {
    display: inline-block;
}

.claim-btn:hover {
    background: #0088cc;
}

/* RIGHT SIDE */
.nav-right {
    display: flex;
    align-items: center;
    gap: 18px;
}

.nav-right a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Hamburger */
.menu-toggle {
    display: none;
    font-size: 24px;
    cursor: pointer;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {

    .navbar {
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .nav-left {
        width: 100%;
        justify-content: center;
        flex-direction: column;
        gap: 8px;
    }

    .nav-earnings {
        justify-content: center;
        flex-wrap: wrap;
    }

    .menu-toggle {
        display: block;
        position: absolute;
        right: 20px;
        top: 18px;
    }

    .nav-right {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        flex-direction: column;
        padding: 20px;
        gap: 16px;
    }

    .nav-right.show {
        display: flex;
    }

    .nav-right a {
        justify-content: center;
    }
}
</style>

<div class="navbar">

    <!-- LEFT: Logo + Earnings -->
    <div class="nav-left">

        <a href="/index.php">
            <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars($site_name ?? 'PlayEarn'); ?>">
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="nav-earnings">

            <!-- Balance -->
            <div class="balance">
                <i class="fa-solid fa-wallet"></i>
                <span id="navBalance">
                    <?php
                    $user_balance = $_SESSION['balance'] ?? 0.00;

                    if (isset($_SESSION['user_id'])) {
                        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $user_balance = $row['balance'];
                            $_SESSION['balance'] = $user_balance;
                        }
                    }

                    echo ($currency ?? '$') . number_format($user_balance, 4);
                    ?>
                </span>
            </div>

            <!-- Claim Button -->
            <button id="claimBtn" onclick="claimEarnings()" class="claim-btn">
                💰 Claim
            </button>

        </div>
        <?php endif; ?>

    </div>

    <!-- Hamburger -->
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
    </div>

    <!-- RIGHT MENU -->
    <div class="nav-right" id="navMenu">

        <?php if ($current_page !== 'index.php'): ?>
            <a href="/index.php"><i class="fa-solid fa-house"></i> Home</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="/dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="/profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

        <?php else: ?>

            <a href="/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <a href="/register.php"><i class="fa-solid fa-user-plus"></i> Register</a>

        <?php endif; ?>

    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("show");
}

// CLAIM SYSTEM
function claimEarnings() {
    fetch('/claim.php')
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("Claimed $" + parseFloat(data.amount).toFixed(4));

            const el = document.getElementById('navBalance');
            let current = parseFloat(el.innerText.replace(/[^0-9.]/g, ''));
            el.innerText = "$" + (current + parseFloat(data.amount)).toFixed(4);

            // hide button after claim
            document.getElementById('claimBtn').classList.remove('active');
        } else {
            alert("No earnings to claim");
        }
    });
}

// SHOW CLAIM BUTTON ONLY WHEN PLAYING
// You already set sessionId when game starts → we reuse that idea
function setPlayingState(isPlaying) {
    const btn = document.getElementById('claimBtn');
    if (!btn) return;

    if (isPlaying) {
        btn.classList.add('active');
        localStorage.setItem('isPlaying', '1');
    } else {
        btn.classList.remove('active');
        localStorage.removeItem('isPlaying');
    }
}

// restore state on refresh
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('isPlaying')) {
        setPlayingState(true);
    }
});

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById("navMenu");
    const toggle = document.querySelector(".menu-toggle");
    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
        menu.classList.remove("show");
    }
});
</script>
