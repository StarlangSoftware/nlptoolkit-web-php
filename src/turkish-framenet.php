<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish FrameNet</title>
</head>
<body>
<?php
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
use olcaytaner\Framenet\FrameNet;
use olcaytaner\WordNet\WordNet;
$wordNetCache = "wordnet1.cache";
if (file_exists($wordNetCache)) {
    $turkishWordNet = unserialize(file_get_contents($wordNetCache));
} else {
    $turkishWordNet = new WordNet();
    file_put_contents($wordNetCache, serialize($turkishWordNet));
}
$framenetCache = "framenet.cache";
if (file_exists($framenetCache)) {
    $frameNet = unserialize(file_get_contents($framenetCache));
} else {
    $frameNet = new FrameNet();
    file_put_contents($framenetCache, serialize($frameNet));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="frame_name">Frame name: (Abandoned_State, Duplication, Judgement, ...)</label>
    <input type="text" id="frame_name" name="frame_name" required><br><br>
    <input type="submit" name="submit_frame_search" value="Find Frame">
</form>
<?php
if (isset($_POST['submit_frame_search'])) {
    $frame_name = htmlspecialchars($_POST['frame_name']);
    echo create_frame_table($turkishWordNet, $frameNet, $frame_name);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="verb_name">Verb: (gitmek, koşmak, yürümek, atmak, ...)</label>
    <input type="text" id="verb_name" name="verb_name" required><br><br>
    <input type="submit" name="framenet_verb_search" value="Find Verb">
</form>
<?php
if (isset($_POST['framenet_verb_search'])) {
    $verb_name = htmlspecialchars($_POST['verb_name']);
    $synsets = $turkishWordNet->getSynSetsWithLiteral($verb_name);
    $frames = get_frames_for_synsets($frameNet, $synsets);
    echo create_table_of_frames($turkishWordNet, $frames);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="verb_id">Verb SynSet Id: (TUR10-0884740, TUR10-0596810, TUR10-0715300, ...)</label>
    <input type="text" id="verb_id" name="verb_id" required><br><br>
    <input type="submit" name="framenet_id_search" value="Find Verb">
</form>
<?php
if (isset($_POST['framenet_id_search'])) {
    $verb_id = htmlspecialchars($_POST['verb_id']);
    $frames = $frameNet->getFrames($verb_id);
    echo create_table_of_frames($turkishWordNet, $frames);
}
?>
</body>
</html>