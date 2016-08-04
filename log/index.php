<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>search #octolog</title>
    <link href="https://fonts.googleapis.com/css?family=Ubuntu+Mono" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div>
    <form action=<?php echo htmlspecialchars(str_replace('index.php', '', $_SERVER["PHP_SELF"])); ?> method="GET">
        <input type="text" autofocus="autofocus" name="search">
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_GET['search']) || trim($_GET['search']) == '') die();

$log = file_get_contents('./octo.log');

/* $search = $_GET['search']; */
$search = preg_quote($_GET['search'], "'");

echo '<label>searching: ' . stripslashes($search) . '</label>';
echo '</form></div>';
echo '<pre>';

/* $pattern = '/' . $search . '/'; */
$pattern = '/.*' . $search . '.*/';
preg_match_all($pattern, $log, $matches);
foreach ($matches[0] as $result) {
    echo $result . "\r\n\r\n";
}

echo '</pre>';

?>

</body>
</html>
