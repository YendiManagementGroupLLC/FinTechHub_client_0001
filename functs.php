<?php
// ########################################################################################
// start -- do some cleanup to ensure clients are not caching any data from previous cycle
// ########################################################################################
if (!session_id()) {
	@session_start();   
}

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate( "D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 

if (isset($_SESSION["form_submitted"])) {
	unset($_SESSION["form_submitted"]);
	header("Location: ?" . uniqid());
	#header("Refresh: 0");
}

unset($GLOBALS["TELEMETRY"]);

// open ro password file and read contents if needed
if (isset($_SESSION[mysql_ro_user()])) {
	set_telemetry_data(("Previously stored creds: " . mysql_ro_user()));
}
else {
	$pwd = file_get_contents((mysql_ro_creds_file())) or die("Unable to open file!");
	$_SESSION[mysql_ro_user()] = $pwd;
}
// open rw password file and read contents if needed
if (isset($_SESSION[mysql_rw_user()])) {
	set_telemetry_data(("Previously stored creds: " . mysql_rw_user()));
}
else {
	$pwd = file_get_contents((mysql_rw_creds_file())) or die("Unable to open file!");
	$_SESSION[mysql_rw_user()] = $pwd;
}
// ########################################################################################
// finish -- do some cleanup to ensure clients are not caching any data from previous cycle
// ########################################################################################


// ########################################################################################
// start -- app specific queries, for c.r.u.d. operations in mysql
// ########################################################################################
function close_credit_line_data($credit_id) {
	$conn = rw_connection();
	
	try {
		// first of all, let"s begin a transaction
		// Set autocommit to off
		mysqli_autocommit($conn, FALSE);
		// now a set of queries; if one fails, an exception should be thrown
		
		// close out credit line by setting to paid
		$sql = "UPDATE `credit` SET `paid`=1, `updated_dt`=NOW(), `updated_by`=CURRENT_USER() WHERE `id`=" . $credit_id . " AND `paid`=0;";
		mysqli_query($conn, $sql);
		set_telemetry_data(("Record updated successfully (S1)"));

		// if we arrive here, it means that no exception was thrown
		// i.e. no query has failed, and we can commit the transaction
		mysqli_commit($conn);
	} 
	catch (Exception $e) {
		// an exception has been thrown
		$sql_err = "Exception >> " . $e;
		set_telemetry_data($sql_err);
		// we must rollback the transaction
		mysqli_rollback($conn);
		die($sql_err);
	}	

	// now cleanup and release resources
	mysqli_close($conn);
}


function save_credit_line_data($name, $credit_amount) {
	$conn = rw_connection();
	
	try {
		// first of all, let"s begin a transaction
		// Set autocommit to off
		mysqli_autocommit($conn, FALSE);
		// now a set of queries; if one fails, an exception should be thrown
		
		// first add the change in credit data		
		// create a prepared statement -- prevent sql injection
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "INSERT INTO `credit` (`name`, `amount`, `added_dt`, `added_by`) VALUES (?, ?, NOW(), CURRENT_USER())"))
		{
			mysqli_stmt_bind_param($stmt, "sd", $name, $credit_amount);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}
		set_telemetry_data(("New record created successfully (S1)"));
		
		// now reflect this change in inventory valuation to increase amount due to inventory change
		$sql = "UPDATE `inventory_value` SET `amount`=`amount` - " . $credit_amount . ", `updated_dt`=NOW(), `updated_by`=CURRENT_USER() WHERE `id`=1;";
		mysqli_query($conn, $sql);
		set_telemetry_data(("Record updated successfully (S2)"));

		// if we arrive here, it means that no exception was thrown
		// i.e. no query has failed, and we can commit the transaction
		mysqli_commit($conn);
	} 
	catch (Exception $e) {
		// an exception has been thrown
		$sql_err = "Exception >> " . $e;
		set_telemetry_data($sql_err);
		// we must rollback the transaction
		mysqli_rollback($conn);
		die($sql_err);
	}	

	// now cleanup and release resources
	mysqli_close($conn);
}


