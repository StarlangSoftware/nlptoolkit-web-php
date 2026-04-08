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
    <label for="frame_name">Frame name:</label>
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
    <label for="verb_name">Verb:</label>
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
    <label for="verb_id">Verb SynSet Id:</label>
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