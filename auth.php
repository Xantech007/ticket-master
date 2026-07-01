<?php
require_once "inc/countries.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Authentication</title>

    <!-- FAVICON -->
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .brand-blue {
            color: #024DDF;
        }

        .bg-brand {
            background: #024DDF;
        }

        .ring-brand {
            --tw-ring-color: #024DDF;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
        }

        .tab-active {
            background: rgba(2, 77, 223, 0.08);
            color: #024DDF;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

<!-- Background accent -->
<div class="fixed inset-0 -z-10">
    <div class="absolute top-[-200px] left-[-200px] w-[500px] h-[500px] bg-blue-100 rounded-full blur-3xl opacity-40"></div>
    <div class="absolute bottom-[-200px] right-[-200px] w-[500px] h-[500px] bg-blue-200 rounded-full blur-3xl opacity-30"></div>
</div>

<div class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md glass border border-gray-200 rounded-3xl shadow-2xl overflow-hidden">

        <!-- HEADER -->
        <div class="p-6 text-center border-b bg-white/70">
            <img src="assets/logo.png" class="h-10 mx-auto mb-3" alt="Logo">

            <h1 class="text-2xl font-black text-gray-900">Welcome Back</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in or create your account to continue</p>
        </div>

        <!-- TABS -->
        <div class="flex border-b bg-white">
            <button onclick="showLogin()"
                id="loginTab"
                class="w-1/2 py-3 text-sm font-bold tab-active transition-all">
                Sign In
            </button>

            <button onclick="showRegister()"
                id="registerTab"
                class="w-1/2 py-3 text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                Create Account
            </button>
        </div>

        <!-- LOGIN -->
        <form id="loginForm" class="p-6 space-y-4">

            <input type="email" placeholder="Email address"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 ring-brand">

            <input type="password" placeholder="Password"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 ring-brand">

            <button class="w-full bg-brand hover:bg-blue-800 text-white font-black py-3 rounded-xl shadow-md transition">
                Sign In
            </button>

            <p class="text-xs text-center text-gray-400">
                Forgot password? Contact support
            </p>
        </form>

        <!-- REGISTER -->
        <form id="registerForm" class="p-6 space-y-4 hidden">

            <input type="text" placeholder="Full Name"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">

            <input type="email" placeholder="Email address"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">

            <!-- COUNTRY -->
            <select id="countrySelect"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">
                <option value="">Select Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>">
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- COUNTRY CODE -->
            <select id="codeSelect"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">
                <option value="">Country Code</option>
            </select>

            <input type="tel" placeholder="Phone number"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">

            <input type="password" placeholder="Password"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">

            <input type="password" placeholder="Confirm Password"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 ring-brand">

            <button class="w-full bg-brand hover:bg-blue-800 text-white font-black py-3 rounded-xl shadow-md transition">
                Create Account
            </button>
        </form>

    </div>
</div>

<script>
/* -----------------------------
   TAB SWITCH
------------------------------*/
function showLogin() {
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("registerForm").classList.add("hidden");

    document.getElementById("loginTab").classList.add("tab-active");
    document.getElementById("registerTab").classList.remove("tab-active");
    document.getElementById("registerTab").classList.add("text-gray-500");
}

function showRegister() {
    document.getElementById("registerForm").classList.remove("hidden");
    document.getElementById("loginForm").classList.add("hidden");

    document.getElementById("registerTab").classList.add("tab-active");
    document.getElementById("loginTab").classList.remove("tab-active");
    document.getElementById("loginTab").classList.add("text-gray-500");
}

/* -----------------------------
   COUNTRY CODE MAP
------------------------------*/
const countryCodes = {
    "Nigeria": "+234",
    "Ghana": "+233",
    "Kenya": "+254",
    "South Africa": "+27",
    "United States": "+1",
    "Canada": "+1",
    "United Kingdom": "+44",
    "India": "+91",
    "Germany": "+49",
    "France": "+33",
    "Australia": "+61"
};

document.getElementById("countrySelect").addEventListener("change", function () {
    const code = countryCodes[this.value] || "";
    document.getElementById("codeSelect").innerHTML =
        `<option value="${code}">${code}</option>`;
});

/* -----------------------------
   GEOLOCATION AUTO DETECT
------------------------------*/
function detectCountry() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(async function (pos) {
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`
            );

            const data = await res.json();
            const country = data?.address?.country;

            if (country) {
                const select = document.getElementById("countrySelect");
                select.value = country;
                select.dispatchEvent(new Event("change"));
            }
        } catch (e) {
            console.log("Geo detection failed");
        }
    });
}

detectCountry();
</script>

</body>
</html>
