<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish SentiNet</title>
</head>
<body>
<?php

use olcaytaner\Sentinet\PolarityType;
use olcaytaner\Sentinet\SentiLiteralNet;
use olcaytaner\Sentinet\SentiNet;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
$sentiNetCache = "sentinet.cache";
if (file_exists($sentiNetCache)) {
    $sentiNet = unserialize(file_get_contents($sentiNetCache));
} else {
    $sentiNet = new SentiNet();
    file_put_contents($sentiNetCache, serialize($sentiNet));
}
$sentiLiteralNetCache = "sentiliteralnet.cache";
if (file_exists($sentiLiteralNetCache)) {
    $sentiLiteralNet = unserialize(file_get_contents($sentiLiteralNetCache));
} else {
    $sentiLiteralNet = new SentiLiteralNet();
    file_put_contents($sentiLiteralNetCache, serialize($sentiLiteralNet));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="senti_net_word">Word: (güzel, çirkin, kötü, mükemmel, ...)</label>
    <input type="text" id="senti_net_word" name="senti_net_word" required><br><br>
    <input type="submit" name="submit_sentinet_word_search" value="Find Word Sense">
</form>
<?php
if (isset($_POST['submit_sentinet_word_search'])) {
    $word = htmlspecialchars($_POST['senti_net_word']);
    $sentiLiteral = $sentiLiteralNet->getSentiLiteral($word);
    if ($sentiLiteral != null) {
        if ($sentiLiteral->getPolarity() == PolarityType::POSITIVE) {
            echo "POSITIVE";
        } elseif ($sentiLiteral->getPolarity() == PolarityType::NEGATIVE) {
            echo "NEGATIVE";
        } elseif ($sentiLiteral->getPolarity() == PolarityType::NEUTRAL) {
            echo "NEUTRAL";
        }
    }
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="senti_net_synset_id">SynSet Id: (TUR10-0969360, TUR10-0019400, TUR10-0867930, ...)</label>
    <input type="text" id="senti_net_synset_id" name="senti_net_synset_id" required><br><br>
    <input type="submit" name="submit_sentinet_id_search" value="Find Sense">
</form>
<?php
if (isset($_POST['submit_sentinet_id_search'])) {
    $id = htmlspecialchars($_POST['senti_net_synset_id']);
    $sentiSynSet = $sentiNet->getSentiSynSet($id);
    if ($sentiSynSet != null) {
        if ($sentiSynSet->getPolarity() == PolarityType::POSITIVE) {
            echo "POSITIVE";
        } elseif ($sentiSynSet->getPolarity() == PolarityType::NEGATIVE) {
            echo "NEGATIVE";
        } elseif ($sentiSynSet->getPolarity() == PolarityType::NEUTRAL) {
            echo "NEUTRAL";
        }
    }
}
?>
</body>
</html>