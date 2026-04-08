<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>Turkish Morphological Analysis</title>
</head>
<body>
<?php
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
$fsmCache = "fsm.cache";
if (file_exists($fsmCache)) {
    $fsm = unserialize(file_get_contents($fsmCache));
} else {
    $fsm = new FsmMorphologicalAnalyzer();
    file_put_contents($fsmCache, serialize($fsm));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="sentence">Sentence:</label>
    <input type="text" id="sentence" name="sentence" size="100" required><br><br>
    <input type="submit" name="submit_morphological_analysis" value="Morphological Analysis">
</form>
<?php
if (isset($_POST['submit_morphological_analysis'])) {
    $sentence = htmlspecialchars($_POST['sentence']);
    echo create_morphological_analysis_table($fsm, $sentence);
}
?>
</body>
</html>