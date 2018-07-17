<?php
include "functs.php";
// define variables and set to empty values
$open_credits = array();
$closed_credits = array();
$credits_outstanding = array();
// fetch all the open credit accounts
$open_credits = select_open_credits_from_db();
// fetch all the closed credit accounts
$closed_credits = select_closed_credits_from_db();
// fetch the value at risk (credits outstanding) from db
$credits_outstanding = select_total_credits_outstanding_from_db();
$amount_outstanding = $credits_outstanding[0];
?>

<html>
<head><title><?php echo get_app_console_name();?></title>
<link rel="stylesheet" type="text/css" href="appstyle.css">
</head>
<body>
<hr>
<div class="container">
<h1>Creditors Report</h1>
<h2>Outstanding Credit: <?php echo number_format((double)$amount_outstanding, 2);?></h2>
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
		<th>Paid</th>
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
		<td><?= $parts[4] ?></td>
	</tr>
	<?php
	} ?>
</table>
<?php
} ?>
<?php
if (count($closed_credits) > 0) {
?>
<h2>Closed Credit Accounts</h2>
<table>
	<tr>
		<th>Name</th>
		<th>Amount</th> 
		<th>Added Date</th>
		<th>Last Activity Date</th>
		<th>Paid</th>
	</tr>
	<?php
	foreach($closed_credits as $entry) { 
		$parts = explode(separator(), $entry);
		?>
	<tr>
		<td><?= $parts[0] ?></td>
		<td style="text-align: right"><?= number_format((double)$parts[1]) ?></td>
		<td><?= get_formatted_date(($parts[2])) ?></td>
		<td><?= get_formatted_date(($parts[3])) ?></td>
		<td><?= $parts[4] ?></td>
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
