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
set_time_limit(0);
ini_set('memory_limit', '2048M');
use olcaytaner\WordNet\WordNet;
$wordNetCache = "wordnet2.cache";
if (file_exists($wordNetCache)) {
    $englishWordNet = unserialize(file_get_contents($wordNetCache));
} else {
    $englishWordNet = new WordNet("../english_wordnet_version_31.xml");
    file_put_contents($wordNetCache, serialize($englishWordNet));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="english_word_net_word">Word: (thing, walk, nice, ...)</label>
    <input type="text" id="english_word_net_word" name="english_word_net_word" required><br><br>
    <input type="submit" name="submit_english_wordnet_word_search" value="Find Word">
</form>
<?php
if (isset($_POST['submit_english_wordnet_word_search'])) {
    $word = htmlspecialchars($_POST['english_word_net_word']);
    echo create_table_for_word_search($word, $englishWordNet);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="english_word_net_synonym">Word: (black, walk, wonderful, ...)</label>
    <input type="text" id="english_word_net_synonym" name="english_word_net_synonym" required><br><br>
    <input type="submit" name="submit_english_wordnet_synonym_search" value="Find Synonym">
</form>
<?php
if (isset($_POST['submit_english_wordnet_synonym_search'])) {
    $word = htmlspecialchars($_POST['english_word_net_synonym']);
    echo create_table_for_synonym_search($word, $englishWordNet);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="english_word_net_synset_id">SynSet Id: (ENG31-00001740-n, ENG31-00003316-v, ENG31-00003699-s, ...)</label>
    <input type="text" id="english_word_net_synset_id" name="english_word_net_synset_id" required><br><br>
    <input type="submit" name="submit_submit_english_wordnet_id_search" value="Find SynSet">
</form>
<?php
if (isset($_POST['submit_submit_english_wordnet_id_search'])) {
    $id = htmlspecialchars($_POST['english_word_net_synset_id']);
    echo create_table_for_id_search($id, $englishWordNet);
}
?>
</html>