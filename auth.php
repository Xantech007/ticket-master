<?php
require_once "inc/countries.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Authentication</title>

    <link rel="icon" href="assets/favicon.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: Inter, sans-serif; }

        .brand { color:#024DDF; }
        .bg-brand { background:#024DDF; }

        .glass {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(14px);
        }

        .tab-active {
            background: rgba(2,77,223,0.08);
            color:#024DDF;
        }

        .input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(2,77,223,0.15);
            border-color:#024DDF;
        }

        .bar {
            height:6px;
            border-radius:999px;
            transition:0.3s;
        }
    </style>
</head>

<body class="bg-gray-50">

<!-- background -->
<div class="fixed inset-0 -z-10">
    <div class="absolute top-[-200px] left-[-200px] w-[500px] h-[500px] bg-blue-100 blur-3xl opacity-40"></div>
    <div class="absolute bottom-[-200px] right-[-200px] w-[500px] h-[500px] bg-blue-200 blur-3xl opacity-30"></div>
</div>

<div class="min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md glass border border-gray-200 rounded-3xl shadow-2xl overflow-hidden">

<!-- HEADER -->
<div class="p-6 text-center border-b bg-white/80">
    <img src="assets/logo.png" class="h-10 mx-auto mb-3">

    <h1 class="text-2xl font-black">Welcome</h1>
    <p class="text-sm text-gray-500">Sign in or create account</p>
</div>

<!-- TABS -->
<div class="flex border-b bg-white">
    <button id="loginTab" onclick="showLogin()" class="w-1/2 py-3 font-bold tab-active">
        Sign In
    </button>
    <button id="registerTab" onclick="showRegister()" class="w-1/2 py-3 font-bold text-gray-500">
        Create Account
    </button>
</div>

<!-- LOGIN -->
<form id="loginForm" class="p-6 space-y-4">

    <input class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="email" placeholder="Email">

    <input class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="password" placeholder="Password">

    <button id="loginBtn"
        class="w-full bg-brand text-white font-black py-3 rounded-xl hover:bg-blue-800 transition">
        Sign In
    </button>

</form>

<!-- REGISTER -->
<form id="registerForm" class="p-6 space-y-4 hidden">

    <input class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Full Name">
    <input class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Email">

    <select id="countrySelect" class="w-full border rounded-xl px-4 py-3 text-sm input">
        <option>Select Country</option>
        <?php foreach ($countries as $c): ?>
            <option><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="codeSelect" class="w-full border rounded-xl px-4 py-3 text-sm input">
        <option>Code</option>
    </select>

    <input class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Phone">

    <!-- PASSWORD -->
    <input id="password"
        class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="password" placeholder="Password"
        oninput="checkStrength(this.value)">

    <!-- STRENGTH BAR -->
    <div class="space-y-1">
        <div class="bg-gray-200 bar w-full overflow-hidden">
            <div id="strengthBar" class="bar w-0 bg-red-500"></div>
        </div>
        <p id="strengthText" class="text-xs text-gray-500">Password strength: -</p>
    </div>

    <input class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="password" placeholder="Confirm Password">

    <button id="registerBtn"
        class="w-full bg-brand text-white font-black py-3 rounded-xl hover:bg-blue-800 transition">
        Create Account
    </button>

</form>

</div>
</div>

<script>

/* -----------------------
   TAB SWITCH + BUTTON TEXT CONTROL
------------------------*/
function showLogin() {
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("registerForm").classList.add("hidden");

    document.getElementById("loginTab").classList.add("tab-active");
    document.getElementById("registerTab").classList.remove("tab-active");

    document.getElementById("loginBtn").style.display = "block";
    document.getElementById("registerBtn").style.display = "none";
}

function showRegister() {
    document.getElementById("registerForm").classList.remove("hidden");
    document.getElementById("loginForm").classList.add("hidden");

    document.getElementById("registerTab").classList.add("tab-active");
    document.getElementById("loginTab").classList.remove("tab-active");

    document.getElementById("loginBtn").style.display = "none";
    document.getElementById("registerBtn").style.display = "block";
}

/* -----------------------
   PASSWORD STRENGTH ENGINE
------------------------*/
function checkStrength(password) {

    let score = 0;

    if (password.length >= 6) score++;
    if (password.length >= 10) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const bar = document.getElementById("strengthBar");
    const text = document.getElementById("strengthText");

    let width = score * 20;

    bar.style.width = width + "%";

    if (score <= 1) {
        bar.className = "bar bg-red-500";
        text.innerText = "Weak password";
    }
    else if (score <= 3) {
        bar.className = "bar bg-yellow-500";
        text.innerText = "Medium strength";
    }
    else {
        bar.className = "bar bg-green-500";
        text.innerText = "Strong password";
    }
}

/* -----------------------
   COUNTRY CODES
------------------------*/
const countryCodes = {
    "Nigeria":"+234",
    "Ghana":"+233",
    "Kenya":"+254",
    "South Africa":"+27",
    "United States":"+1",
    "United Kingdom":"+44",
    "India":"+91",
    "Germany":"+49"
};

document.getElementById("countrySelect").addEventListener("change", function () {
    document.getElementById("codeSelect").innerHTML =
        `<option>${countryCodes[this.value] || ""}</option>`;
});

/* -----------------------
   GEO DETECT COUNTRY
------------------------*/
function detectCountry() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(async (pos) => {
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`
            );

            const data = await res.json();

            if (data?.address?.country) {
                const c = document.getElementById("countrySelect");
                c.value = data.address.country;
                c.dispatchEvent(new Event("change"));
            }
        } catch (e) {}
    });
}

detectCountry();

/* default UI state */
showLogin();

</script>

</body>
</html>
