<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
} elseif (file_exists('config/db.php')) {
    require_once 'config/db.php';
}

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
$concert_date = "";
$concert_time = "";
$concert_venue = "";
$concert_location = "";
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
            $concert_date = $concert['concert_date'] ?? '';
            $concert_time = $concert['day_time'] ?? '';
            $concert_venue = $concert['venue'] ?? '';
            $concert_location = $concert['location'] ?? '';

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
                        'type' => $row['ticket_name'] ?? 'Standard Ticket',
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
    $concert_date = "Select another concert";
    $concert_time = "";
    $concert_venue = "";
    $concert_location = "";
    $stadium_map_image = "assets/images/theatre.jpg";
    $ticket_sections = [];
}

$event_heading = trim($artist_name . ($concert_title !== '' ? ': ' . $concert_title : ''));
$event_meta_parts = array_filter([$concert_date, $concert_time, $concert_venue]);
$event_meta = implode(' · ', $event_meta_parts);
$ticket_count_label = count($ticket_sections) > 0 ? count($ticket_sections) . ' Tickets' : '0 Tickets';
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.php"; ?>

<body class="ticket-page">
    <div id="__next" class="ticket-shell">
        <header class="event-header">
            <div class="event-header__accent"></div>
            <div class="event-header__main">
                <div class="event-header__copy">
                    <h1><?php echo e($event_heading); ?></h1>
                    <p>
                        <?php echo e($event_meta ?: 'Event details unavailable'); ?>
                        <?php if ($concert_location !== ''): ?>
                            <span class="event-location"> - <?php echo e($concert_location); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <button type="button" class="info-button" aria-label="Event information">
                    <i class="fas fa-info"></i>
                </button>
            </div>
            <div class="important-info">
                <strong>Important Info:</strong>
                <span>Tickets are not available at the box office.</span>
                <button type="button">more</button>
            </div>
        </header>

        <section class="map-panel">
            <button type="button"
                    class="map-switch"
                    onclick="openImageModal(<?php echo js($stadium_map_image); ?>, 'Full Stadium Map')">
                <i class="fas fa-exchange-alt"></i>
                <span>Switch to Map</span>
            </button>
            <button type="button"
                    class="map-image-button"
                    onclick="openImageModal(<?php echo js($stadium_map_image); ?>, 'Full Stadium Map')"
                    aria-label="Open full stadium map">
                <img src="<?php echo e($stadium_map_image); ?>"
                     onerror="this.onerror=null; this.src='assets/images/theatre.jpg';"
                     alt="Stadium seating map">
            </button>
        </section>

        <main class="ticket-content">
            <section class="ticket-controls" aria-label="Ticket controls">
                <button type="button" class="select-pill">
                    <span><?php echo e($ticket_count_label); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <button type="button" class="filter-pill">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filters</span>
                </button>
            </section>

            <section class="all-in-banner">
                <strong>We're All In:</strong>
                <span>Prices include fees (before taxes).</span>
            </section>

            <section class="paypal-strip" aria-label="PayPal payment option">
                <span class="paypal-wordmark">PayPal</span>
                <span>Buy Now, Pay Later</span>
                <a href="#" onclick="return false;">More Info</a>
            </section>

            <?php if ($concert_not_found): ?>
                <div class="empty-state">
                    Sorry, the concert you are looking for was not found or is no longer available.
                </div>
            <?php elseif (empty($ticket_sections)): ?>
                <div class="empty-state">
                    No tickets are currently available for this concert.
                </div>
            <?php endif; ?>

            <section class="ticket-list" aria-label="Available tickets">
                <?php foreach ($ticket_sections as $sec): ?>
                    <?php
                        $section_label = trim(($sec['section'] !== '' ? 'Sec ' . $sec['section'] : 'Section') . ($sec['row'] !== '' ? ' · Row ' . $sec['row'] : ''));
                        $ticket_type = $sec['type'] !== '' ? $sec['type'] : 'Standard Ticket';
                        $image_title = trim(($sec['section'] ?: 'Section') . ' ' . ($sec['row'] ?: 'View'));
                    ?>
                    <article class="ticket-row" id="card-<?php echo e($sec['id']); ?>">
                        <button type="button"
                                class="ticket-row__main"
                                onclick="toggleSectionDisplay(<?php echo js($sec['id']); ?>)">
                            <span class="ticket-thumb-wrap">
                                <?php if (!empty($sec['section_view'])): ?>
                                    <img src="<?php echo e($sec['section_view']); ?>"
                                         onerror="this.closest('.ticket-thumb-wrap').classList.add('image-load-failed'); this.remove();"
                                         alt="Section <?php echo e($sec['section']); ?> view"
                                         class="ticket-thumb">
                                <?php endif; ?>
                                <span class="ticket-thumb-empty">
                                    <i class="far fa-image"></i>
                                </span>
                            </span>

                            <span class="ticket-row__copy">
                                <span class="ticket-row__title"><?php echo e($section_label); ?></span>
                                <span class="ticket-row__type"><?php echo e($ticket_type); ?></span>
                            </span>

                            <span class="ticket-row__price">$<?php echo number_format((float)$sec['price'], 2); ?></span>
                            <i class="fas fa-chevron-down ticket-row__chevron" id="icon-<?php echo e($sec['id']); ?>"></i>
                        </button>

                        <div id="drawer-<?php echo e($sec['id']); ?>" class="seat-drawer hidden">
                            <div class="seat-drawer__head">
                                <span><?php echo count($sec['seats']); ?> seat entry(s)</span>
                                <?php if (!empty($sec['section_view'])): ?>
                                    <button type="button"
                                            onclick="openImageModal(<?php echo js($sec['section_view']); ?>, <?php echo js($image_title); ?>)">
                                        <i class="fas fa-search-plus"></i>
                                        View Image
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="seat-grid">
                                <?php foreach ($sec['seats'] as $seat): ?>
                                    <button type="button"
                                            onclick="toggleSeatSelection(this, <?php echo js($sec['id']); ?>, <?php echo js($seat); ?>, <?php echo json_encode((float)$sec['price']); ?>, <?php echo js($sec['section']); ?>, <?php echo js($sec['row']); ?>)"
                                            class="seat-btn">
                                        <?php echo e($seat); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>

        <aside id="checkout-sidebar-panel" class="checkout-bar is-disabled">
            <div>
                <span id="summary-count">0 seats</span>
                <strong id="summary-total-price">$0.00</strong>
            </div>
            <form action="checkout.php" method="POST">
                <input type="hidden" name="serialized_seat_payload" id="serialized-seat-payload" value="">
                <button type="submit">Checkout</button>
            </form>
        </aside>

        <div id="image-modal" class="image-modal hidden" role="dialog" aria-modal="true" aria-labelledby="image-modal-title">
            <div class="image-modal__panel">
                <div class="image-modal__toolbar">
                    <div class="image-modal__title">
                        <h2 id="image-modal-title">Image View</h2>
                        <p id="image-modal-zoom-label">100%</p>
                    </div>
                    <div class="image-modal__tools">
                        <button type="button" onclick="zoomImage(-0.25)" class="modal-tool-btn" aria-label="Zoom out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button type="button" onclick="resetImageZoom()" class="modal-tool-btn" aria-label="Reset zoom">
                            <i class="fas fa-compress-alt"></i>
                        </button>
                        <button type="button" onclick="zoomImage(0.25)" class="modal-tool-btn" aria-label="Zoom in">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button type="button" onclick="closeImageModal()" class="modal-tool-btn modal-tool-btn--close" aria-label="Close image view">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div id="image-modal-stage" class="image-modal__stage">
                    <img id="image-modal-img" src="" alt="" draggable="false">
                </div>
            </div>
        </div>
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

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function openImageModal(src, title) {
            const modal = document.getElementById('image-modal');
            const image = document.getElementById('image-modal-img');
            const titleElement = document.getElementById('image-modal-title');

            if (!modal || !image || !src) {
                return;
            }

            image.onload = () => resetImageZoom();
            image.src = src;
            image.alt = title || 'Expanded image';
            titleElement.innerText = title || 'Image View';
            modal.classList.remove('hidden');
            document.body.classList.add('modal-open');
            resetImageZoom();
        }

        function closeImageModal() {
            const modal = document.getElementById('image-modal');
            const image = document.getElementById('image-modal-img');

            if (!modal || !image) {
                return;
            }

            modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
            image.removeAttribute('src');
            resetImageZoom();
        }

        function clampImagePan() {
            const stage = document.getElementById('image-modal-stage');
            const image = document.getElementById('image-modal-img');

            if (!stage || !image || !image.naturalWidth || activeImageScale <= 1) {
                activeImageTranslateX = 0;
                activeImageTranslateY = 0;
                return;
            }

            const stageRect = stage.getBoundingClientRect();
            const imageRect = image.getBoundingClientRect();
            const scaledWidth = imageRect.width;
            const scaledHeight = imageRect.height;
            const maxX = Math.max(0, (scaledWidth - stageRect.width) / 2);
            const maxY = Math.max(0, (scaledHeight - stageRect.height) / 2);

            activeImageTranslateX = Math.min(maxX, Math.max(-maxX, activeImageTranslateX));
            activeImageTranslateY = Math.min(maxY, Math.max(-maxY, activeImageTranslateY));
        }

        function applyImageTransform() {
            const image = document.getElementById('image-modal-img');
            const zoomLabel = document.getElementById('image-modal-zoom-label');

            if (!image) {
                return;
            }

            clampImagePan();
            image.style.transform = `translate3d(${activeImageTranslateX}px, ${activeImageTranslateY}px, 0) scale(${activeImageScale})`;

            if (zoomLabel) {
                zoomLabel.innerText = `${Math.round(activeImageScale * 100)}%`;
            }
        }

        function zoomImage(delta) {
            activeImageScale = Math.min(5, Math.max(1, +(activeImageScale + delta).toFixed(2)));
            applyImageTransform();
        }

        function resetImageZoom() {
            activeImageScale = 1;
            activeImageTranslateX = 0;
            activeImageTranslateY = 0;
            applyImageTransform();
        }

        function toggleSectionDisplay(sectionId) {
            const drawer = document.getElementById('drawer-' + sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            const card = document.getElementById('card-' + sectionId);

            if (!drawer || !icon || !card) {
                return;
            }

            const isOpening = drawer.classList.contains('hidden');
            drawer.classList.toggle('hidden', !isOpening);
            icon.classList.toggle('is-open', isOpening);
            card.classList.toggle('is-open', isOpening);
        }

        function toggleSeatSelection(buttonElement, sectionId, seatName, priceMetric, sectionName, rowName) {
            const compositeKeyId = `${sectionId}_${seatName}`;
            const searchIndex = pickedSeatsRegister.findIndex(item => item.id === compositeKeyId);

            if (searchIndex > -1) {
                pickedSeatsRegister.splice(searchIndex, 1);
                buttonElement.classList.remove('is-selected');
            } else {
                pickedSeatsRegister.push({
                    id: compositeKeyId,
                    section_id: sectionId,
                    section: sectionName,
                    row: rowName,
                    seat: seatName,
                    price: parseFloat(priceMetric) || 0
                });
                buttonElement.classList.add('is-selected');
            }

            refreshSidebarStateView();
        }

        function refreshSidebarStateView() {
            const checkoutBar = document.getElementById('checkout-sidebar-panel');
            const countLabel = document.getElementById('summary-count');
            const priceLabel = document.getElementById('summary-total-price');
            const hiddenPayloadInput = document.getElementById('serialized-seat-payload');

            if (pickedSeatsRegister.length === 0) {
                checkoutBar.classList.add('is-disabled');
                countLabel.innerText = '0 seats';
                priceLabel.innerText = '$0.00';
                hiddenPayloadInput.value = '';
                return;
            }

            const cumulativeTotalSum = pickedSeatsRegister.reduce((sum, seatNode) => sum + seatNode.price, 0);
            checkoutBar.classList.remove('is-disabled');
            countLabel.innerText = `${pickedSeatsRegister.length} seat(s) selected`;
            priceLabel.innerText = `$${cumulativeTotalSum.toFixed(2)}`;
            hiddenPayloadInput.value = JSON.stringify(pickedSeatsRegister);
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const stage = document.getElementById('image-modal-stage');
            const modal = document.getElementById('image-modal');

            if (!stage || !modal) {
                return;
            }

            stage.addEventListener('wheel', event => {
                if (modal.classList.contains('hidden')) {
                    return;
                }

                event.preventDefault();
                zoomImage(event.deltaY > 0 ? -0.2 : 0.2);
            }, { passive: false });

            stage.addEventListener('pointerdown', event => {
                if (activeImageScale <= 1) {
                    return;
                }

                isImageDragging = true;
                stage.setPointerCapture(event.pointerId);
                stage.classList.add('is-dragging');
                dragStartX = event.clientX;
                dragStartY = event.clientY;
                dragOriginX = activeImageTranslateX;
                dragOriginY = activeImageTranslateY;
            });

            stage.addEventListener('pointermove', event => {
                if (!isImageDragging) {
                    return;
                }

                activeImageTranslateX = dragOriginX + event.clientX - dragStartX;
                activeImageTranslateY = dragOriginY + event.clientY - dragStartY;
                applyImageTransform();
            });

            function stopDragging(event) {
                isImageDragging = false;
                if (event && stage.hasPointerCapture(event.pointerId)) {
                    stage.releasePointerCapture(event.pointerId);
                }
                stage.classList.remove('is-dragging');
            }

            stage.addEventListener('pointerup', stopDragging);
            stage.addEventListener('pointercancel', stopDragging);
            window.addEventListener('resize', applyImageTransform);
        });
    </script>

    <style>
        :root {
            --tm-blue: #024ddf;
            --ink: #111;
            --muted: #6f6f76;
            --line: #e8e8eb;
            --soft: #f5f5f6;
        }

        * {
            box-sizing: border-box;
        }

        body.ticket-page {
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            background: #fff;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        body.modal-open {
            overflow: hidden;
        }

        button {
            font: inherit;
        }

        .hidden {
            display: none !important;
        }

        .ticket-shell {
            width: 100%;
            min-height: 100vh;
            background: #fff;
            padding-bottom: 88px;
        }

        .event-header {
            background: #111;
            color: #fff;
        }

        .event-header__accent {
            height: 8px;
            background: var(--tm-blue);
        }

        .event-header__main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 20px 18px 22px;
        }

        .event-header h1 {
            margin: 0;
            overflow: hidden;
            font-size: clamp(22px, 4vw, 30px);
            font-weight: 800;
            line-height: 1.18;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .event-header p {
            margin: 14px 0 0;
            overflow: hidden;
            color: #efeff1;
            font-size: clamp(15px, 3vw, 22px);
            font-weight: 500;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .info-button {
            display: inline-flex;
            width: 52px;
            height: 52px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border: 2px solid #d7d7dc;
            border-radius: 999px;
            background: transparent;
            color: #fff;
            font-size: 24px;
        }

        .important-info {
            display: flex;
            align-items: center;
            gap: 6px;
            border-top: 1px solid #1d1d1f;
            padding: 18px;
            background: #181818;
            font-size: clamp(14px, 2.6vw, 19px);
            line-height: 1.25;
        }

        .important-info strong {
            flex: 0 0 auto;
            font-weight: 800;
        }

        .important-info span {
            min-width: 0;
            overflow: hidden;
            color: #f1f1f1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .important-info button {
            flex: 0 0 auto;
            border: 0;
            background: transparent;
            color: #fff;
            font-weight: 800;
            text-decoration: underline;
        }

        .map-panel {
            position: relative;
            display: grid;
            min-height: 280px;
            place-items: center;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
            background: #ededee;
        }

        .map-switch {
            position: absolute;
            top: 24px;
            left: 24px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #bdbdc2;
            border-radius: 999px;
            background: #fff;
            color: #222;
            padding: 14px 24px;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .map-switch i {
            font-size: 22px;
        }

        .map-image-button {
            width: min(54vw, 360px);
            max-width: 78%;
            border: 0;
            background: transparent;
            padding: 0;
            cursor: zoom-in;
        }

        .map-image-button img {
            display: block;
            width: 100%;
            max-height: 240px;
            object-fit: contain;
            filter: saturate(0.95);
        }

        .ticket-content {
            width: 100%;
            margin: 0 auto;
            background: #fff;
        }

        .ticket-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            padding: 22px 22px;
            border-bottom: 1px solid var(--line);
        }

        .select-pill,
        .filter-pill {
            display: inline-flex;
            min-height: 60px;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: 1px solid #b8b8bf;
            border-radius: 999px;
            background: #fff;
            color: #17171a;
            padding: 0 28px;
            font-size: 20px;
            font-weight: 800;
        }

        .select-pill {
            justify-content: space-between;
        }

        .filter-pill i {
            font-size: 20px;
        }

        .all-in-banner {
            display: flex;
            align-items: center;
            gap: 5px;
            min-height: 72px;
            border: 2px solid #d7ad23;
            border-left-width: 1px;
            border-right-width: 1px;
            padding: 0 22px;
            color: #686870;
            font-size: clamp(15px, 3.2vw, 20px);
            line-height: 1.25;
        }

        .all-in-banner strong {
            color: #5d5d64;
        }

        .paypal-strip {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 18px;
            min-height: 88px;
            padding: 0 28px;
            border-bottom: 7px solid var(--tm-blue);
            color: #111;
            font-size: clamp(16px, 3.4vw, 24px);
            font-weight: 800;
        }

        .paypal-wordmark {
            font-size: clamp(20px, 4vw, 28px);
            letter-spacing: -1px;
        }

        .paypal-strip a {
            color: #0759b8;
            font-size: clamp(15px, 3vw, 20px);
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .empty-state {
            margin: 18px;
            border: 1px solid #f0b5b5;
            border-radius: 8px;
            background: #fff6f6;
            color: #a12828;
            padding: 16px;
            font-size: 15px;
            font-weight: 700;
        }

        .ticket-list {
            background: #fff;
        }

        .ticket-row {
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        .ticket-row.is-open {
            background: #fbfbfc;
        }

        .ticket-row__main {
            display: grid;
            width: 100%;
            grid-template-columns: 124px minmax(0, 1fr) auto auto;
            align-items: center;
            gap: 22px;
            border: 0;
            background: transparent;
            padding: 22px 24px;
            text-align: left;
        }

        .ticket-thumb-wrap {
            position: relative;
            display: block;
            width: 124px;
            height: 72px;
            overflow: hidden;
            border-radius: 5px;
            background: #d8d8dc;
        }

        .ticket-thumb {
            position: relative;
            z-index: 2;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ticket-thumb-empty {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: #8b8b92;
            font-size: 22px;
        }

        .ticket-thumb-wrap:has(.ticket-thumb) .ticket-thumb-empty {
            display: none;
        }

        .ticket-thumb-wrap.image-load-failed .ticket-thumb-empty {
            display: grid;
        }

        .ticket-row__copy {
            min-width: 0;
        }

        .ticket-row__title {
            display: block;
            overflow: hidden;
            color: #151518;
            font-size: clamp(18px, 3.8vw, 25px);
            font-weight: 800;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ticket-row__type {
            display: block;
            margin-top: 8px;
            overflow: hidden;
            color: #74747b;
            font-size: clamp(16px, 3.3vw, 22px);
            font-weight: 500;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ticket-row__price {
            color: #0057b8;
            font-size: clamp(17px, 3.7vw, 23px);
            font-weight: 800;
            white-space: nowrap;
        }

        .ticket-row__chevron {
            color: #9898a0;
            font-size: 14px;
            transition: transform 0.18s ease;
        }

        .ticket-row__chevron.is-open {
            transform: rotate(180deg);
        }

        .seat-drawer {
            border-top: 1px solid var(--line);
            padding: 16px 24px 22px 170px;
        }

        .seat-drawer__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            color: #686870;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .seat-drawer__head button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #cfcfd5;
            border-radius: 999px;
            background: #fff;
            color: #0057b8;
            padding: 9px 13px;
            font-size: 12px;
            font-weight: 800;
            text-transform: none;
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
            gap: 10px;
        }

        .seat-btn {
            min-height: 42px;
            border: 1px solid #b9b9c0;
            border-radius: 999px;
            background: #fff;
            color: #222;
            font-size: 13px;
            font-weight: 800;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .seat-btn.is-selected {
            border-color: var(--tm-blue);
            background: var(--tm-blue);
            color: #fff;
        }

        .checkout-bar {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-top: 1px solid #d8d8dd;
            background: #fff;
            padding: 12px 18px;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.12);
        }

        .checkout-bar.is-disabled {
            pointer-events: none;
            opacity: 0;
            transform: translateY(100%);
        }

        .checkout-bar span {
            display: block;
            color: #696970;
            font-size: 12px;
            font-weight: 800;
        }

        .checkout-bar strong {
            display: block;
            margin-top: 2px;
            color: #111;
            font-size: 22px;
            font-weight: 900;
        }

        .checkout-bar button {
            min-height: 48px;
            border: 0;
            border-radius: 999px;
            background: var(--tm-blue);
            color: #fff;
            padding: 0 26px;
            font-size: 15px;
            font-weight: 900;
        }

        .image-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.94);
            padding: 14px;
        }

        .image-modal__panel {
            display: flex;
            width: 100%;
            height: 100%;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            background: #050505;
        }

        .image-modal__toolbar {
            display: flex;
            min-height: 62px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 10px 12px;
            color: #fff;
        }

        .image-modal__title {
            min-width: 0;
        }

        .image-modal__title h2 {
            margin: 0;
            overflow: hidden;
            font-size: 14px;
            font-weight: 900;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .image-modal__title p {
            margin: 3px 0 0;
            color: #b5b5bd;
            font-size: 12px;
            font-weight: 700;
        }

        .image-modal__tools {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-tool-btn {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .modal-tool-btn--close {
            background: #fff;
            color: #111;
        }

        .image-modal__stage {
            position: relative;
            display: flex;
            min-height: 0;
            flex: 1 1 auto;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #000;
            cursor: grab;
            touch-action: none;
        }

        .image-modal__stage.is-dragging {
            cursor: grabbing;
        }

        .image-modal__stage img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transform-origin: center center;
            user-select: none;
            will-change: transform;
        }

        @media (min-width: 760px) {
            .ticket-shell {
                max-width: 720px;
                margin: 0 auto;
                border-right: 1px solid var(--line);
                border-left: 1px solid var(--line);
                box-shadow: 0 0 30px rgba(0, 0, 0, 0.08);
            }

            .checkout-bar {
                right: 50%;
                left: 50%;
                width: 720px;
                transform: translateX(-50%);
            }

            .checkout-bar.is-disabled {
                transform: translateX(-50%) translateY(100%);
            }
        }

        @media (max-width: 560px) {
            .event-header__main {
                padding: 17px 18px 20px;
            }

            .info-button {
                width: 48px;
                height: 48px;
            }

            .map-panel {
                min-height: 280px;
            }

            .map-switch {
                top: 22px;
                left: 22px;
                padding: 13px 22px;
                font-size: 18px;
            }

            .ticket-controls {
                padding: 21px 22px;
            }

            .select-pill,
            .filter-pill {
                min-height: 58px;
                padding: 0 22px;
                font-size: 18px;
            }

            .ticket-row__main {
                grid-template-columns: 124px minmax(0, 1fr) auto;
                gap: 20px;
                padding: 22px 24px;
            }

            .ticket-row__chevron {
                display: none;
            }

            .seat-drawer {
                padding: 14px 24px 22px;
            }
        }

        @media (max-width: 430px) {
            .ticket-row__main {
                grid-template-columns: 96px minmax(0, 1fr) auto;
                gap: 14px;
                padding: 18px 18px;
            }

            .ticket-thumb-wrap {
                width: 96px;
                height: 58px;
            }

            .paypal-strip,
            .all-in-banner,
            .ticket-controls {
                padding-right: 18px;
                padding-left: 18px;
            }

            .select-pill,
            .filter-pill {
                padding: 0 18px;
                font-size: 16px;
            }

            .paypal-strip {
                gap: 10px;
            }
        }
    </style>
</body>
</html>
