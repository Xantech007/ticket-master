<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect();
    }
} catch (Exception $e) {
    // Keep the page renderable; the fallback UI below handles missing data.
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function js($value) {
    return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function image_path($value, $basePath, $fallback) {
    $value = trim((string)$value);

    if ($value === '') {
        return $fallback;
    }

    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }

    if (preg_match('/^(uploads|assets)\//i', $value)) {
        return str_replace('\\', '/', $value);
    }

    return rtrim($basePath, '/') . '/' . basename($value);
}

$concert_not_found = false;
$concert_id = null;

if (!isset($_GET['concert_id']) || !ctype_digit((string)$_GET['concert_id'])) {
    $concert_not_found = true;
} else {
    $concert_id = (int)$_GET['concert_id'];
}

$artist_name = "";
$concert_title = "";
$concert_details = "";
$stadium_map_image = "";
$ticket_sections = [];

try {
    if (!$concert_not_found && $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM concerts WHERE concert_id = ? LIMIT 1");
        $stmt->execute([$concert_id]);
        $concert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$concert) {
            $concert_not_found = true;
        } else {
            $stmt = $pdo->prepare("SELECT artist_name FROM artists WHERE artist_id = ? LIMIT 1");
            $stmt->execute([$concert['artist_id']]);
            $artist = $stmt->fetch(PDO::FETCH_ASSOC);

            $artist_name = $artist['artist_name'] ?? '';
            $concert_title = $concert['title'] ?? '';

            $concert_details = implode(" / ", array_filter([
                $concert['concert_date'] ?? '',
                $concert['day_time'] ?? '',
                $concert['venue'] ?? '',
                $concert['location'] ?? '',
            ]));

            $stadium_map_image = image_path(
                $concert['map_view'] ?? '',
                'uploads/concerts',
                'assets/images/theatre.jpg'
            );

            $stmt = $pdo->prepare("
                SELECT * FROM tickets
                WHERE concert_id = ?
                ORDER BY section_name, row_name, seat_name
            ");
            $stmt->execute([$concert_id]);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $section = $row['section_name'] ?? '';
                $row_name = $row['row_name'] ?? '';
                $key = md5($section . '|' . $row_name);
                $sectionViewImage = image_path(
                    $row['section_view'] ?? '',
                    'uploads/tickets',
                    ''
                );

                if (!isset($ticket_sections[$key])) {
                    $ticket_sections[$key] = [
                        'id' => $key,
                        'section' => $section,
                        'row' => $row_name,
                        'type' => $row['ticket_name'] ?? '',
                        'price' => (float)($row['price'] ?? 0),
                        'entry' => 'Mobile Entry',
                        'section_view' => $sectionViewImage,
                        'seats' => []
                    ];
                } elseif ($ticket_sections[$key]['section_view'] === '' && $sectionViewImage !== '') {
                    $ticket_sections[$key]['section_view'] = $sectionViewImage;
                }

                $ticket_sections[$key]['seats'][] = $row['seat_name'] ?? '';
            }

            $ticket_sections = array_values($ticket_sections);
        }
    }
} catch (PDOException $e) {
    die("Database error: " . e($e->getMessage()));
}

