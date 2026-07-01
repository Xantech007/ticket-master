<?php
require_once "inc/countries.php";
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.php"; ?>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="p-6 text-center border-b bg-white">
            <h1 class="text-2xl font-black text-gray-900">Welcome</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in or create an account</p>
        </div>

        <!-- Toggle Buttons -->
        <div class="flex border-b">
            <button onclick="showLogin()"
                id="loginTab"
                class="w-1/2 py-3 text-sm font-black bg-blue-50 text-[#024DDF]">
                Sign In
            </button>

            <button onclick="showRegister()"
                id="registerTab"
                class="w-1/2 py-3 text-sm font-black text-gray-600 hover:bg-gray-50">
                Create Account
            </button>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="p-6 space-y-4">

            <input type="email" placeholder="Email"
                class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="password" placeholder="Password"
                class="w-full border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button class="w-full bg-[#024DDF] text-white font-black py-3 rounded-lg hover:bg-blue-800">
                Sign In
            </button>

        </form>

        <!-- REGISTER FORM -->
        <form id="registerForm" class="p-6 space-y-4 hidden">

            <input type="text" placeholder="Full Name"
                class="w-full border rounded-lg px-4 py-3 text-sm">

            <input type="email" placeholder="Email"
                class="w-full border rounded-lg px-4 py-3 text-sm">

            <!-- Country -->
            <select id="countrySelect"
                class="w-full border rounded-lg px-4 py-3 text-sm">
                <option value="">Select Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>">
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Country Code -->
            <select id="codeSelect"
                class="w-full border rounded-lg px-4 py-3 text-sm">
                <option value="">Code</option>
            </select>

            <input type="tel" placeholder="Phone Number"
                class="w-full border rounded-lg px-4 py-3 text-sm">

            <input type="password" placeholder="Password"
                class="w-full border rounded-lg px-4 py-3 text-sm">

            <input type="password" placeholder="Confirm Password"
                class="w-full border rounded-lg px-4 py-3 text-sm">

            <button class="w-full bg-[#024DDF] text-white font-black py-3 rounded-lg hover:bg-blue-800">
                Create Account
            </button>

        </form>

    </div>

</div>

<script>
/* -----------------------------
   FORM TOGGLE
------------------------------*/
function showLogin() {
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("registerForm").classList.add("hidden");

    document.getElementById("loginTab").classList.add("bg-blue-50", "text-[#024DDF]");
    document.getElementById("registerTab").classList.remove("bg-blue-50", "text-[#024DDF]");
}

function showRegister() {
    document.getElementById("registerForm").classList.remove("hidden");
    document.getElementById("loginForm").classList.add("hidden");

    document.getElementById("registerTab").classList.add("bg-blue-50", "text-[#024DDF]");
    document.getElementById("loginTab").classList.remove("bg-blue-50", "text-[#024DDF]");
}

/* -----------------------------
   COUNTRY → CODE MAP
   (basic starter mapping)
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

/* -----------------------------
   UPDATE CODE ON COUNTRY SELECT
------------------------------*/
document.getElementById("countrySelect").addEventListener("change", function () {
    const code = countryCodes[this.value] || "";
    const codeSelect = document.getElementById("codeSelect");

    codeSelect.innerHTML = `<option value="${code}">${code}</option>`;
});

/* -----------------------------
   GEOLOCATION COUNTRY DETECT
------------------------------*/
function detectCountry() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(async function (pos) {
        try {
            const lat = pos.coords.latitude;
            const lon = pos.coords.longitude;

            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
            const data = await res.json();

            const country = data.address.country;

            if (country) {
                const select = document.getElementById("countrySelect");
                select.value = country;

                const event = new Event("change");
                select.dispatchEvent(event);
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
