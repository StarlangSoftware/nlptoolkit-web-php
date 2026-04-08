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
    <label for="turkish_word_net_word">Word:</label>
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
    <label for="turkish_word_net_synonym">Word:</label>
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
    <label for="turkish_word_net_synset_id">SynSet Id:</label>
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