function save_bank_payment_data($bank_amount) {
	$conn = rw_connection();
	
	try {
		// first of all, let"s begin a transaction
		// Set autocommit to off
		mysqli_autocommit($conn, FALSE);
		// now a set of queries; if one fails, an exception should be thrown
		
		// first add the change in bank payment data
		$sql = "INSERT INTO `bank_payment` (`amount`, `updated_dt`, `updated_by`) VALUES (" . $bank_amount . ", NOW(), CURRENT_USER());";
		mysqli_query($conn, $sql);
		set_telemetry_data(("New record created successfully (S1)"));
		
		// now reflect this change in inventory valuation to increase amount due to inventory change
		$sql = "UPDATE `inventory_value` SET `amount`=`amount` - " . $bank_amount . ", `updated_dt`=NOW(), `updated_by`=CURRENT_USER() WHERE `id`=1;";
		mysqli_query($conn, $sql);
		set_telemetry_data(("Record updated successfully (S2)"));

		// if we arrive here, it means that no exception was thrown
		// i.e. no query has failed, and we can commit the transaction
		mysqli_commit($conn);
	} 
	catch (Exception $e) {
		// an exception has been thrown
		$sql_err = "Exception >> " . $e;
		set_telemetry_data($sql_err);
		// we must rollback the transaction
		mysqli_rollback($conn);
		die($sql_err);
	}	

	// now cleanup and release resources
	mysqli_close($conn);
}


function save_inventory_data($article_id, $quantity, $price) {
	$amount = (int)$quantity * (double)$price;
	$conn = rw_connection();
	
	try {
		// first of all, let"s begin a transaction
		// Set autocommit to off
		mysqli_autocommit($conn, FALSE);
		// now a set of queries; if one fails, an exception should be thrown
		
		// first add the change in inventory data
		$sql = "INSERT INTO `inventory_change` (`article_id`,`quantity`,`price`,`amount`,`added_dt`,`added_by`) VALUES (" . $article_id . "," . $quantity . "," . $price . "," . $amount . ",NOW(),CURRENT_USER());";
		mysqli_query($conn, $sql);
		set_telemetry_data(("New record created successfully (S1)"));
		
		// now reflect this change in the tally card to reduce remaining quantity
		$sql = "UPDATE `tally_card` SET `quantity`=`quantity` - " . $quantity . ", `updated_dt`=NOW(), `updated_by`=CURRENT_USER() WHERE `article_id`=" . $article_id . ";";
		mysqli_query($conn, $sql);
		set_telemetry_data(("Record updated successfully (S2)"));
		
		// now reflect this change in inventory valuation to increase amount due to inventory change
		$sql = "UPDATE `inventory_value` SET `amount`=`amount` + " . $amount . ", `updated_dt`=NOW(), `updated_by`=CURRENT_USER() WHERE `id`=1;";
		mysqli_query($conn, $sql);
		set_telemetry_data(("Record updated successfully (S3)"));

		// if we arrive here, it means that no exception was thrown
		// i.e. no query has failed, and we can commit the transaction
		mysqli_commit($conn);
	} 
	catch (Exception $e) {
		// an exception has been thrown
		$sql_err = "Exception >> " . $e;
		set_telemetry_data($sql_err);
		// we must rollback the transaction
		mysqli_rollback($conn);
		die($sql_err);
	}	

	// now cleanup and release resources
	mysqli_close($conn);
}


function select_article_threshold_from_db($article_id) {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT a.`name`, b.`quantity`, CONVERT_TZ(b.`updated_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `updated_dt` FROM `article` a JOIN `tally_card` b ON a.`id`=b.`article_id` WHERE a.`id`=" . $article_id . ";";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["updated_dt"] . separator() . $row["name"] . separator() . $row["quantity"];
		}
	} 
	else {
		set_telemetry_data(("No corresponding article found in the database"));
	}
	$conn->close();
	return $list;
}


function select_open_credits_from_db() {
	return select_credits_from_db(0);
}


function select_closed_credits_from_db() {
	return select_credits_from_db(1);
}


function select_credits_from_db($is_paid) {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT `id`, `name`, `amount`, `paid`, CONVERT_TZ(`added_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `added_dt`, CONVERT_TZ(COALESCE(`updated_dt`, `added_dt`), " . server_tz_diff() . ", " . client_tz_diff() . ") as `updated_dt` FROM `credit` WHERE `paid`=" . $is_paid . " ORDER BY `name`, `added_dt` DESC, `updated_dt` DESC;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$paid_status = (get_boolval($row["paid"]) ? "true" : "false");
			$list[] = $row["name"] . separator() . $row["amount"] . separator() . $row["added_dt"] . separator() . $row["updated_dt"] . separator() . $paid_status . separator() . $row["id"];
		}
	} 
	else {
		set_telemetry_data(("No credits found in the database"));
	}
	$conn->close();
	return $list;
}


