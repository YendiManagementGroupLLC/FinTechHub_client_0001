<?php
include "functs.php";
// define variables and set to empty values
$bank_amount = $name = $credit_amount = $credit_id = $mode = "";
$open_credits = array();
// setup the various form keys for multi-functions
$add_bank_payment_data_Msg = "";
$add_bank_payment_form_key = "add_bank_payment";
$add_credit_line_form_key = "add_credit_line";
$close_credit_line_form_key = "close_credit_line";
// validate incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	print_array($_POST);
	
	$mode = sanitize_input($_POST["mode"]);
	$credit_id = sanitize_input($_POST["credit_id"]);
	set_telemetry_data(("Mode : " . $mode . " CreditID : " . $credit_id));
	
	if ($mode == $add_bank_payment_form_key) {
		$bank_amount = sanitize_input($_POST["bank_amount"]);
		if (empty($bank_amount)) {
			$bank_amount_Err = "Deposit Amount is required";
		}
	}
	
	if ($mode == $add_credit_line_form_key) {
		$name = sanitize_input($_POST["name"]);
		if (empty($name)) {
			$name_Err = "Name is required";
		}
		$credit_amount = sanitize_input($_POST["credit_amount"]);
		if (empty($credit_amount)) {
			$credit_amount_Err = "Credit Amount is required";
		}
	}
}
// proceed here with edit mode, add new inventory management record
if ($mode == $add_bank_payment_form_key) {
	if (empty($bank_amount)) {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		save_bank_payment_data($bank_amount);
		$add_bank_payment_data_Msg = "Deposit Amount recorded successfully";
	}
}
else if ($mode == $add_credit_line_form_key) {
	if (empty($name) || empty($credit_amount)) {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		save_credit_line_data($name, $credit_amount);
	}
}
else if ($mode == $close_credit_line_form_key) {
	if (empty($credit_id)) {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		close_credit_line_data($credit_id);
	}
}
// collect credit lines to display if open
$open_credits = select_open_credits_from_db();
?>

<html>
<head><title><?php echo get_app_console_name();?></title>
<link rel="stylesheet" type="text/css" href="appstyle.css">
</head>
<body>
<hr>
<div class="container">
<h1>Add Bank Payment</h1>
<p><span class="error">* required field</span></p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="bank_amount">Amount <span class="error">* <?php echo $bank_amount_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="bank_amount" id="bank_amount" placeholder="Amount to pay..">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<?php echo $add_bank_payment_data_Msg;?>
			<input type="hidden" name="mode" id="mode" value="<?php echo $add_bank_payment_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Make Payment">
		</div>
	</div>
</form>
<hr>
<h1>Add Credit Line</h1>
<p><span class="error">* required field</span></p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="name">Name <span class="error">* <?php echo $name_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="name" id="name" placeholder="Creditor name..">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<label for="credit_amount">Amount <span class="error">* <?php echo $credit_amount_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="credit_amount" id="credit_amount" placeholder="Amount borrowed..">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<input type="hidden" name="mode" id="mode" value="<?php echo $add_credit_line_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Record Credit">
		</div>
	</div>
</form>
<?php
if (count($open_credits) > 0) {
?>
<h2>Open Credit Accounts</h2>
<table>
	<tr>
		<th>Name</th>
		<th>Amount</th> 
		<th>Added Date</th>
		<th>Last Activity Date</th>
		<th>Action</th>
	</tr>
	<?php
	foreach($open_credits as $entry) { 
		$parts = explode(separator(), $entry);
		?>
	<tr>
		<td><?= $parts[0] ?></td>
		<td style="text-align: right"><?= number_format((double)$parts[1]) ?></td>
		<td><?= get_formatted_date(($parts[2])) ?></td>
		<td><?= get_formatted_date(($parts[3])) ?></td>
		<td><form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post"><input type="hidden" name="mode" value="<?php echo $close_credit_line_form_key;?>"><input type="hidden" name="credit_id" id="credit_id" value="<?= $parts[5] ?>"><input type="submit" value="Mark Paid"></form></td>
	</tr>
	<?php
	} ?>
</table>
<?php
} ?>
</div>
<hr>
<br>
<br>
<iframe src="navigation.php" style="border:none;height:250px;width:100%;"></iframe>
<br><br><span class="telemetry"><?php echo get_telemetry_data();?></span></body>
</html>
