<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<style>
    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: linear-gradient(180deg, #f4f6f8, #eaeef3);
        margin: 0;
        padding: 2rem 1rem;
        color: #222;
    }

    a {
        color: #4f7cff;
        font-weight: 500;
        text-decoration: none;
    }

    a::after {
        content: " →";
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    li:hover a::after {
        opacity: 1;
    }
    a:hover {
        text-decoration: underline;
    }
    ul {
        list-style: none;
        padding: 0;
        margin-top: 1rem;
    }

    li {
        padding: 0.75rem 1rem;
        margin-bottom: 0.6rem;
        background: #f7f9fc;
        border-radius: 8px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    li:hover {
        background: #eef3ff;
        transform: translateX(4px);
    }
    h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    h2 {
        margin-top: 2rem;
        font-size: 1.3rem;
        color: #333;
        border-left: 4px solid #4f7cff;
        padding-left: 0.6rem;
    }

    p {
        color: #555;
    }
</style>
<head>
    <meta charset="UTF-8">
    <title>Turkish Asciifier</title>
</head>
<body>
<?php

use olcaytaner\Corpus\Sentence;
use olcaytaner\Deasciifier\SimpleAsciifier;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
$asciifierCache = "asciifier.cache";
if (file_exists($asciifierCache)) {
    $asciifier = unserialize(file_get_contents($asciifierCache));
} else {
    $asciifier = new SimpleAsciifier();
    file_put_contents($asciifierCache, serialize($asciifier));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="sentence">Sentence:</label>
    <input type="text" id="sentence" name="sentence" size="100" required><br><br>
    <input type="submit" name="submit_asciifier" value="Asciifier">
</form>
<?php
if (isset($_POST['submit_asciifier'])) {
    $sentence = htmlspecialchars($_POST['sentence']);
    echo $asciifier->asciify(new Sentence($sentence))->toWords();
}
?>
</body>
</html>