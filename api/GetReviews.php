<?php
/*
Input (1 GET parameter): item_id
Process: Gets ratings from the table
Output: Returns the rating information on success (on failure returns the error)
*/
require_once('../lib/config.php');
require_once('../lib/http_headers.php');
require_once('../lib/api_common_error_text.php');
require_once('../lib/api_error_functions.php');

// Set Up the Database Connection
$databaseConnection = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DBNAME);
if ($databaseConnection->connect_errno != 0) {
    SendSingleError(HTTP_INTERNAL_ERROR, $databaseConnection->connect_error, ERRTXT_DBCONN_FAILED);
}
$databaseConnection->set_charset(MYSQL_CHARSET);
// Put data in variables
$item_id = (isset($_GET['item_id'])) ? ($_GET['item_id']) : (false);

// Check for Data
if($item_id === false) {
	SendSingleError(HTTP_BAD_REQUEST, "one or more fields not found", ERRTXT_ID_NOT_FOUND);
} else {
	// Get data from database
	$query = "SELECT star_ranking, description FROM ratings WHERE item_id = $item_id";
	$data = $databaseConnection->query($query);
    if ($data->num_rows > 0) {
        $output = array();
        while ($row = $data->fetch_assoc()) {
            $output[] = $row;
        }
    	header(HTTP_OK);
		header(API_RESPONSE_CONTENT);
    	echo json_encode($output);
	    exit;
	} else if($data != "") {
	    header(HTTP_OK);
		header(API_RESPONSE_CONTENT);
    	echo json_encode(false);
    	exit;
	} else {
        SendSingleError(HTTP_INTERNAL_ERROR, 'failed to retrieve reviews from database', ERRTXT_FAILED_QUERY);
    }
}

SendSingleError(HTTP_INTERNAL_ERROR, 'php failed', ERRTXT_FAILED);

?>