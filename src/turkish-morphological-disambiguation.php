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
$atiscache = "atis.cache";
if (file_exists($atiscache)) {
    $atis = unserialize(file_get_contents($atiscache));
} else {
    $atis = new AnnotatedCorpus("../Atis/Turkish-Phrase");
    file_put_contents($atiscache, serialize($atis));
}
$bouncache = "boun.cache";
if (file_exists($bouncache)) {
    $boun = unserialize(file_get_contents($bouncache));
} else {
    $boun = new AnnotatedCorpus("../Boun/Turkish-Phrase");
    file_put_contents($bouncache, serialize($boun));
}
$tourismcache = "tourism.cache";
if (file_exists($tourismcache)) {
    $tourism = unserialize(file_get_contents($tourismcache));
} else {
    $tourism = new AnnotatedCorpus("../Etstur/Turkish-Phrase");
    file_put_contents($tourismcache, serialize($tourism));
}
$gbcache = "gb.cache";
if (file_exists($gbcache)) {
    $gb = unserialize(file_get_contents($gbcache));
} else {
    $gb = new AnnotatedCorpus("../Gb/Turkish-Phrase");
    file_put_contents($gbcache, serialize($gb));
}
$imstcache = "imst.cache";
if (file_exists($imstcache)) {
    $imst = unserialize(file_get_contents($imstcache));
} else {
    $imst = new AnnotatedCorpus("../Imst/Turkish-Phrase");
    file_put_contents($imstcache, serialize($imst));
}
$kenetcache = "kenet.cache";
if (file_exists($kenetcache)) {
    $kenet = unserialize(file_get_contents($kenetcache));
} else {
    $kenet = new AnnotatedCorpus("../Kenet-Examples/Turkish-Phrase");
    file_put_contents($kenetcache, serialize($kenet));
}
$penn15cache = "penn15.cache";
if (file_exists($penn15cache)) {
    $penn15 = unserialize(file_get_contents($penn15cache));
} else {
    $penn15 = new AnnotatedCorpus("../Penn-Treebank/Turkish-Phrase");
    file_put_contents($penn15cache, serialize($penn15));
}
$penn20cache = "penn20.cache";
if (file_exists($penn20cache)) {
    $penn20 = unserialize(file_get_contents($penn20cache));
} else {
    $penn20 = new AnnotatedCorpus("../Penn-Treebank-20/Turkish-Phrase");
    file_put_contents($penn20cache, serialize($penn20));
}
$pudcache = "pud.cache";
if (file_exists($pudcache)) {
    $pud = unserialize(file_get_contents($pudcache));
} else {
    $pud = new AnnotatedCorpus("../Pud/Turkish-Phrase");
    file_put_contents($pudcache, serialize($pud));
}
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