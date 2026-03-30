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

    .container {
        background: white;
        max-width: 900px;
        margin: auto;
        padding: 2rem 2.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
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
    .badge {
        display: inline-block;
        background: #4f7cff;
        color: white;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 999px;
        margin-left: 0.5rem;
        vertical-align: middle;
    }
</style>
<head>
    <meta charset="UTF-8">
    <title>Turkish Morphological Disambiguation Datasets</title>
</head>
<body>
<?php

use olcaytaner\AnnotatedSentence\AnnotatedCorpus;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '4096M');
$atis = new AnnotatedCorpus("../Atis/Turkish-Phrase");
$boun = new AnnotatedCorpus("../Boun/Turkish-Phrase");
$tourism = new AnnotatedCorpus("../Etstur/Turkish-Phrase");
$gb = new AnnotatedCorpus("../Gb/Turkish-Phrase");
$imst = new AnnotatedCorpus("../Imst/Turkish-Phrase");
$kenet = new AnnotatedCorpus("../Kenet-Examples/Turkish-Phrase");
$penn15 = new AnnotatedCorpus("../Penn-Treebank/Turkish-Phrase");
$penn20 = new AnnotatedCorpus("../Penn-Treebank-20/Turkish-Phrase");
$pud = new AnnotatedCorpus("../Pud/Turkish-Phrase");
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="word">Search word:</label>
    <input type="text" id="word" name="word" size="100" required><br><br>
    <input type="submit" name="submit_word" value="Morphological Disambiguation">
</form>
<?php
if (isset($_POST['submit_word'])) {
    $word = $_POST['word'];
    echo create_morphology_table("Atis", $atis, $word);
    echo create_morphology_table("Boun", $boun, $word);
    echo create_morphology_table("Tourism", $tourism, $word);
    echo create_morphology_table("Gb", $gb, $word);
    echo create_morphology_table("Imst", $imst, $word);
    echo create_morphology_table("Kenet", $kenet, $word);
    echo create_morphology_table("Penn-Treebank-15", $penn15, $word);
    echo create_morphology_table("Penn-Treebank-20", $penn20, $word);
    echo create_morphology_table("Pud", $pud, $word);
}
?>
</body>
</html>