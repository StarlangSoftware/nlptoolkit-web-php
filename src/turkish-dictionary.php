<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Dictionary</title>
</head>
<body>
<?php

use olcaytaner\Dictionary\Dictionary\TxtDictionary;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
$dictionaryCache = "dictionary.cache";
if (file_exists($dictionaryCache)) {
    $dictionary = unserialize(file_get_contents($dictionaryCache));
} else {
    $dictionary = new TxtDictionary();
    file_put_contents($dictionaryCache, serialize($dictionary));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="dictionary_search_word">Word: (git, ev, güzel, Ali, ...)</label>
    <input type="text" id="dictionary_search_word" name="dictionary_search_word" required><br><br>
    <input type="submit" name="word_search" value="Find Word">
</form>
<?php
if (isset($_POST['word_search'])) {
    $word = htmlspecialchars($_POST['dictionary_search_word']);
    echo display_word_properties($dictionary, $word);
}
?>
</body>
</html>