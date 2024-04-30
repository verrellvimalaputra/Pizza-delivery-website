<?php
    header('Content-type: text/html');
    $title = "1-100 Zähler";
?>
<!DOCTYPE html>
<html lang="de">
<?php
echo <<<HEREDOC
<head>
    <meta charset="UTF-8" />
    <title>$title</title>
</head>
HEREDOC;
?>
<body>
<?php
    for($i = 1; $i <= 100; $i++){
        echo "<p> Zahl: ".$i."</p>\n";
    }
?>
</body>
</html>