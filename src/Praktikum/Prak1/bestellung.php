<?php declare(strict_types=1);
error_reporting(E_ALL);
require_once 'Pizza.php';
    header ("Content-type: text/html; charset=utf-8");
    $title = "1337_Pizza: Bestellseite";
    $all_pizzas[] = new Pizza("Pizza Margherita", "Margherita","../../resources/Margherita.jpg", 4.00);
    $all_pizzas[] = new Pizza("Pizza Salami", "Salami","../../resources/Salami.jpg", 4.50);
    $all_pizzas[] = new Pizza("Pizza Hawaiian", "Hawaii", "../../resources/Hawaii.jpg", 5.50);
    ?>
<!DOCTYPE html>
<html lang="de">
<?php
echo <<<HEREDOC
<head>
    <meta charset="UTF-8" />
    <!-- für später: CSS include -->
    <!-- <link rel="stylesheet" href="XXX.css"/> -->
    <!-- für später: JavaScript include -->
    <!-- <script src="XXX.js"></script> -->
    <title>$title</title>
</head>
HEREDOC;
?>
<body>
<h1>Bestellung</h1>
<section id="speisekarte">
    <h2>Speisekarte</h2>

    <?php
        foreach ($all_pizzas as $pizza) {
            echo <<<HEREDOC
                <article>
                    <img src="$pizza->pizza_image" alt="$pizza->pizza_name" width="300" height="200">
                    <h3>$pizza->pizza_name</h3>
                    <p><strong>Preis:</strong> $pizza->pizza_price €</p>
                </article>
HEREDOC;
        }
    ?>
</section>

<section id="warenkorb">
    <fieldset>
        <form action="https://echo.fbi.h-da.de/" method="post" accept-charset="UTF-8">
            <h2>Warenkorb</h2>
            <label>Pizza auswählen:
            <select id="auswahö_pizzen" name="pizzen_auswahl[]" size="3" tabindex="0" multiple>
                <?php
                    foreach ($all_pizzas as $pizza) {
                        $count = 1;
                        echo <<<HEREDOC
                            <option value="$pizza->pizza_short" tabindex="$count">$pizza->pizza_short</option>
HEREDOC;
                        $count++;
                    }
                ?>
            </select>
            </label>
            <p><strong>Gesamt:</strong> 14,00€</p>
            <h2>Bestellung abschließen</h2>
            <label>Adresse:
                <input type="text" name="Adresse" placeholder="Ihre Adresse" value="" required/>
            </label>
            <input type="button" name="löschen_button" value="Alle Löschen" />
            <input type="button" name="löschen-auswahl_button" value="Auswahl löschen" />
            <input type="submit" name="bestellen_button" value="Bestellen" />
        </form>
    </fieldset>
</section>
</body>
</html>
