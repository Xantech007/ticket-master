<?php
session_start();
require_once "inc/countries.php";
require_once "inc/country-codes.php";
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

<!-- ERROR MESSAGE -->
<?php if (!empty($_SESSION['auth_error'])): ?>
    <div class="bg-red-50 text-red-600 text-sm font-bold p-3 rounded-lg mb-4 max-w-md mx-auto">
        <?= $_SESSION['auth_error']; ?>
    </div>
    <?php unset($_SESSION['auth_error']); ?>
<?php endif; ?>

<!-- BACKGROUND -->
<div class="fixed inset-0 -z-10">
    <div class="absolute top-[-200px] left-[-200px] w-[500px] h-[500px] bg-blue-100 blur-3xl opacity-40"></div>
    <div class="absolute bottom-[-200px] right-[-200px] w-[500px] h-[500px] bg-blue-200 blur-3xl opacity-30"></div>
</div>

<div class="min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md glass border border-gray-200 rounded-3xl shadow-2xl overflow-hidden">

<!-- HEADER -->
<div class="p-6 text-center border-b bg-white/80">
    <img src="assets/auth-logo.png" class="h-10 mx-auto mb-3">
    <h1 class="text-2xl font-black">Authentication</h1>
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
<form id="loginForm" class="p-6 space-y-4" method="POST" action="login.php">

    <input name="email" class="w-full border rounded-xl px-4 py-3 text-sm input" type="email" placeholder="Email">

    <input name="password" class="w-full border rounded-xl px-4 py-3 text-sm input" type="password" placeholder="Password">

    <button class="w-full bg-brand text-white font-black py-3 rounded-xl hover:bg-blue-800 transition">
        Sign In
    </button>

</form>

<!-- REGISTER -->
<form id="registerForm" class="p-6 space-y-4 hidden" method="POST" action="register.php">

    <input name="full_name" class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Full Name">

    <input name="email" class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Email">

    <select name="country" id="countrySelect" class="w-full border rounded-xl px-4 py-3 text-sm input">
        <option>Select Country</option>
        <?php foreach ($countries as $c): ?>
            <option><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="country_code" id="codeSelect" class="w-full border rounded-xl px-4 py-3 text-sm input">
        <option>Code</option>
    </select>

    <input name="phone" class="w-full border rounded-xl px-4 py-3 text-sm input" placeholder="Phone">

    <input name="password" id="password"
        class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="password" placeholder="Password"
        oninput="checkStrength(this.value)">

    <div class="space-y-1">
        <div class="bg-gray-200 bar w-full overflow-hidden">
            <div id="strengthBar" class="bar w-0 bg-red-500"></div>
        </div>
        <p id="strengthText" class="text-xs text-gray-500">Password strength: -</p>
    </div>

    <input name="confirm_password" class="w-full border rounded-xl px-4 py-3 text-sm input"
        type="password" placeholder="Confirm Password">

    <button class="w-full bg-brand text-white font-black py-3 rounded-xl hover:bg-blue-800 transition">
        Create Account
    </button>

</form>

</div>
</div>

<script>

/* -----------------------
   TAB SWITCH
------------------------*/
function showLogin() {
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("registerForm").classList.add("hidden");

    document.getElementById("loginTab").classList.add("tab-active");
    document.getElementById("registerTab").classList.remove("tab-active");
}

function showRegister() {
    document.getElementById("registerForm").classList.remove("hidden");
    document.getElementById("loginForm").classList.add("hidden");

    document.getElementById("registerTab").classList.add("tab-active");
    document.getElementById("loginTab").classList.remove("tab-active");
}

/* -----------------------
   PASSWORD STRENGTH
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

    bar.style.width = (score * 20) + "%";

    if (score <= 1) {
        bar.className = "bar bg-red-500";
        text.innerText = "Weak password";
    } else if (score <= 3) {
        bar.className = "bar bg-yellow-500";
        text.innerText = "Medium strength";
    } else {
        bar.className = "bar bg-green-500";
        text.innerText = "Strong password";
    }
}


document.getElementById("countrySelect").addEventListener("change", function () {
    document.getElementById("codeSelect").innerHTML =
        `<option>${countryCodes[this.value] || ""}</option>`;
});

</script>

</body>
</html>
