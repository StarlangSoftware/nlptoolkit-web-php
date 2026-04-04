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
    line{
        stroke:black;
        stroke-width:2
    }
</style>
<head>
    <meta charset="UTF-8">
    <title>English Parse Tree Datasets</title>
</head>
<body>
<?php

use olcaytaner\AnnotatedTree\TreeBankDrawable;
use olcaytaner\ParseTree\TreeBank;

require_once __DIR__ . '/../vendor/autoload.php';
set_time_limit(0);
ini_set('memory_limit', '4096M');
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <table>
        <tr><td><p>Search type:</p>
                <input type="radio" id="word" name="search_type" value="full">
                <label for="word">Full Match</label><br>
                <input type="radio" id="contains" name="search_type" value="contains" checked="checked">
                <label for="contains">Contains</label><br>
            <td><p>Search dataset:</p>
                <input type="radio" id="penn15" name="dataset" value="penn15" checked="checked">
                <label for="penn15">Penn-Treebank-15</label><br>
                <input type="radio" id="penn20" name="dataset" value="penn20">
                <label for="penn20">Penn-Treebank-20</label></td>
            <td><p>Display color:</p>
                <input type="radio" id="red" name="color" value="red" checked="checked">
                <label for="red"><span style="color: red;">Red</span></label><br>
                <input type="radio" id="green" name="color" value="green">
                <label for="green"><span style="color: green;">Green</span></label><br>
                <input type="radio" id="blue" name="color" value="blue">
                <label for="blue"><span style="color: blue;">Blue</span></label><br>
                <input type="radio" id="orange" name="color" value="orange">
                <label for="orange"><span style="color: orange;">Orange</span></label><br>
                <input type="radio" id="purple" name="color" value="purple">
                <label for="purple"><span style="color: violet;">Violet</span></label><br>
                <input type="radio" id="yellow" name="color" value="yellow">
                <label for="yellow"><span style="color: yellow;">Yellow</span></label><br>
                <input type="radio" id="purple" name="color" value="purple">
                <label for="purple"><span style="color: purple;">Purple</span></label><br>
            </td>
            <td><p>Display results:</p>
                <input type="radio" id="horizontal" name="display" value="horizontal" checked="checked">
                <label for="horizontal">Horizontal</label><br>
                <input type="radio" id="vertical" name="display" value="vertical">
                <label for="vertical">Vertical</label>
            </td></tr>
    </table>
    <label for="word">Search word:</label>
    <input type="text" id="word" name="word" size="100" required><br><br>
    <input type="submit" name="submit_word" value="Parse Trees">
</form>
<?php
if (isset($_POST['submit_word'])) {
    $parameter = new DisplayParameter();
    $parameter->word = $_POST['word'];
    $parameter->search_type = $_POST['search_type'];
    $dataset = $_POST['dataset'];
    $parameter->columnWise = $_POST['display'] == "vertical";
    $parameter->color = $_POST['color'];
    $parameter->treebankdrawable = null;
    switch ($dataset) {
        case "penn15":
            $parameter->treebank = new TreeBank("../Penn-Treebank/English");
            $parameter->corpusName = "Penn-Treebank-15";
            echo create_parse_tree($parameter);
            break;
        case "penn20":
            $parameter->treebank = new TreeBank("../Penn-Treebank-20/English");
            $parameter->corpusName = "Penn-Treebank-20";
            echo create_parse_tree($parameter);
            break;
    }
}
?>
</body>
</html>