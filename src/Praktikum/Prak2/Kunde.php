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
class Kunde extends Page
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
        $all_orders = array();
        $all_order_ids = array();
        $order_id = "AND o.ordering_id = ";
        if (isset($_SESSION['order_id']))
            $order_id .= $_SESSION['order_id'];
        else
            $order_id = "";
        $sql = "
SELECT o.ordering_id
FROM pizzaservice.ordering o, pizzaservice.ordered_article r
WHERE o.ordering_id = r.ordering_id $order_id
GROUP BY o.ordering_id
HAVING MIN(r.status) < 4
ORDER BY o.ordering_id;";
        $recordset = $this->_database->query($sql);
        if (!$recordset) {
            throw new Exception("Abfrage fehlgeschlagen: " . $this->database->error);
        }
        $record = $recordset->fetch_assoc();
        while ($record) {
            $all_order_ids[] = intval($record['ordering_id']);
            $record = $recordset->fetch_assoc();
        }
        $recordset->free();
        foreach ($all_order_ids as $order_id) {
            $sql = "
            SELECT a.name, o.address, r.status
FROM pizzaservice.ordering o, pizzaservice.ordered_article r, pizzaservice.article a
WHERE a.article_id = r.article_id AND r.ordering_id = o.ordering_id AND o.ordering_id = $order_id;";
            $recordset = $this->_database->query($sql);
            if (!$recordset) {
                throw new Exception("Order-Abfrage fehlgeschlagen: " . $this->database->error);
            }
            $all_orders[] = new Kundenbestellung($order_id, $recordset);
            $recordset->free();
        }
        return $all_orders;
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
        $allOrders = $this->getViewData(); //NOSONAR ignore unused $data
        $this->generatePageHeader('1337_Pizza Kundenübersicht'); //to do: set optional parameters
        // to do: output view of this page
        echo <<<HEREDOC
<h1>Kunden (Lieferstatus)</h1>
<section id="kundenliste">

HEREDOC;
        foreach ($allOrders as $order) {

            $this->addOrder($order);
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
        // to do: call processReceivedData() for all members
        session_start();
    }

    private function stateToString($status):string
    {
        if ($status == 0) {
            return "bestellt";
        } elseif ($status == 1) {
            return "Im Ofen";
        } elseif ($status == 2) {
            return "fertig";
        } elseif ($status == 3) {
            return "unterwegs";
        } elseif ($status == 4) {
            return "geliefert";
        } else {
            return "ungültig";
        }
    }

    private function addOrder(Kundenbestellung $order): void {
        echo <<<HEREDOC
<article>
    <fieldset>
        <h2>$order->username Order: $order->order_id</h2>
HEREDOC;
        for ($i = 0; $i < count($order->pizza_status); $i++) {
            $status = $this->stateToString($order->pizza_status[$i]['status']);
            echo "<p>{$order->pizza_status[$i]['pizza_name']}: $status</p>\n";
        }
        echo <<<HEREDOC
    </fieldset>
</article>

HEREDOC;
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
            $page = new Kunde();
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
Kunde::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends).
// Not specifying the closing ? >  helps to prevent accidents
// like additional whitespace which will cause session
// initialization to fail ("headers already sent").
//? >