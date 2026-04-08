<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish WordNet</title>
</head>
<body>
<?php
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
use olcaytaner\WordNet\WordNet;
$wordNetCache = "wordnet1.cache";
if (file_exists($wordNetCache)) {
    $turkishWordNet = unserialize(file_get_contents($wordNetCache));
} else {
    $turkishWordNet = new WordNet();
    file_put_contents($wordNetCache, serialize($turkishWordNet));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="turkish_word_net_word">Word: (gitmek, güzel, ev, almak, ...)</label>
    <input type="text" id="turkish_word_net_word" name="turkish_word_net_word" required><br><br>
    <input type="submit" name="submit_turkish_wordnet_word_search" value="Find Word">
</form>
<?php
if (isset($_POST['submit_turkish_wordnet_word_search'])) {
    $word = htmlspecialchars($_POST['turkish_word_net_word']);
    echo create_table_for_word_search($word, $turkishWordNet);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="turkish_word_net_synonym">Word: (gitmek, güzel, ev, almak, ...)</label>
    <input type="text" id="turkish_word_net_synonym" name="turkish_word_net_synonym" required><br><br>
    <input type="submit" name="submit_turkish_wordnet_synonym_search" value="Find Synonym">
</form>
<?php
if (isset($_POST['submit_turkish_wordnet_synonym_search'])) {
    $word = htmlspecialchars($_POST['turkish_word_net_synonym']);
    echo create_table_for_synonym_search($word, $turkishWordNet);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="turkish_word_net_synset_id">SynSet Id: (TUR10-0000006, TUR10-0452300, TUR10-0644430, ...)</label>
    <input type="text" id="turkish_word_net_synset_id" name="turkish_word_net_synset_id" required><br><br>
    <input type="submit" name="submit_submit_turkish_wordnet_id_search" value="Find SynSet">
</form>
<?php
if (isset($_POST['submit_submit_turkish_wordnet_id_search'])) {
    $id = htmlspecialchars($_POST['turkish_word_net_synset_id']);
    echo create_table_for_id_search($id, $turkishWordNet);
}
?>
</html>