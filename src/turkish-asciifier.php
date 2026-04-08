<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
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