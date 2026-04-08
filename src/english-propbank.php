<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
<link rel="stylesheet" href="../style.css">
<head>
    <meta charset="UTF-8">
    <title>English PropBank</title>
</head>
<body>
<?php
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('memory_limit', '1024M');
use olcaytaner\Propbank\PredicateList;
$propBankCache = "propbank2.cache";
if (file_exists($propBankCache)) {
    $englishPropBank = unserialize(file_get_contents($propBankCache));
} else {
    $englishPropBank = new PredicateList();
    file_put_contents($propBankCache, serialize($englishPropBank));
}
include 'functions.php';
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="predicate_name">Predicate: (go, watch, talk, ...)</label>
    <input type="text" id="predicate_name" name="predicate_name" required><br><br>
    <input type="submit" name="submit_english_predicate_search" value="Find Predicate">
    <br>
</form>
<?php
if (isset($_POST['submit_english_predicate_search'])) {
    $predicate_name = htmlspecialchars($_POST['predicate_name']);
    echo create_predicate_table($englishPropBank, $predicate_name);
}
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="role_set_id">RoleSet Id: (go.01, watch.01, talk.02, ...)</label>
    <input type="text" id="role_set_id" name="role_set_id" required><br><br>
    <input type="submit" name="submit_english_role_set_search" value="Find Roleset">
</form>
</body>
<?php
if (isset($_POST['submit_english_role_set_search'])) {
    $role_set_id = htmlspecialchars($_POST['role_set_id']);
    echo create_role_set_table($englishPropBank, $role_set_id);
}
?>
</html>