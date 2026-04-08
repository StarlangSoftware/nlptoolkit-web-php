<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Deasciifier</title>
</head>
<body>
<?php

use olcaytaner\Corpus\Sentence;
use olcaytaner\Deasciifier\SimpleDeasciifier;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
$fsmCache = "fsm.cache";
if (file_exists($fsmCache)) {
    $fsm = unserialize(file_get_contents($fsmCache));
} else {
    $fsm = new FsmMorphologicalAnalyzer();
    file_put_contents($fsmCache, serialize($fsm));
}
$deasciifierCache = "deasciifier.cache";
if (file_exists($deasciifierCache)) {
    $deasciifier = unserialize(file_get_contents($deasciifierCache));
} else {
    $deasciifier = new SimpleDeasciifier($fsm);
    file_put_contents($deasciifierCache, serialize($deasciifier));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="sentence">Sentence: (sogus eti aldik)</label>
    <input type="text" id="sentence" name="sentence" size="100" required><br><br>
    <input type="submit" name="submit_deasciifier" value="Deasciifier">
</form>
<?php
if (isset($_POST['submit_deasciifier'])) {
    $sentence = htmlspecialchars($_POST['sentence']);
    echo $deasciifier->deasciify(new Sentence($sentence))->toWords();
}
?>
</body>
</html>