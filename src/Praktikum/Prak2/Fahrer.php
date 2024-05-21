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
require_once './Kundenbestellung.php';

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
class Fahrer extends Page
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
    protected function getViewData():array
    {
        // to do: fetch data for this view from the database
        // to do: return array containing data
        $all_deliveries = array();
        $all_delivery_order_ids = array();
        $sql = "
SELECT o.ordering_id
FROM pizzaservice.ordering o, pizzaservice.ordered_article r
WHERE o.ordering_id = r.ordering_id
GROUP BY o.ordering_id
HAVING MIN(r.status) >= 2 AND MIN(r.status) < 4
ORDER BY o.ordering_id;";
        $recordset = $this->_database->query($sql);
        if (!$recordset) {
            throw new Exception("Abfrage fehlgeschlagen: " . $this->database->error);
        }
        $record = $recordset->fetch_assoc();
        while ($record) {
            $all_delivery_order_ids[] = $record['ordering_id'];
            $record = $recordset->fetch_assoc();
        }
        $recordset->free();

        foreach ($all_delivery_order_ids as $order_id) {
            $sql = "
            SELECT a.name, o.address, r.status, a.price
FROM pizzaservice.ordering o, pizzaservice.ordered_article r, pizzaservice.article a
WHERE a.article_id = r.article_id AND r.ordering_id = o.ordering_id AND o.ordering_id = $order_id;";
            $recordset = $this->_database->query($sql);
            if (!$recordset) {
                throw new Exception("Order-Abfrage fehlgeschlagen: " . $this->database->error);
            }
            $all_deliveries[] = new Kundenbestellung(intval($order_id), $recordset);
            $recordset->free();
        }
        return $all_deliveries;
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available the content of
     * all views contained is generated.
     * Finally, the footer is added.
     * @return void
     */
    protected function generateView():void
    {
        $all_deliveries = $this->getViewData(); //NOSONAR ignore unused $data
        $this->generatePageHeader('1337_Pizza: Fahrerübersicht'); //to do: set optional parameters
        // to do: output view of this page
        echo <<<HEREDOC
<h1>Fahrer (Auslieferbare Bestellungen)</h1>
    <section id="Bestellungen">
HEREDOC;
        foreach ($all_deliveries as $order) {
            $this->addDelivery($order);
        }
        echo <<<HEREDOC
</section>

HEREDOC;

        $this->generatePageFooter();
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
     * @return void
     */
    protected function processReceivedData():void
    {
        parent::processReceivedData();
        $order_id = 0;
        $buttongroup = "bestellstatus_";
        // to do: call processReceivedData() for all members
        if (isset($_POST) && count($_POST)) {
            if (isset($_POST['order_id'])) {
                $order_id = intval($_POST['order_id']);
                $buttongroup .= $order_id;
                if (isset( $_POST[$buttongroup])) {
                    $status = $this->statusToInt($_POST[$buttongroup]);
                    $sql = "
UPDATE pizzaservice.ordered_article 
SET status = $status
WHERE ordering_id = $order_id;";
                    $this->_database->query($sql);
                    header('Location: Fahrer.php'); die;

                }
            }
        }

    }

    protected function addDelivery(Kundenbestellung $order): void
    {
        $all_pizzas = $order->get_all_pizza_names();
        echo <<<HEREDOC
<article>
    <fieldset>
        <h2>Order: {$order->order_id} </h2>
        <h3>{$order->address}</h3>
        <p>$all_pizzas<strong>Preis: </strong>{$order->order_price} €</p>
HEREDOC;
        $this->getradioButtons($order->getOrderStatus(), $order->order_id);
        echo <<<HEREDOC
    </fieldset>
</article>
HEREDOC;

    }

    protected function getradioButtons($status, int $order_id)
    {
        $fertig_checked = '';
        $unterwegs_checked = '';
        $geliefert_checked = '';
        switch ($status) {
            case 2:
                $fertig_checked = 'checked';
                break;
            case 3:
                $unterwegs_checked = 'checked';
                break;
            case 4:
                $geliefert_checked = 'checked';
                break;

        }
        $buttongroup = "bestellstatus_".$order_id;
        echo <<<HEREDOC
<form action="Fahrer.php" method="post" accept-charset="UTF-8">
    <input type="hidden" name="order_id" value="$order_id">
    <label>
        fertig
        <input type="radio" name="$buttongroup" value="fertig" $fertig_checked>
    </label>
    <label>
        unterwegs
        <input type="radio" name="$buttongroup" value="unterwegs" $unterwegs_checked>
    </label>
    <label>
        geliefert
        <input type="radio" name="$buttongroup" value="geliefert" $geliefert_checked>
    </label>
    <input type="submit" name="aktualisieren" value="aktualisieren">
</form>
HEREDOC;

    }

    private function statusToInt($status): int {
        if ($status == "fertig")
            return 2;
        else if ($status == "unterwegs")
            return 3;
        else if ($status == "geliefert")
            return 4;
        return 0;
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
    public static function main():void
    {
        try {
            $page = new Fahrer();
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
Fahrer::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends).
// Not specifying the closing ? >  helps to prevent accidents
// like additional whitespace which will cause session
// initialization to fail ("headers already sent").
//? >