<?php
// ======================================================================
// SPORTS WAREHOUSE — DATABASE CONNECTION TESTER (Admin Tool)
// Uses the unified db.php and environment variables
// ======================================================================

require_once __DIR__ . "/_layout.php";  // Admin layout (header/sidebar)
require_once __DIR__ . "/../inc/env.php"; // Environment loader

// Detect environment
$appEnv = sw_env('APP_ENV', 'local');

// ----------------------------------------------------------------------
// Run DB test if user submitted the form
// ----------------------------------------------------------------------
$resultMessage = null;
$isSuccess = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Overwrite DB_PASS dynamically, without hardcoding it
    $inputPassword = trim($_POST["password"] ?? "");

    putenv("DB_PASS=$inputPassword");

    try {
        // Load db.php — this will use the updated env variables
        require __DIR__ . "/../db.php";

        // Optional: simple query to confirm connection works fully
        $stmt = $pdo->query("SELECT DATABASE() AS dbname");
        $row = $stmt->fetch();

        $isSuccess = true;
        $resultMessage = "SUCCESS — Connected to database: <strong>" .
                         htmlspecialchars($row['dbname']) .
                         "</strong>";

    } catch (Throwable $e) {
        $isSuccess = false;
        $resultMessage = "FAILED — Incorrect password or connection issue.";
        if (sw_env('SW_DEBUG', '0') == '1') {
            $resultMessage .= "<br><small>Error: " . htmlspecialchars($e->getMessage()) . "</small>";
        }
    }
}

// ----------------------------------------------------------------------
// PAGE LAYOUT
// ----------------------------------------------------------------------
admin_layout_start("DB Connection Tester");
?>

<div class="admin-wrapper">
    <?php
    admin_header(
        "Database Connection Tester",
        "Enter a password to attempt a live connection using the environment variables."
    );
    ?>

    <section class="card" style="padding:20px; max-width:480px;">
        <form method="post">

            <label style="display:block; margin-bottom:6px; font-size:0.9rem;">
                Enter MySQL Password (not saved):
            </label>

            <input 
                type="password"
                name="password"
                class="form-input"
                placeholder="Enter MySQL password"
                required
                style="
                    width:100%;
                    padding:10px;
                    border-radius:8px;
                    border:1px solid #333;
                    background:#0f172a;
                    color:white;
                    margin-bottom:14px;
                "
            >

            <button class="btn btn-primary" type="submit">
                <span class="btn__dot"></span>
                Test Connection
            </button>
        </form>
    </section>

    <?php if ($resultMessage !== null): ?>
        <div class="flash flash--<?= $isSuccess ? "success" : "error" ?> mt-3">
            <span class="flash__pill"></span>
            <?= $resultMessage ?>
        </div>
    <?php endif; ?>

</div>

<?php admin_layout_end(); ?>

