<?php
// admin/task-reset.php
require_once __DIR__ . '/inc/header.php';

$success = '';
$error = '';

/* UPDATE RESET TIME */
if (isset($_POST['update_reset_time'])) {

    $reset_time = trim($_POST['reset_time']);

    if (!empty($reset_time)) {

        try {

            $stmt = $pdo->prepare("UPDATE task_reset SET reset_time=? LIMIT 1");
            $stmt->execute([$reset_time]);

            $success = "Task reset time updated successfully.";

        } catch (PDOException $e) {

            $error = "Error: " . $e->getMessage();
        }

    } else {

        $error = "Please select a valid date and time.";
    }
}

/* FETCH CURRENT RESET TIME */
try {

    $stmt = $pdo->query("SELECT reset_time FROM task_reset LIMIT 1");
    $taskReset = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentResetTime = $taskReset['reset_time'] ?? '';

} catch (PDOException $e) {

    $currentResetTime = '';
    $error = "Error fetching reset time.";
}
?>

<main style="margin-top: 1.5rem;">

    <h1 style="
        text-align:center;
        margin-bottom:2.5rem;
        font-size:2.1rem;
    ">
        Task Reset Settings
    </h1>

    <div style="
        max-width:550px;
        margin:0 auto;
        background:#161b22;
        padding:1.8rem;
        border-radius:12px;
        border:1px solid #30363d;
    ">

        <h2 style="
            text-align:center;
            margin-bottom:1.5rem;
        ">
            Update Task Reset Time
        </h2>

        <?php if (!empty($success)): ?>
            <div style="
                background:#238636;
                color:white;
                padding:12px;
                border-radius:8px;
                margin-bottom:1rem;
                text-align:center;
            ">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="
                background:#f85149;
                color:white;
                padding:12px;
                border-radius:8px;
                margin-bottom:1rem;
                text-align:center;
            ">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label style="
                display:block;
                margin-bottom:10px;
                font-size:15px;
                color:#c9d1d9;
            ">
                Select Reset Date & Time
            </label>

            <input
                type="datetime-local"
                name="reset_time"
                value="<?= !empty($currentResetTime) ? date('Y-m-d\TH:i', strtotime($currentResetTime)) : '' ?>"
                required
                style="
                    width:100%;
                    padding:14px;
                    border-radius:8px;
                    border:1px solid #30363d;
                    background:#0d1117;
                    color:white;
                    margin-bottom:1.2rem;
                    font-size:15px;
                "
            >

            <button
                type="submit"
                name="update_reset_time"
                style="
                    width:100%;
                    padding:14px;
                    border:none;
                    border-radius:8px;
                    background:#1f6feb;
                    color:white;
                    font-size:15px;
                    cursor:pointer;
                    font-weight:bold;
                "
            >
                Update Reset Time
            </button>

        </form>

    </div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
