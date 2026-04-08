<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../treestyle.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Universal Dependency Datasets</title>
</head>
<body>
<?php

use olcaytaner\AnnotatedSentence\AnnotatedCorpus;

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
                <input type="radio" id="root" name="search_type" value="root">
                <label for="root">Root Match</label><br>
                <input type="radio" id="contains" name="search_type" value="contains" checked="checked">
                <label for="contains">Contains</label><br>
                <input type="radio" id="udtag" name="search_type" value="udtag">
                <label for="udtag">Tag</label></td>
            <td><p>Search dataset:</p>
                <input type="radio" id="atis" name="dataset" value="atis" checked="checked">
                <label for="atis">Atis</label><br>
        </tr>
    </table>
    <label for="word">Search word:</label>
    <input type="text" id="word" name="word" size="100" required><br><br>
    <input type="submit" name="submit_word" value="Universal Dependencies">
</form>
<?php
if (isset($_POST['submit_word'])) {
    $parameter = new DisplayParameter();
    $parameter->word = $_POST['word'];
    $parameter->search_type = $_POST['search_type'];
    $dataset = $_POST['dataset'];
    switch ($dataset) {
        case "atis":
            $parameter->corpus = new AnnotatedCorpus("../Atis/English-Phrase");
            $parameter->corpusName = "Atis";
            echo create_ud_table($parameter);
            break;
    }
}
?>
</body>
</html>