if ($concert_not_found) {
    $artist_name = "Unavailable";
    $concert_title = "Concert Not Found";
    $concert_details = "The selected concert does not exist or has been removed.";
    $stadium_map_image = "assets/images/theatre.jpg";
    $ticket_sections = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.php"; ?>

<body class="bg-slate-50 text-slate-950 font-sans antialiased">
    <div id="__next" class="min-h-screen">
        <?php include "inc/navbar.php"; ?>
        <?php include "inc/header.php"; ?>

        <section class="bg-white border-b border-slate-200 px-4 py-5 shadow-sm sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#024DDF] ring-1 ring-blue-100">
                        <i class="fas fa-ticket-alt"></i> Selected Concert
                    </span>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl lg:text-4xl">
                        <?php echo e($artist_name); ?>
                        <span class="block text-slate-600 sm:inline"> - <?php echo e($concert_title); ?></span>
                    </h1>
                    <p class="mt-2 flex items-start gap-2 text-sm font-semibold leading-6 text-slate-500 sm:text-base">
                        <i class="far fa-calendar-check mt-1 text-[#024DDF]"></i>
                        <span><?php echo e($concert_details); ?></span>
                    </p>
                </div>

                <button type="button"
                        onclick="openImageModal(<?php echo js($stadium_map_image); ?>, 'Full Stadium Map')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#024DDF] focus:outline-none focus:ring-4 focus:ring-blue-100 sm:w-auto">
                    <i class="fas fa-search-plus"></i> Open Map
                </button>
            </div>
        </section>

        <section class="relative isolate overflow-hidden bg-slate-950">
            <button type="button"
                    onclick="openImageModal(<?php echo js($stadium_map_image); ?>, 'Full Stadium Map')"
                    class="group block h-[280px] w-full cursor-zoom-in text-left sm:h-[360px] lg:h-[460px]"
                    aria-label="Open full stadium map">
                <img src="<?php echo e($stadium_map_image); ?>"
                     onerror="this.onerror=null; this.src='assets/images/theatre.jpg';"
                     alt="Stadium seating map"
                     class="h-full w-full object-cover object-center opacity-95 transition duration-500 group-hover:scale-[1.02] group-hover:opacity-100">
                <span class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/10 to-transparent"></span>
                <span class="absolute bottom-4 left-4 right-4 flex flex-col gap-3 rounded-2xl border border-white/10 bg-slate-950/70 p-4 text-white shadow-2xl backdrop-blur md:bottom-6 md:left-8 md:right-auto md:min-w-[360px]">
                    <span class="text-[11px] font-black uppercase tracking-[0.2em] text-blue-200">
                        <i class="fas fa-map-marked-alt mr-2"></i> Map View
                    </span>
                    <span class="flex items-center justify-between gap-4 text-sm font-bold">
                        View the full seating layout
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-slate-950 transition group-hover:bg-[#024DDF] group-hover:text-white">
                            <i class="fas fa-expand-alt"></i>
                        </span>
                    </span>
                </span>
            </button>
        </section>

        <?php if ($concert_not_found): ?>
            <div class="max-w-7xl mx-auto px-4 md:px-8 mt-6">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 font-bold text-red-700">
                    Sorry, the concert you are looking for was not found or is no longer available.
                </div>
            </div>
        <?php endif; ?>

        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
                <div class="space-y-4 lg:col-span-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="flex items-center gap-2 text-lg font-black uppercase tracking-wider text-slate-800">
                                <i class="fas fa-list-ol text-[#024DDF]"></i> Available Seating
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500">Choose a section, review its image, then lock in seats.</p>
                        </div>
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">
                            <?php echo count($ticket_sections); ?> section(s)
                        </span>
                    </div>

                    <?php if (empty($ticket_sections) && !$concert_not_found): ?>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm font-bold text-slate-500 shadow-sm">
                            No tickets are currently available for this concert.
                        </div>
                    <?php endif; ?>

                    <?php foreach ($ticket_sections as $sec): ?>
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-200 hover:shadow-md" id="card-<?php echo e($sec['id']); ?>">
                            <div class="grid gap-0 md:grid-cols-[180px_1fr]">
                                <div class="relative min-h-[150px] bg-slate-100 hidden md:block">
                                    <?php if (!empty($sec['section_view'])): ?>
                                        <button type="button"
                                                onclick="event.stopPropagation(); openImageModal(<?php echo js($sec['section_view']); ?>, <?php echo js(($sec['section'] ?: 'Section') . ' ' . ($sec['row'] ?: 'View')); ?>)"
                                                class="group h-full w-full cursor-zoom-in overflow-hidden text-left"
                                                aria-label="Open section view image">
                                            <img src="<?php echo e($sec['section_view']); ?>"
                                                 onerror="this.closest('.relative').classList.add('image-load-failed'); this.remove();"
                                                 alt="Section <?php echo e($sec['section']); ?> view"
                                                 class="h-full min-h-[150px] w-full object-cover transition duration-300 group-hover:scale-105">
                                            <span class="absolute inset-x-3 bottom-3 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-950/75 px-3 py-2 text-xs font-black uppercase tracking-wider text-white backdrop-blur">
                                                <i class="fas fa-search-plus"></i> Section View
                                            </span>
                                        </button>
                                    <?php endif; ?>
                                    <div class="section-image-empty absolute inset-0 flex flex-col items-center justify-center gap-2 px-4 text-center text-slate-400">
                                        <i class="far fa-image text-2xl"></i>
                                        <span class="text-xs font-black uppercase tracking-wider">No Section Image</span>
                                    </div>
                                </div>

                                <div>
                                    <button type="button"
                                            onclick="toggleSectionDisplay(<?php echo js($sec['id']); ?>)"
                                            class="flex w-full cursor-pointer items-center justify-between gap-4 p-4 text-left transition hover:bg-slate-50 sm:p-5">
                                        <span class="min-w-0">
                                            <span class="block text-base font-black tracking-tight text-slate-950 sm:text-lg">
                                                <?php echo e($sec['section']); ?> <span class="text-slate-300">/</span> <?php echo e($sec['row']); ?>
                                            </span>
                                            <span class="mt-1 block text-xs font-bold uppercase tracking-wide text-slate-400">
                                                <?php echo count($sec['seats']); ?> seat entry(s) available
                                            </span>
                                            <span class="mt-3 flex flex-wrap items-center gap-2">
                                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase text-amber-700">
                                                    <?php echo e($sec['type']); ?>
                                                </span>
                                            
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500">
                                                    <i class="fas fa-mobile-alt"></i>
                                                    <?php echo e($sec['entry']); ?>
                                                </span>
                                            </span>
                                            
                                            <?php if (!empty($sec['section_view'])): ?>
                                            <button
                                                type="button"
                                                onclick="event.stopPropagation();openImageModal(<?php echo js($sec['section_view']); ?>,<?php echo js($sec['section'].' '.$sec['row']); ?>)"
                                                class="mt-3 inline-flex md:hidden items-center gap-2 rounded-lg bg-[#024DDF] px-3 py-2 text-xs font-bold text-white">
                                                <i class="fas fa-image"></i>
                                                View Section
                                            </button>
                                            <?php endif; ?>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-3 text-right">
                                            <span>
                                                <span class="block text-xl font-black tracking-tight text-[#024DDF]">
                                                    $<?php echo number_format((float)$sec['price'], 2); ?>
                                                </span>
                                                <span class="block text-[10px] font-semibold text-slate-400">ea + fees</span>
                                            </span>
                                            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300" id="icon-<?php echo e($sec['id']); ?>"></i>
                                        </span>
                                    </button>

                                    <div id="drawer-<?php echo e($sec['id']); ?>" class="hidden border-t border-slate-100 bg-slate-50/80 p-4 sm:p-5">
                                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-xs font-bold uppercase tracking-tight text-slate-500">
                                                Select desired seat positions below.
                                            </p>
                                            <?php if (!empty($sec['section_view'])): ?>
                                                <button type="button"
                                                        onclick="openImageModal(<?php echo js($sec['section_view']); ?>, <?php echo js(($sec['section'] ?: 'Section') . ' ' . ($sec['row'] ?: 'View')); ?>)"
                                                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black uppercase tracking-wider text-slate-700 transition hover:border-blue-200 hover:text-[#024DDF]">
                                                    <i class="far fa-image"></i> View Image
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 xs:grid-cols-3 sm:grid-cols-4 md:grid-cols-5 xl:grid-cols-6">
                                            <?php foreach ($sec['seats'] as $seat): ?>
                                                <button type="button"
                                                        onclick="toggleSeatSelection(this, <?php echo js($sec['id']); ?>, <?php echo js($seat); ?>, <?php echo json_encode((float)$sec['price']); ?>, <?php echo js($sec['section']); ?>, <?php echo js($sec['row']); ?>)"
                                                        class="seat-btn min-h-11 rounded-xl border border-slate-300 bg-white px-2 py-2 text-center text-xs font-black text-slate-700 transition hover:border-[#024DDF] hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                                    <i class="fas fa-chair mr-1 text-[10px] opacity-40"></i>
                                                    <?php echo e($seat); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <aside class="lg:col-span-4 lg:sticky lg:top-4">
                    <div id="checkout-sidebar-panel"
                    class="rounded-2xl border border-slate-200 bg-white p-5 opacity-45 shadow-md transition sm:p-6">
                        <h3 class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3 text-base font-black uppercase tracking-wider text-slate-800">
                            <i class="fas fa-shopping-basket text-[#024DDF]"></i> Order Summary
                        </h3>

                        <div id="selected-seats-container" class="mb-4 max-h-[220px] space-y-2 overflow-y-auto pr-1">
                            <p class="py-2 text-xs font-bold italic text-slate-400">No seats selected yet.</p>
                        </div>

                        <div class="space-y-3 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-xs font-bold text-slate-500">
                                <span>Selected Count:</span>
                                <span id="summary-count">0 seats</span>
                            </div>
                            <div class="flex items-baseline justify-between border-t border-dashed border-slate-100 pt-3">
                                <span class="text-sm font-black text-slate-950">Estimated Total:</span>
                                <span class="text-2xl font-black tracking-tight text-[#024DDF]" id="summary-total-price">$0.00</span>
                            </div>
                        </div>

                        <form action="checkout.php" method="POST" class="mt-6">
                            <input type="hidden" name="serialized_seat_payload" id="serialized-seat-payload" value="">
                            <button
                            id="checkoutBtn"
                            type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#024DDF] px-6 py-4 text-sm font-black uppercase tracking-widest text-white shadow transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                Proceed to Checkout <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </main>

        <div id="image-modal" class="fixed inset-0 z-[9999] hidden bg-slate-950/95 p-3 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="image-modal-title">
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl">
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-3 py-3 text-white sm:px-4">
                    <div class="min-w-0">
                        <h2 id="image-modal-title" class="truncate text-sm font-black uppercase tracking-wider">Image View</h2>
                        <p id="image-modal-zoom-label" class="text-xs font-semibold text-slate-400">100%</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="zoomImage(-0.2)" class="modal-tool-btn" aria-label="Zoom out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button type="button" onclick="resetImageZoom()" class="modal-tool-btn" aria-label="Reset zoom">
                            <i class="fas fa-compress-alt"></i>
                        </button>
                        <button type="button" onclick="zoomImage(0.2)" class="modal-tool-btn" aria-label="Zoom in">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button type="button" onclick="closeImageModal()" class="modal-tool-btn bg-white text-slate-950 hover:bg-blue-50" aria-label="Close image view">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div id="image-modal-stage" class="relative flex min-h-0 flex-1 touch-none cursor-grab items-center justify-center overflow-hidden bg-black">
                    <img id="image-modal-img"
                         src=""
                         alt=""
                         draggable="false"
                         class="max-h-full max-w-full select-none object-contain transition-transform duration-100 ease-out">
                </div>
            </div>
        </div>

        <?php include "inc/footer.php"; ?>
    </div>

<script>
let pickedSeatsRegister = [];

let activeImageScale = 1;
let activeImageTranslateX = 0;
let activeImageTranslateY = 0;

let isImageDragging = false;
let dragStartX = 0;
let dragStartY = 0;
let dragOriginX = 0;
let dragOriginY = 0;

let pinchStartDistance = 0;

function escapeHtml(value){
    return String(value).replace(/[&<>"']/g,char=>({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#039;'
    }[char]));
}

function openImageModal(src,title){

    const modal=document.getElementById('image-modal');
    const image=document.getElementById('image-modal-img');

    if(!src)return;

    image.src=src;
    image.alt=title||"Image";

    document.getElementById("image-modal-title").innerText=title||"Image";

    modal.classList.remove("hidden");
    document.body.classList.add("modal-open");

    resetImageZoom();
}

function closeImageModal(){

    document.getElementById("image-modal").classList.add("hidden");
    document.body.classList.remove("modal-open");

    document.getElementById("image-modal-img").src="";

    resetImageZoom();
}

function applyImageTransform(){

    const image=document.getElementById("image-modal-img");

    image.style.transform=
        `translate(${activeImageTranslateX}px,${activeImageTranslateY}px) scale(${activeImageScale})`;

    document.getElementById("image-modal-zoom-label").innerText=
        Math.round(activeImageScale*100)+"%";
}

function zoomImage(delta){

    activeImageScale+=delta;

    if(activeImageScale<1)
        activeImageScale=1;

    if(activeImageScale>5)
        activeImageScale=5;

    if(activeImageScale===1){
        activeImageTranslateX=0;
        activeImageTranslateY=0;
    }

    applyImageTransform();
}

function resetImageZoom(){

    activeImageScale=1;
    activeImageTranslateX=0;
    activeImageTranslateY=0;

    applyImageTransform();
}

function getDistance(t1,t2){

    const dx=t1.clientX-t2.clientX;
    const dy=t1.clientY-t2.clientY;

    return Math.sqrt(dx*dx+dy*dy);

}

function toggleSectionDisplay(sectionId){

    const drawer=document.getElementById("drawer-"+sectionId);
    const icon=document.getElementById("icon-"+sectionId);
    const card=document.getElementById("card-"+sectionId);

    if(!drawer)return;

    drawer.classList.toggle("hidden");
    icon.classList.toggle("rotate-180");

    card.classList.toggle("border-blue-200");
    card.classList.toggle("shadow-md");
    card.classList.toggle("border-slate-200");
}

function toggleSeatSelection(btn,sectionId,seatName,priceMetric,sectionName,rowName){

    const id=sectionId+"_"+seatName;

    const index=pickedSeatsRegister.findIndex(s=>s.id===id);

    if(index>-1){

        pickedSeatsRegister.splice(index,1);

        btn.classList.remove("selected-seat");

    }else{

        pickedSeatsRegister.push({

            id:id,
            section_id:sectionId,
            section:sectionName,
            row:rowName,
            seat:seatName,
            price:parseFloat(priceMetric)||0

        });

        btn.classList.add("selected-seat");

    }

    refreshSidebarStateView();

}

function refreshSidebarStateView(){

    const sidebar=document.getElementById("checkout-sidebar-panel");

    const container=document.getElementById("selected-seats-container");

    const count=document.getElementById("summary-count");

    const total=document.getElementById("summary-total-price");

    const payload=document.getElementById("serialized-seat-payload");

    const checkout=document.getElementById("checkoutBtn");

    if(pickedSeatsRegister.length===0){

        sidebar.classList.add("opacity-45","pointer-events-none","select-none");

        container.innerHTML=
        `<p class="py-2 text-xs font-bold italic text-slate-400">
        No seats selected yet.
        </p>`;

        count.innerHTML="0 seats";

        total.innerHTML="$0.00";

        payload.value="";

        if(checkout) checkout.disabled=true;

        return;
    }

    sidebar.classList.remove("opacity-45","pointer-events-none","select-none");

    if(checkout) checkout.disabled=false;

    let sum=0;

    let html="";

    pickedSeatsRegister.forEach(item=>{

        sum+=item.price;

        html+=`
        <div class="flex justify-between rounded-xl border border-blue-100 bg-blue-50 p-3 text-xs">
            <div>
                <div class="font-black">${escapeHtml(item.section)} / ${escapeHtml(item.row)}</div>
                <div>${escapeHtml(item.seat)}</div>
            </div>
            <div class="font-black text-[#024DDF]">$${item.price.toFixed(2)}</div>
        </div>`;

    });

    container.innerHTML=html;

    count.innerHTML=pickedSeatsRegister.length+" seat(s)";

    total.innerHTML="$"+sum.toFixed(2);

    payload.value=JSON.stringify(pickedSeatsRegister);

}

document.addEventListener("keydown",e=>{

    if(e.key==="Escape")
        closeImageModal();

});

document.addEventListener("DOMContentLoaded",()=>{

    const stage=document.getElementById("image-modal-stage");

    const modal=document.getElementById("image-modal");

    stage.addEventListener("wheel",e=>{

        if(modal.classList.contains("hidden")) return;

        e.preventDefault();

        zoomImage(e.deltaY>0?-0.15:0.15);

    },{passive:false});

    stage.addEventListener("pointerdown",e=>{

        if(activeImageScale<=1) return;

        isImageDragging=true;

        dragStartX=e.clientX;
        dragStartY=e.clientY;

        dragOriginX=activeImageTranslateX;
        dragOriginY=activeImageTranslateY;

        stage.setPointerCapture(e.pointerId);

    });

    stage.addEventListener("pointermove",e=>{

        if(!isImageDragging) return;

        activeImageTranslateX=
        dragOriginX+(e.clientX-dragStartX);

        activeImageTranslateY=
        dragOriginY+(e.clientY-dragStartY);

        applyImageTransform();

    });

    stage.addEventListener("pointerup",()=>{

        isImageDragging=false;

    });

    stage.addEventListener("pointercancel",()=>{

        isImageDragging=false;

    });

    stage.addEventListener("touchmove",function(e){

        if(e.touches.length!==2) return;

        e.preventDefault();

        const distance=getDistance(
            e.touches[0],
            e.touches[1]
        );

        if(!pinchStartDistance){

            pinchStartDistance=distance;
            return;

        }

        const diff=distance-pinchStartDistance;

        activeImageScale+=diff*0.003;

        if(activeImageScale<1)
            activeImageScale=1;

        if(activeImageScale>5)
            activeImageScale=5;

        pinchStartDistance=distance;

        applyImageTransform();

    },{passive:false});

    stage.addEventListener("touchend",()=>{

        pinchStartDistance=0;

    });

    stage.addEventListener("dblclick",()=>{

        resetImageZoom();

    });

    refreshSidebarStateView();

});
</script>

<style>
    body {
        overflow-x: hidden;
    }

    body.modal-open {
        overflow: hidden;
        touch-action: none;
    }

    .rotate-180 {
        transform: rotate(180deg);
    }

    .seat-btn {
        transition:
            border-color .15s ease,
            background-color .15s ease,
            color .15s ease,
            box-shadow .15s ease,
            transform .15s ease;
    }

    .seat-btn:hover {
        transform: translateY(-1px);
    }

    .selected-seat {
        background: #024DDF !important;
        color: #fff !important;
        border-color: #024DDF !important;
        box-shadow: inset 0 2px 8px rgba(0,0,0,.25);
    }

    .modal-tool-btn {
        display: inline-flex;
        width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        background: rgba(255,255,255,.08);
        color: #fff;
        transition: .2s;
    }

    .modal-tool-btn:hover {
        background: rgba(255,255,255,.18);
        transform: translateY(-1px);
    }

    .modal-tool-btn.bg-white {
        background: #fff;
        color: #0f172a;
    }

    #image-modal-stage {
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
        overflow: hidden;
    }

    #image-modal-img {
        transform-origin: center center;
        cursor: grab;
        will-change: transform;
        transition: transform .08s linear;
        max-width: 100%;
        max-height: 100%;
    }

    #image-modal-stage.cursor-grabbing #image-modal-img,
    #image-modal-img:active {
        cursor: grabbing;
    }

    .section-image-empty {
        display: flex;
    }

    .image-load-failed .section-image-empty {
        display: flex;
    }

    @media (max-width:768px){

        /* hide preview image on phones */
        .ticket-preview-image{
            display:none;
        }

        #image-modal{
            padding:.5rem;
        }

        .modal-tool-btn{
            width:2.2rem;
            height:2.2rem;
            border-radius:.65rem;
        }

        #image-modal-title{
            font-size:.8rem;
        }

        #image-modal-zoom-label{
            font-size:.7rem;
        }

    }

    @media (max-width:640px){

        .modal-tool-btn{
            width:2rem;
            height:2rem;
        }

    }
</style>
</body>
</html>
