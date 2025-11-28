<?php
// /admin/debug/index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Debug Tools</title>
<style>
    body {
        background: #0b1020;
        color: #f9fafc;
        font-family: system-ui, sans-serif;
        padding: 24px;
    }
    h1 { margin-bottom: 12px; }

    .tool-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }
    .tool a {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        background: #141b2d;
        border: 1px solid #2a3550;
        padding: 14px 16px;
        border-radius: 12px;
        transition: background 0.15s, transform 0.15s;
    }
    .tool a:hover {
        background: #1c2640;
        transform: translateY(-2px);
    }
    .tool span {
        color: #dbe0f5;
        font-size: 0.95rem;
        font-weight: 500;
    }
    .icon {
        font-size: 1.35rem;
        width: 28px;
        text-align: center;
    }

    /* Colour-coded icons */
    .icon-blue { color: #63a4ff; }
    .icon-green { color: #5dd39e; }
    .icon-orange { color: #ffb366; }
    .icon-red { color: #ff6674; }
</style>
</head>
<body>

<h1>Debug Tools</h1>
<p>Choose a diagnostic tool to run:</p>

<div class="tool-list">

    <div class="tool">
        <a href="list.php">
            <div class="icon icon-blue">📄</div>
            <span>List all files</span>
        </a>
    </div>

    <div class="tool">
        <a href="site_map.php">
            <div class="icon icon-green">🗂️</div>
            <span>Site folder map</span>
        </a>
    </div>

    <div class="tool">
        <a href="compare_root.php">
            <div class="icon icon-red">🧬</div>
            <span>Detect duplicate site trees</span>
        </a>
    </div>

    <div class="tool">
        <a href="path_trace.php">
            <div class="icon icon-orange">🧭</div>
            <span>Trace include paths</span>
        </a>
    </div>

    <div class="tool">
        <a href="env_report.php">
            <div class="icon icon-blue">🔐</div>
            <span>.env & config report</span>
        </a>
    </div>

    <div class="tool">
        <a href="php-info.php">
            <div class="icon icon-green">⚙️</div>
            <span>phpinfo()</span>
        </a>
    </div>

</div>

</body>
</html>

