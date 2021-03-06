
<?php
/*
Input (1 POST parameters): username
Process: Inserts input into the sales table
Output: A boolean variable that is true on success (on failure returns the error)
*/
require_once('../lib/config.php');
require_once('../lib/http_headers.php');
require_once('../lib/api_common_error_text.php');
require_once('../lib/api_error_functions.php');

session_start();

// Set Up the Database Connection
$databaseConnection = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DBNAME);
if ($databaseConnection->connect_errno != 0) {
    SendSingleError(HTTP_INTERNAL_ERROR, $databaseConnection->connect_error, ERRTXT_DBCONN_FAILED);
}
$databaseConnection->set_charset(MYSQL_CHARSET);

// Put data in variables
$username = (isset($_SESSION['username'])) ? ($_SESSION['username']) : (false);

//$amount = (isset($_GET['amount'])) ? ($_GET['amount']) : (false);
$item_id = (isset($_GET['item_id'])) ? ($_GET['item_id']) : (false);
$card_number = (isset($_GET['card_number'])) ? ($_GET['card_number']) : (false);
$address_id = (isset($_GET['address_id'])) ? ($_GET['address_id']) : (false);

// Check for Data
if($username === false ||  $item_id === false ||  $card_number === false ||  $address_id === false) {
	SendSingleError(HTTP_BAD_REQUEST, "one or more fields not found", ERRTXT_ID_NOT_FOUND);
} else {
	// Write data to database
	$query1 = "INSERT INTO sales (amount, `time`, username, item_id, card_number, address_id) VALUES ((SELECT listed_price FROM sold_by WHERE item_id=$item_id), NOW(), '$username', $item_id, $card_number, $address_id)";
    $query2 = "UPDATE sold_by SET number_in_stock=number_in_stock-1 WHERE item_id=$item_id";
    $query3 = "UPDATE sellers SET balance_due = balance_due+(SELECT listed_price FROM sold_by WHERE item_id=$item_id) WHERE username='(SELECT seller FROM items WHERE item_id=$item_id)'";
	if($databaseConnection->query( $query1) && $databaseConnection->query( $query2) && $databaseConnection->query( $query3)) { // If query was successful
        // badge 2 (Santa Claus)
        if (date('n') == '12') {
            $badgeQuery = "INSERT INTO badge_progresses (username, badge_id, units_earned, last_updated) VALUES ('$username', 2, 1, NOW()) ON DUPLICATE KEY UPDATE last_updated=NOW(), units_earned=if(units_earned+1 > 5, 5, units_earned+1)";
            $databaseConnection->query($badgeQuery);
        }
        // badge 3 (My Valentine)
        if (date('n') == '2') {
            $badgeQuery = "INSERT INTO badge_progresses (username, badge_id, units_earned, last_updated) VALUES ('$username', 3, 1, NOW()) ON DUPLICATE KEY UPDATE last_updated=NOW(), units_earned=if(units_earned+1 > 3, 3, units_earned+1)";
            $databaseConnection->query($badgeQuery);
        }
		header(HTTP_OK);
		header(API_RESPONSE_CONTENT);
    	echo json_encode(TRUE);
    	exit;
    } else {
        SendSingleError(HTTP_INTERNAL_ERROR, 'failed to complete purchase transaction.', ERRTXT_FAILED_QUERY);
    }
}

SendSingleError(HTTP_INTERNAL_ERROR, 'php failed', ERRTXT_FAILED);

?>