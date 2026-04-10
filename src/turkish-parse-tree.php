<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../treestyle.css">
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
include 'tree-functions.php';
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
                <label for="penn20">Penn-Treebank-20</label><br>
                <input type="radio" id="penn15t" name="dataset" value="penn15t">
                <label for="penn15t">Penn-Treebank-15 Translated</label><br>
                <input type="radio" id="penn20t" name="dataset" value="penn20t">
                <label for="penn20t">Penn-Treebank-20 Translated</label>
            </td>
            <td><p>Display Layer:</p>
                <input type="radio" id="text" name="layer" value="text" checked="checked">
                <label for="text">Text</label><br>
                <input type="radio" id="morphology" name="layer" value="morphology">
                <label for="morphology">Morphology</label><br>
                <input type="radio" id="metamorpheme" name="layer" value="metamorpheme">
                <label for="metamorpheme">Metamorpheme</label><br>
                <input type="radio" id="semantics" name="layer" value="semantics">
                <label for="semantics">Semantics</label><br>
                <input type="radio" id="namedentity" name="layer" value="namedentity">
                <label for="namedentity">Named Entity</label><br>
                <input type="radio" id="propbank" name="layer" value="propbank">
                <label for="propbank">Propbank</label><br>
            </td>
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
    $parameter->layer = $_POST['layer'];
    $parameter->treebank = null;
    switch ($dataset) {
        case "penn15":
            $parameter->treebankdrawable = new TreeBankDrawable("../Penn-Treebank/Turkish2");
            $parameter->corpusName = "Penn-Treebank-15";
            echo create_parse_tree($parameter);
            break;
        case "penn20":
            $parameter->treebankdrawable = new TreeBankDrawable("../Penn-Treebank-20/Turkish2");
            $parameter->corpusName = "Penn-Treebank-20";
            echo create_parse_tree($parameter);
            break;
        case "penn15t":
            $parameter->treebankdrawable = new TreeBankDrawable("../Penn-Treebank/Turkish");
            $parameter->corpusName = "Penn-Treebank-15";
            echo create_parse_tree($parameter);
            break;
        case "penn20t":
            $parameter->treebankdrawable = new TreeBankDrawable("../Penn-Treebank-20/Turkish");
            $parameter->corpusName = "Penn-Treebank-20";
            echo create_parse_tree($parameter);
            break;
    }
}
?>
</body>
</html>