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
    <form action="https://echo.fbi.h-da.de/" id="form_1" method="get" accept-charset="UTF-8">
        <label> Name:
            <input type="text" name="my_name" />
        </label>

    </form>
</body>
</html>