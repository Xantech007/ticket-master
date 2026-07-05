<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Define the Catalog of Major Gift Cards with their custom parameters
$giftcards_catalog = [
    'amazon' => [
        'name' => 'Amazon Gift Card',
        'icon' => 'fab fa-amazon',
        'color' => 'from-amber-500 to-orange-600',
        'text_color' => 'text-orange-500',
        'bg_light' => 'bg-orange-50',
        'border_color' => 'border-orange-200',
        'url' => 'https://www.amazon.com/giftcards',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with an Amazon specific tutorial video ID
        'instructions' => 'Select eGift or Print-at-Home, enter your target balance allocation, input the destination email address, and complete standard credit card authorization steps.'
    ],
    'apple' => [
        'name' => 'Apple Gift Card',
        'icon' => 'fab fa-apple',
        'color' => 'from-slate-700 to-slate-900',
        'text_color' => 'text-slate-800',
        'bg_light' => 'bg-slate-50',
        'border_color' => 'border-slate-200',
        'url' => 'https://www.apple.com/shop/gift-cards',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with an Apple specific tutorial video ID
        'instructions' => 'Choose digital delivery options, pick a design layout preset, select a predefined fund balance, and checkout using Apple Pay or standard visa systems.'
    ],
    'googleplay' => [
        'name' => 'Google Play Gift Card',
        'icon' => 'fab fa-google-play',
        'color' => 'from-emerald-500 to-teal-700',
        'text_color' => 'text-emerald-600',
        'bg_light' => 'bg-emerald-50',
        'border_color' => 'border-emerald-200',
        'url' => 'https://play.google.com/about/giftcards/',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with a Google Play tutorial video ID
        'instructions' => 'Navigate to the official digital distribution supplier, authorize your profile identifier, designate your total face value, and execute local currency clearing routines.'
    ],
    'playstation' => [
        'name' => 'PlayStation Network Card',
        'icon' => 'fab fa-playstation',
        'color' => 'from-blue-600 to-indigo-900',
        'text_color' => 'text-blue-600',
        'bg_light' => 'bg-blue-50',
        'border_color' => 'border-blue-200',
        'url' => 'https://www.playstation.com/en-us/playstation-gift-cards/',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with a PlayStation tutorial video ID
        'instructions' => 'Choose your regional network zone, select the correct wallet fund increments ($10 - $100), add the product block to your cart, and finish instant delivery processing.'
    ],
    'steam' => [
        'name' => 'Steam Wallet Code',
        'icon' => 'fab fa-steam',
        'color' => 'from-slate-800 to-indigo-950',
        'text_color' => 'text-indigo-950',
        'bg_light' => 'bg-slate-100',
        'border_color' => 'border-slate-300',
        'url' => 'https://store.steampowered.com/digitalgiftcards/',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with a Steam tutorial video ID
        'instructions' => 'Log in to your active Steam client framework, select the active friend recipient or personal account allocation, input settlement credentials, and clear the transaction.'
    ],
    'razer' => [
        'name' => 'Razer Gold Pin',
        'icon' => 'fas fa-gem',
        'color' => 'from-green-500 to-emerald-600',
        'text_color' => 'text-green-600',
        'bg_light' => 'bg-green-50',
        'border_color' => 'border-green-200',
        'url' => 'https://gold.razer.com/',
        'youtube_id' => 'dQw4w9WgXcQ', // Replace with a Razer Gold tutorial video ID
        'instructions' => 'Select the Razer Gold PIN direct option, set your payment structural balance gateway matrix, input secure two-factor auth data, and receive the alphanumeric string instantly.'
    ]
];

// 2. Identify the active selected card or default to Amazon if empty/invalid
$selected_key = isset($_GET['type']) ? trim($_GET['type']) : 'amazon';
if (!array_key_exists($selected_key, $giftcards_catalog)) {
    $selected_key = 'amazon';
}

$active_card = $giftcards_catalog[$selected_key];
?>
<!DOCTYPE html>
<html lang="en">

