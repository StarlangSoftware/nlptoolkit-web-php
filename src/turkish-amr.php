<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../treestyle.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Amr Datasets</title>
</head>
<body>
<?php

use olcaytaner\Amr\Corpus\AmrCorpus;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '4096M');
set_time_limit(0);
include 'amr-functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <table>
        <tr><td><p>Search type:</p>
                <input type="radio" id="word" name="search_type" value="full">
                <label for="word">Full Match</label><br>
                <input type="radio" id="contains" name="search_type" value="contains" checked="checked">
                <label for="contains">Contains</label><br>
            </td>
            <td><p>Search dataset:</p>
                <input type="radio" id="gb" name="dataset" value="gb" checked="checked">
                <label for="gb">gb</label><br>
                <input type="radio" id="tourism" name="dataset" value="tourism">
                <label for="tourism">tourism</label><br>
                <input type="radio" id="framenet" name="dataset" value="framenet">
                <label for="framenet">framenet</label>
            </td>
        </tr>
    </table>
    <label for="word">Search word:</label>
    <input type="text" id="word" name="word" size="100" required><br><br>
    <input type="submit" name="submit_word" value="Amr Annotation">
</form>
<?php
if (isset($_POST['submit_word'])) {
    $parameter = new DisplayParameter();
    $parameter->word = $_POST['word'];
    $parameter->search_type = $_POST['search_type'];
    $dataset = $_POST['dataset'];
    switch ($dataset) {
        case "gb":
            $parameter->amrCorpus = new AmrCorpus("../Gb/Amr");
            $parameter->corpusName = "Gb";
            echo create_amr($parameter);
            break;
        case "tourism":
            $parameter->amrCorpus = new AmrCorpus("../Etstur/Amr");
            $parameter->corpusName = "Tourism";
            echo create_amr($parameter);
            break;
        case "framenet":
            $parameter->amrCorpus = create_merged_amr_corpus("../FrameNet-Examples/Amr");
            $parameter->corpusName = "Framenet";
            echo create_amr($parameter);
            break;
    }
}
?>
</body>
</html>