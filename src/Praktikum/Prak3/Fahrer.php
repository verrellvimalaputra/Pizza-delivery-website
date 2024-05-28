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
        $sql =
            "SELECT o.ordering_id, o.address, GROUP_CONCAT(a.name ORDER BY a.name) AS ordered_pizzas, 
       ROUND(SUM(a.price),2) AS order_price, MIN(r.status) AS status
FROM pizzaservice.ordering o, pizzaservice.ordered_article r, pizzaservice.article a
WHERE a.article_id = r.article_id AND r.ordering_id = o.ordering_id
GROUP BY o.ordering_id
HAVING MIN(r.status) >= 2 AND MIN(r.status) != 4;";
        $recordset = $this->_database->query($sql);
        if (!$recordset) {
            throw new Exception("Abfrage fehlgeschlagen: " . $this->database->error);
        }
        $record = $recordset->fetch_assoc();
        while ($record) {
            $order_id = $record['ordering_id'];
            $address = $record['address'];
            $ordered_pizzas = $record['ordered_pizzas'];
            $order_price = $record['order_price'];
            $order_status = $record['status'];
            $all_deliveries[$order_id] = [
                'order_id' => $order_id,
                'address' => $address,
                'ordered_pizzas' => $ordered_pizzas,
                'order_price' => $order_price,
                'status' => $order_status
            ];
            $record = $recordset->fetch_assoc();
        }
        $recordset->free();
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
        $this->generatePageHeader('1337_Pizza: Kundenübersicht'); //to do: set optional parameters
        // to do: output view of this page
        if (empty($all_deliveries)) {
            // Display message when there are no orders to process
            echo '<p>There are no orders to process at the moment.</p>';
        } else {

        echo <<<HEREDOC
<h1>Fahrer (Auslieferbare Bestellungen)</h1>
    <section id="Bestellungen">
    <form action="Fahrer.php" method="POST" accept-charset="UTF-8">
HEREDOC;
        foreach ($all_deliveries as $order_id => $order) {
            $this->addDelivery(
                $order_id, $order['address'], $order['ordered_pizzas'],
                $order['order_price'], $order['status']);
        }
        echo <<<HEREDOC
        <input type = "submit" value = "update">
        </form>
</section>
        <script>
        // Refresh the page every 10 seconds
        setTimeout(function() {
        window.location.reload();
        }, 10000);
        </script>

HEREDOC;

        $this->generatePageFooter();
        }
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
     * @return void
     */
    protected function processReceivedData():void
    {
        //var_dump($_POST);
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                // Ensure key and value are set and not empty
                if (isset($key) && isset($value)) {
                    $key = intval($key);        // Ensure $key is treated as an integer
                    $value = intval($value);    // Ensure $value is treated as an integer
                    $sql = "UPDATE ordered_article
                            SET status = $value
                            WHERE ordering_id = $key";
                    $this->_database->query($sql);
                }
            }
            header("Location: Fahrer.php");
            die();
        }    
    }

    protected function addDelivery(
        $order_id, $address, $ordered_pizzas, $order_price, $status): void
    {
        echo <<<HEREDOC
<article>
    <fieldset>        
        <h2>Order: $order_id </h2>
        <h3>$address</h3>
        <p>$ordered_pizzas <strong>Preis: </strong>$order_price €</p>
HEREDOC;
        $this->getradioButtons($order_id, $status);
        echo <<<HEREDOC
    </fieldset>
</article>
HEREDOC;

    }

    protected function getradioButtons($order_id, $status)
    {   
        // Initialize variables to store the status for each radio button
        $fertig_checked = '';
        $unterwegs_checked = '';
        $geliefert_checked = '';

        // Check the status of the order and set the corresponding radio button to be checked
        switch ($status) {
            case '2':
                $fertig_checked = 'checked';
                break;
            case '3':
                $unterwegs_checked = 'checked';
                break;
            case '4':
                $geliefert_checked = 'checked';
                break;
            default:
                break;
        }
        echo <<<HEREDOC
        <label>
            <input type="radio" name={$order_id} value= 2 $fertig_checked>
            fertig
        </label>
        <label>
            <input type="radio" name={$order_id} value= 3 $unterwegs_checked>
            unterwegs
        </label>
        <label>
            <input type="radio" name={$order_id} value= 4 $geliefert_checked>
            geliefert
        </label>
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