<?php 
if (file_exists("inc/head.php")) { include "inc/head.php"; } else {
    echo '<head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Buy Gift Cards — Step-by-Step Purchase Guide</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
          </head>';
}
?>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    
    <?php if (file_exists("inc/navbar.php")) { include "inc/navbar.php"; } ?> 

    <div id="__next">
        <div class="bg-gradient-to-r <?php echo $active_card['color']; ?> text-white py-12 px-4 md:px-8 shadow-md transition-all duration-300">
            <div class="max-w-6xl mx-auto text-center md:text-left flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest bg-black/30 px-3 py-1 rounded-full">Official Purchase Hub</span>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight mt-3 flex items-center justify-center md:justify-start gap-3">
                        <i class="<?php echo $active_card['icon']; ?>"></i> How to Buy <?php echo htmlspecialchars($active_card['name']); ?>
                    </h1>
                    <p class="text-xs md:text-sm text-white/80 mt-2 max-w-xl leading-relaxed font-medium mx-auto md:mx-0">
                        Follow our verified digital onboarding blueprint down below to safely secure and claim valid credit codes instantly through verified merchant platforms.
                    </p>
                </div>
            </div>
        </div>

        <main class="max-w-6xl mx-auto px-4 md:px-8 py-10 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-3 space-y-3">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2 pl-1"><i class="fas fa-layer-group"></i> Select Card Type</h3>
                <div class="flex lg:flex-col gap-2 overflow-x-auto pb-2 lg:pb-0 scrollbar-none whitespace-nowrap">
                    <?php foreach ($giftcards_catalog as $key => $card): 
                        $is_active = ($key === $selected_key);
                        $btn_classes = $is_active 
                            ? "bg-slate-900 text-white shadow" 
                            : "bg-white text-gray-700 hover:bg-gray-100 border border-gray-200";
                    ?>
                        <a href="giftcard.php?type=<?php echo $key; ?>" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all duration-200 shrink-0 <?php echo $btn_classes; ?>">
                            <i class="<?php echo $card['icon']; ?> text-sm <?php echo !$is_active ? $card['text_color'] : ''; ?>"></i>
                            <?php echo htmlspecialchars(explode(' ', $card['name'])[0]); ?> Card
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-base font-black text-gray-900 tracking-tight uppercase border-b border-gray-100 pb-3 mb-6 flex items-center gap-2">
                        <i class="fas fa-list-check <?php echo $active_card['text_color']; ?>"></i> Purchase Steps
                    </h2>

                    <div class="flex gap-4 mb-6 relative">
                        <div class="absolute left-4 top-10 bottom-0 w-0.5 bg-gray-100 hidden sm:block"></div>
                        <div class="w-9 h-9 <?php echo $active_card['bg_light']; ?> border <?php echo $active_card['border_color']; ?> <?php echo $active_card['text_color']; ?> font-black rounded-full flex items-center justify-center shrink-0 shadow-sm">
                            1
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Access Portal Link</h4>
                            <p class="text-xs text-gray-500 leading-relaxed mt-1">
                                Click the primary <span class="font-bold text-gray-700">"Proceed to Purchase Website"</span> button located on the right sidebar module to launch the official web instance safely.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-6 relative">
                        <div class="absolute left-4 top-10 bottom-0 w-0.5 bg-gray-100 hidden sm:block"></div>
                        <div class="w-9 h-9 <?php echo $active_card['bg_light']; ?> border <?php echo $active_card['border_color']; ?> <?php echo $active_card['text_color']; ?> font-black rounded-full flex items-center justify-center shrink-0 shadow-sm">
                            2
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Custom Processing Config</h4>
                            <p class="text-xs text-gray-500 leading-relaxed mt-1">
                                <?php echo htmlspecialchars($active_card['instructions']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-9 h-9 <?php echo $active_card['bg_light']; ?> border <?php echo $active_card['border_color']; ?> <?php echo $active_card['text_color']; ?> font-black rounded-full flex items-center justify-center shrink-0 shadow-sm">
                            3
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Upload Transaction Invoice Voucher</h4>
                            <p class="text-xs text-gray-500 leading-relaxed mt-1">
                                Keep your processing completion validation receipt statement file safe, then upload it into your client profile panel area to finalize automatic asset validation loops.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                    <i class="fas fa-circle-exclamation text-amber-500 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <h5 class="text-xs font-extrabold text-amber-900 uppercase tracking-wider">Security Notice</h5>
                        <p class="text-[11px] text-amber-700 leading-relaxed mt-0.5">
                            Verify the external domain browser navigation strings match secure SSL structures. Never disclose active alphanumeric keys to unverified support entities.
                        </p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center mb-3 <?php echo $active_card['bg_light']; ?>">
                        <i class="<?php echo $active_card['icon']; ?> text-xl <?php echo $active_card['text_color']; ?>"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Ready to Purchase?</h3>
                    <p class="text-xs text-gray-500 mt-1 mb-6 leading-relaxed">
                        Redirect instantly to our certified merchant system instance to clear your dynamic cash balance allocations.
                    </p>
                    
                    <a href="<?php echo htmlspecialchars($active_card['url']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="block text-center w-full bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-wider py-4 px-6 rounded-xl transition-all shadow focus:outline-none flex items-center justify-center gap-2">
                        Proceed to Purchase Website <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-tight mb-3 flex items-center gap-2">
                        <i class="fab fa-youtube text-red-600 text-base"></i> Video Walkthrough Guide
                    </h3>
                    
                    <div class="relative w-full aspect-video rounded-xl overflow-hidden shadow-inner bg-slate-900 border border-gray-100">
                        <iframe 
                            class="absolute top-0 left-0 w-full h-full"
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($active_card['youtube_id']); ?>?rel=0" 
                            title="YouTube video player" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    
                    <p class="text-[10px] text-gray-400 mt-3 text-center italic leading-relaxed">
                        Having issues processing your transaction window? Watch this visual execution demo layout module.
                    </p>
                </div>

            </div>
        </main>

        <?php if (file_exists("inc/footer.php")) { include "inc/footer.php"; } ?>
    </div>

    <style>
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</body>
</html>
