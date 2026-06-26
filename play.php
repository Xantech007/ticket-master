<?php
include "inc/header.php";

/* GET GAME SLUG */
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    echo "<h2 style='text-align:center;margin-top:50px;'>Game not found</h2>";
    exit;
}

$slug = htmlspecialchars($_GET['slug']);
?>

<style>
body {
    margin: 0;
    background: #000;
    overflow: hidden;
}

.game-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Top bar */
.top-bar {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
}

.back-btn {
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
}
</style>

<div class="game-container">

    <div class="top-bar">
        <a href="games.php" class="back-btn">⬅ Back</a>
    </div>

    <!-- CRAZYGAMES EMBED -->
    <iframe 
        src="https://www.crazygames.com/embed/<?php echo $slug; ?>" 
        allow="gamepad *; fullscreen *"
        allowfullscreen>
    </iframe>

</div>