function select_total_credits_outstanding_from_db() {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT SUM(`amount`) AS `total_amount` FROM `credit` WHERE `paid`=0;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["total_amount"];
		}
	} 
	else {
		set_telemetry_data(("No credits found in the database"));
	}
	$conn->close();
	return $list;
}


function select_bank_payments_from_db() {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT `amount`, CONVERT_TZ(`updated_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `updated_dt` FROM `bank_payment` ORDER BY `updated_dt` DESC;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["updated_dt"] . separator() . $row["amount"];
		}
	} 
	else {
		set_telemetry_data(("No payments found in the database"));
	}
	$conn->close();
	return $list;
}


function select_valuations_from_db() {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT CONVERT_TZ(`updated_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `updated_dt`, `amount` FROM `inventory_value`;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["updated_dt"] . separator() . $row["amount"];
		}
	} 
	else {
		set_telemetry_data(("No valuations found in the database"));
	}
	$conn->close();
	return $list;
}


function select_article_levels_from_db() {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT a.`name`, b.`quantity`, CONVERT_TZ(b.`updated_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `updated_dt` FROM `article` a JOIN `tally_card` b ON a.`id`=b.`article_id` WHERE a.`id`>0 ORDER BY a.`name`, b.`updated_dt` DESC;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["updated_dt"] . separator() . $row["name"] . separator() . $row["quantity"];
		}
	} 
	else {
		set_telemetry_data(("No articles found in the database"));
	}
	$conn->close();
	return $list;
}


function select_article_history_from_db($article_id) {
	$list = array();
	$conn = ro_connection();
	$sql = "SELECT a.`name`, b.`quantity`, b.`price`, b.`amount`, CONVERT_TZ(b.`added_dt`, " . server_tz_diff() . ", " . client_tz_diff() . ") as `added_dt` FROM `article` a JOIN `inventory_change` b ON a.`id`=b.`article_id` WHERE a.`id`=" . $article_id . " ORDER BY b.`added_dt` DESC;";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["added_dt"] . separator() . $row["quantity"] . separator() . $row["price"] . separator() . $row["amount"];
		}
	} 
	else {
		set_telemetry_data(("No article history found in the database"));
	}
	$conn->close();
	return $list;
}


function select_articles_dropdown_from_db() {
	$list = array();
	$conn = ro_connection();
	$sql = select_articles_sql();
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row["id"] . separator() . $row["name"] . " [" . $row["quantity"] . " available]";
		}
	} 
	else {
		set_telemetry_data(("No articles found in the database"));
	}
	$conn->close();
	return $list;
}


function select_id_and_name_from_db($sql) {
	return select_kvp_from_db($sql, "id", "name");
}


function select_kvp_from_db($sql, $key, $value) {
	$list = array();
	$conn = ro_connection();
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$list[] = $row[$key] . separator() . $row[$value];
		}
	} 
	else {
		set_telemetry_data(("No articles found in the database"));
	}
	$conn->close();
	return $list;
}


function select_articles_sql() {
	return "SELECT a.`id`, a.`name`, b.`quantity` FROM `article` a JOIN `tally_card` b ON a.`id` = b.`article_id` ORDER BY a.`name`;";
}
// ########################################################################################
// finish -- app specific queries, for c.r.u.d. operations in mysql
// ########################################################################################


// ########################################################################################
// start -- generic helper functions used throughout the application
// ########################################################################################
function ro_credentials() {
	return ("" . mysql_host() . separator() . mysql_ro_user() . separator() . mysql_ro_pass() . separator() . mysql_db() . "");
}


function rw_credentials() {
	return ("" . mysql_host() . separator() . mysql_rw_user() . separator() . mysql_rw_pass() . separator() . mysql_db() . "");
}


function ro_connection() {
	// set the credentials
	$parts = explode(separator(), ro_credentials());
	$servername = $parts[0];
	$username = $parts[1];
	$password = $parts[2];
	$dbname = $parts[3];
	// create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// check connection
	if ($conn->connect_error) {
		$sql_conn_err = "Failed to connect to MySQL: " . $conn->connect_error;
		set_telemetry_data($sql_conn_err);
		die($sql_conn_err);
	}
	return $conn;
}


function rw_connection() {
	// set the credentials
	$parts = explode(separator(), rw_credentials());
	$servername = $parts[0];
	$username = $parts[1];
	$password = $parts[2];
	$dbname = $parts[3];
	// create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	// check connection
	if (mysqli_connect_errno()) {
		$sql_conn_err = "Failed to connect to MySQL: " . mysqli_connect_error();
		set_telemetry_data($sql_conn_err);
		die($sql_conn_err);
	}
	return $conn;
}


function get_formatted_date($date_str) {
    // set_telemetry_data($date_str); => 2018-10-15 19:43:32
    // $date = date_create_from_format("Y-m-d H:i:s", $date_str);
    $tz = timezone_open('UTC');
    $date = date_create($date_str, $tz);
	return date_format($date, "D dS M Y h:i:s A"); 
	// Thursday 14th June 2018 12:15pm => l jS F Y g:ia
	// Thu 14th Jun 2018 12:15:46 PM => D dS M Y h:i:s A
}


function get_boolval($in, $strict=false) {
    $out = false;
    // if not strict, we only have to check if something is false
    if (in_array($in, array("false", "False", "FALSE", "no", "No", "n", "N", "0", "off", "Off", "OFF", false, 0, null), true)) {
        $out = false;
    } 
	else if ($strict) {
        // if strict, check the equivalent true values
        if (in_array($in, array("true", "True", "TRUE", "yes", "Yes", "y", "Y", "1", "on", "On", "ON", true, 1), true)) {
            $out = true;
        }
    } 
	else {
        // not strict? let the regular php bool check figure it out (will
        //     largely default to true)
        $out = ($in ? true : false);
    }
    return $out;
}


function sanitize_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}


function mysql_host() {
	return "localhost";
}


function mysql_db() {
	return "neoaf2_client_0001_inventory";
}


function mysql_creds_path() {
	return "/home/neoaf2/mysql_creds/.";
}


function mysql_creds_ext() {
	return ".lgn";
}


function mysql_ro_creds_file() {
	return (mysql_creds_path() . mysql_ro_user() . mysql_creds_ext());
}


function mysql_ro_user() {
	return "neoaf2_cl0001ro";
}


function mysql_ro_pass() {
	return $_SESSION[mysql_ro_user()];
}


function mysql_rw_creds_file() {
	return (mysql_creds_path() . mysql_rw_user() . mysql_creds_ext());
}


function mysql_rw_user() {
	return "neoaf2_cl0001rw";
}


function mysql_rw_pass() {
	return $_SESSION[mysql_rw_user()];
}


function get_app_name() {
	return "[YMG] Inventory Manager App v0.1";
}


function get_telemetry_data() {
	return $GLOBALS["TELEMETRY"];
}


function set_telemetry_data($in) {
	$GLOBALS["TELEMETRY"] = $GLOBALS["TELEMETRY"] . " " . separator() . " " . $in;
}


function get_app_console_name() {
	$app_name = get_app_name();
	return $app_name . " - CONSOLE";
}


function get_app_menu_name() {
	$app_name = get_app_name();
	return $app_name . " - MENU";
}


function print_array($array, $pad="") {
	foreach ($array as $key => $value) {
		set_telemetry_data(("" . $pad . "$key => $value"));
        if (is_array($value)) {
            print_array($value, $pad . " ");
        }  
    } 
}


function separator() {
	return "|";
}


function server_tz_diff() {
	return "'-07:00'";
}


function client_tz_diff() {
	return "'+00:00'";
}
// ########################################################################################
// finish -- generic helper functions used throughout the application
// ########################################################################################
?>
