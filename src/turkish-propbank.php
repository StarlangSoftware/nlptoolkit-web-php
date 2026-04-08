<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish PropBank</title>
</head>
<body>
<?php
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
use olcaytaner\Framenet\FrameNet;
use olcaytaner\Propbank\FramesetList;
use olcaytaner\WordNet\WordNet;
$propBankCache = "propbank1.cache";
if (file_exists($propBankCache)) {
    $turkishPropBank = unserialize(file_get_contents($propBankCache));
} else {
    $turkishPropBank = new FramesetList();
    file_put_contents($propBankCache, serialize($turkishPropBank));
}
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
    <label for="verb_name">Verb: (gitmek, koşmak, atmak, yürümek, sallamak, ...)</label>
    <input type="text" id="verb_name" name="verb_name" required><br><br>
    <input type="submit" name="submit_propbank_verb_search" value="Find Verb">
</form>
<?php
if (isset($_POST['submit_propbank_verb_search'])) {
    $verb_name = htmlspecialchars($_POST['verb_name']);
    $synsets = $turkishWordNet->getSynSetsWithLiteral($verb_name);
    echo create_prop_bank_table_for_multiple_synsets($turkishPropBank, $synsets);
}
?>
<br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="verb_id">Verb SynSet Id: (TUR10-0000360, TUR10-0908230, TUR10-0718440, ...)</label>
    <input type="text" id="verb_id" name="verb_id" required><br><br>
    <input type="submit" name="submit_propbank_verb_id_search" value="Find Verb">
</form>
<?php
if (isset($_POST['submit_propbank_verb_id_search'])) {
    $verb_id = htmlspecialchars($_POST['verb_id']);
    echo create_prop_bank_table($turkishPropBank, $verb_id);
}
?>
</body>
</html>