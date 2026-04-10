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
include 'ud-functions.php';
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
                <input type="radio" id="boun" name="dataset" value="boun">
                <label for="boun">Boun</label><br>
                <input type="radio" id="tourism" name="dataset" value="tourism">
                <label for="tourism">Tourism</label><br>
                <input type="radio" id="framenet" name="dataset" value="framenet">
                <label for="framenet">Framenet</label><br>
                <input type="radio" id="gb" name="dataset" value="gb">
                <label for="gb">Gb</label><br>
                <input type="radio" id="imst" name="dataset" value="imst">
                <label for="imst">Imst</label><br>
                <input type="radio" id="kenet" name="dataset" value="kenet">
                <label for="kenet">Kenet</label><br>
                <input type="radio" id="penn" name="dataset" value="penn">
                <label for="penn">Penn</label><br>
                <input type="radio" id="pud" name="dataset" value="pud">
                <label for="pud">Pud</label></td>
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
            $parameter->corpus = new AnnotatedCorpus("../Atis/Turkish-Phrase");
            $parameter->corpusName = "Atis";
            echo create_ud_table($parameter);
            break;
        case "boun":
            $parameter->corpus = new AnnotatedCorpus("../Boun/Turkish-Phrase");
            $parameter->corpusName = "Boun";
            echo create_ud_table($parameter);
            break;
        case "tourism":
            $parameter->corpus = new AnnotatedCorpus("../Etstur/Turkish-Phrase");
            $parameter->corpusName = "Tourism";
            echo create_ud_table($parameter);
            break;
        case "framenet":
            $parameter->corpus = create_merged_corpus("../FrameNet-Examples/Turkish-Phrase");
            $parameter->corpusName = "Framenet";
            echo create_ud_table($parameter);
            break;
        case "gb":
            $parameter->corpus = new AnnotatedCorpus("../Gb/Turkish-Phrase");
            $parameter->corpusName = "Gb";
            echo create_ud_table($parameter);
            break;
        case "imst":
            $parameter->corpus = new AnnotatedCorpus("../Imst/Turkish-Phrase");
            $parameter->corpusName = "Imst";
            echo create_ud_table($parameter);
            break;
        case "kenet":
            $parameter->corpus = new AnnotatedCorpus("../Kenet-Examples/Turkish-Phrase");
            $parameter->corpusName = "Kenet";
            echo create_ud_table($parameter);
            break;
        case "penn":
            $parameter->corpus = new AnnotatedCorpus("../Penn-Treebank/Turkish-Phrase");
            $parameter->corpusName = "Penn-Treebank-15";
            echo create_ud_table($parameter);
            $parameter->corpus = new AnnotatedCorpus("../Penn-Treebank-20/Turkish-Phrase");
            $parameter->corpusName = "Penn-Treebank-20";
            echo create_ud_table($parameter);
            break;
        case "pud":
            $parameter->corpus = new AnnotatedCorpus("../Pud/Turkish-Phrase");
            $parameter->corpusName = "Pud";
            echo create_ud_table($parameter);
            break;
    }
}
?>
</body>
</html>