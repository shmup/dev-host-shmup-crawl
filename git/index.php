<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>grep crawl master branch</title>
    <link href="https://fonts.googleapis.com/css?family=Ubuntu+Mono" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div>
    <form action=<?php echo htmlspecialchars(str_replace('index.php', '', $_SERVER["PHP_SELF"])); ?> method="GET">
        <input type="text" autofocus="autofocus" name="grep">
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_GET['grep']) || trim($_GET['grep']) == '') die();

$grep = preg_quote($_GET['grep']);
// FIX ME. can't grep for ->   "ok
$str = 'grep -rnwI ./crawl/ -e "' . $grep . '"';
$result = shell_exec($str);
$parts = explode("\n", $result);

echo '<label>grepping ' . stripslashes($grep) . '</label>';
echo '</form></div>';
echo '<div id=thing><ul id=list>';

foreach ($parts as $p) {
    $more_parts = explode(":", $p);
    // https://raw.githubusercontent.com/crawl/crawl/master/
    // ./crawl/crawl-ref/source/test/stress/qw.rc
    $url_parts = explode("/", $more_parts[0], 3);
    if (count($url_parts) != 3) continue;
    $url = "http://raw.githubusercontent.com/crawl/crawl/master/" . $url_parts[2];
    $ugh = htmlspecialchars($more_parts[2]);
    echo "<li><a href='{$url}' target='_blank'>{$more_parts[0]}</a> - {$more_parts[1]}";
    echo "<ul><li>{$ugh}</li></ul>";
    echo "</li>";
}

echo '</ul></div>';

?>

</body>
</html>
