<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 *
 * PHP Version 7.4
 *
 * @file     PageTemplate.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.1
 */

// to do: change name 'PageTemplate' throughout this file
require_once './Page.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Bestellung extends Page
{
    // to do: declare reference variables for members
    // representing substructures/blocks

    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
     * @return array An array containing the requested data.
     * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData(): array
    {
        // to do: fetch data for this view from the database
        // to do: return array containing data
        $pizzas = array();
        $sql = "SELECT * FROM pizzaservice.article";
        $recordset = $this->_database->query($sql);
        if (!$recordset) {
            throw new Exception("Abfrage fehlgeschlagen: " . $this->database->error);
        }

        $record = $recordset->fetch_assoc();
        while ($record) {
            $article_id = $record['article_id'];
            $name = $record['name'];
            $picture = $record['picture'];
            $price = $record['price'];
            $pizza_data = array(
                'name' => $name,
                'picture' => $picture,
                'price' => $price
            );
            $pizzas[$article_id] = $pizza_data;
            $record = $recordset->fetch_assoc();
        }
        $recordset->free();
        return $pizzas;
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available the content of
     * all views contained is generated.
     * Finally, the footer is added.
     * @return void
     */
    protected function generateView(): void
    {
        $all_pizzas = $this->getViewData(); //NOSONAR ignore unused $data
        $this->generatePageHeader('1337_Pizza Bestellung'); //to do: set optional parameters
        // to do: output view of this page
        echo <<<HEREDOC
<h1>Bestellung</h1>
<section id="speisekarte">
    <h2>Speisekarte</h2>
HEREDOC;
        foreach ($all_pizzas as $pizza) {
            $this->addPizzaArticle($pizza['name'], $pizza['price'], $pizza['picture']);
        }
        $this->addShoppingCart($all_pizzas);

        $this->generatePageFooter();
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
     * @return void
     */
    protected function processReceivedData(): void
    {
        parent::processReceivedData();
        // to do: call processReceivedData() for all members

    }

    private function addPizzaArticle($pizza_name, $pizza_price, $pizza_image): void
    {
        echo <<<HEREDOC
<fieldset>
    <article>
        <img src="$pizza_image" alt="$pizza_name" width="322" height="156">
        <h3>$pizza_name</h3>
        <p><strong>Preis:</strong> $pizza_price €</p>
    </article>
</fieldset>
HEREDOC;

    }

    private function addShoppingCart($allPizzas): void
    {
        echo <<<EOC
<section id="warenkorb">
    <fieldset>
        <form action="https://echo.fbi.h-da.de/" method="post" accept-charset="UTF-8">
            <h2>Warenkorb</h2>
            <label>Pizza auswählen:
            <select id="auswahl_pizzen" name="pizzen_auswahl[]" size="3" tabindex="0" multiple>
EOC;
        foreach ($allPizzas as $pizza) {
            $name = $pizza['name'];
            echo <<<HEREDOC
<option value="$name">$name</option>
HEREDOC;

        }
        echo <<<EOC
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

EOC;
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
     * @return void
     */
    public static function main(): void
    {
        try {
            $page = new Bestellung();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page.
// That is input is processed and output is created.
Bestellung::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends).
// Not specifying the closing ? >  helps to prevent accidents
// like additional whitespace which will cause session
// initialization to fail ("headers already sent").
//? >