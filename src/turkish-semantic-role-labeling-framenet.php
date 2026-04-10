<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Morphological Disambiguation Datasets</title>
</head>
<body>
<?php

use olcaytaner\AnnotatedSentence\AnnotatedCorpus;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '4096M');
set_time_limit(0);
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <table>
        <tr><td><p>Search type:</p>
                <input type="radio" id="word" name="search_type" value="full">
                <label for="word">Full Match</label><br>
                <input type="radio" id="root" name="search_type" value="root">
                <label for="root">Root Match</label><br>
                <input type="radio" id="contains" name="search_type" value="contains" checked="checked">
                <label for="contains">Contains</label><br>
                <input type="radio" id="predicate_sense_framenet" name="search_type" value="predicate_sense_framenet">
                <label for="predicate_sense_framenet">Predicate Sense Id</label><br>
                <input type="radio" id="frame" name="search_type" value="frame">
                <label for="frame">Frame</label><br>
                <input type="radio" id="frame_element" name="search_type" value="frame_element">
                <label for="frame_element">Frame Element</label></td>
            <td><p>Search dataset:</p>
                <input type="radio" id="atis" name="dataset" value="atis" checked="checked">
                <label for="atis">Atis</label><br>
                <input type="radio" id="tourism" name="dataset" value="tourism">
                <label for="tourism">Tourism</label><br>
                <input type="radio" id="kenet" name="dataset" value="kenet">
                <label for="kenet">Kenet</label><br>
                <input type="radio" id="penn" name="dataset" value="penn">
                <label for="penn">Penn</label><br>
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
                <input type="radio" id="column" name="display" value="column">
                <label for="column">Column</label><br>
                <input type="radio" id="row" name="display" value="row" checked="checked">
                <label for="row">Row</label>
            </td></tr>
    </table>
    <label for="word">Search word:</label>
    <input type="text" id="word" name="word" size="100" required><br><br>
    <input type="submit" name="submit_word" value="Framenet Annotation">
</form>
<?php
function create_framenet_table(DisplayParameter $parameter): string{
    $parameter->field_name = "framenet";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

if (isset($_POST['submit_word'])) {
    $parameter = new DisplayParameter();
    $parameter->word = $_POST['word'];
    $parameter->search_type = $_POST['search_type'];
    $dataset = $_POST['dataset'];
    $parameter->columnWise = $_POST['display'] == "column";
    $parameter->color = $_POST['color'];
    switch ($dataset) {
        case "atis":
            $parameter->corpus = new AnnotatedCorpus("../Atis/Turkish-Phrase");
            $parameter->corpusName = "Atis";
            echo create_framenet_table($parameter);
            break;
        case "tourism":
            $parameter->corpus = new AnnotatedCorpus("../Etstur/Turkish-Phrase");
            $parameter->corpusName = "Tourism";
            echo create_framenet_table($parameter);
            break;
        case "kenet":
            $parameter->corpus = new AnnotatedCorpus("../Kenet-Examples/Turkish-Phrase");
            $parameter->corpusName = "Kenet";
            echo create_framenet_table($parameter);
            break;
        case "penn":
            $parameter->corpus = new AnnotatedCorpus("../Penn-Treebank/Turkish-Phrase");
            $parameter->corpusName = "Penn-Treebank-15";
            echo create_framenet_table($parameter);
            $parameter->corpus = new AnnotatedCorpus("../Penn-Treebank-20/Turkish-Phrase");
            $parameter->corpusName = "Penn-Treebank-20";
            echo create_framenet_table($parameter);
            break;
    }
}
?>
</body>
</html>