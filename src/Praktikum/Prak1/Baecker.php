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

class Pizzabaecker extends Page
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
        $pizzaorders = array();
        $sql = "SELECT oa.ordered_article_id, oa.ordering_id, oa.article_id, oa.status, a.name
                FROM pizzaservice.ordered_article AS oa
                INNER JOIN pizzaservice.article AS a ON oa.article_id = a.article_id";
        $recordset = $this->_database->query($sql);
        if (!$recordset) {
            throw new Exception("Abfrage fehlgeschlagen: " . $this->database->error);
        }

        while ($record = $recordset->fetch_assoc()) {
            // Extract data from the current row
            $ordered_article_id = $record['ordered_article_id'];
            $ordering_id = $record['ordering_id'];
            $article_id = $record['article_id'];
            $status = $record['status'];
            $article_name = $record['name'];

            // Store the data in an associative array
            $pizzaorder_data = array(
                'ordered_article_id'=> $ordered_article_id,
                'ordering_id' => $ordering_id,
                'article_id' => $article_id,
                'article_name' => $article_name,
                'status' => $status
            );

            // Store the pizza order data using the article_id as the key
            $pizzaorders[$ordered_article_id] = $pizzaorder_data;
        }
        $recordset->free();
        return $pizzaorders;
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
        $all_pizzaorders = $this->getViewData(); //NOSONAR ignore unused $data
        $this->generatePageHeader('1337_Pizza Pizzabäcker'); //to do: set optional parameters
        // to do: output view of this page
        echo <<<HEREDOC
<h1>Pizza Bestellstatus</h1>
<section id="bestellungen">
<table id="bestellungenstatus" border="0">
<tr>
    <th>Pizza</th>
    <th>bestellt</th>
    <th>im Ofen</th>
    <th>fertig</th>
</tr>
HEREDOC;
        foreach ($all_pizzaorders as $pizzaorders) {
            $this->addPizzaOrders($pizzaorders['ordered_article_id'], $pizzaorders['ordering_id'], $pizzaorders['article_id'], $pizzaorders['article_name'], $pizzaorders['status']);
        }

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

    private function addPizzaOrders($ordered_article_id, $ordering_id, $article_id, $article_name, $status): void
    {
        // Initialize variables to store the status for each radio button
        $bestellt_checked = '';
        $im_ofen_checked = '';
        $fertig_checked = '';

        // Check the status of the order and set the corresponding radio button to be checked
        if ($status > 0 && $status < 6) {
            switch ($status) {
                case '1':
                    $bestellt_checked = 'checked';
                    break;
                case '2':
                    $im_ofen_checked = 'checked';
                    break;
                case '3':
                    $fertig_checked = 'checked';
                    break;
                default:
                    break;
            }
        }
        echo <<<HEREDOC
        <tr>
        <td>$article_name</td>
        <td><input type="radio" name="{$ordered_article_id}_status" value="bestellt" $bestellt_checked onclick="updateOrderStatus($ordered_article_id, 1)"> Bestellt</td>
        <td><input type="radio" name="{$ordered_article_id}_status" value="im Ofen" $im_ofen_checked onclick="updateOrderStatus($ordered_article_id, 2)"> Im Ofen</td>
        <td><input type="radio" name="{$ordered_article_id}_status" value="fertig" $fertig_checked onclick="updateOrderStatus($ordered_article_id, 3)"> Fertig</td>
    </tr>
HEREDOC;
    }

    //save the updated status to database
    private function updateOrderStatus($ordered_article_id, $status): void
    {
        $sql = "UPDATE ordered_article
                SET status = $status
                WHERE ordered_article_id = $ordered_article_id;";
        $this->db->query($sql);
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
            $page = new Pizzabaecker();
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
Pizzabaecker::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends).
// Not specifying the closing ? >  helps to prevent accidents
// like additional whitespace which will cause session
// initialization to fail ("headers already sent").
//? >