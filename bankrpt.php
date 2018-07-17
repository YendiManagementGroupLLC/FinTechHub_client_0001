<?php
include "functs.php";
// define variables and set to empty values
$entries = array();
// fetch all the payments made to the bank
$entries = select_bank_payments_from_db();
?>

<html>
<head><title><?php echo get_app_console_name();?></title>
<link rel="stylesheet" type="text/css" href="appstyle.css">
</head>
<body>
<hr>
<div class="container">
<h1>Bank Payments Report</h1>
<?php
if (count($entries) > 0) {
?>
<table>
	<tr>
		<th>Date</th>
		<th>Amount Deposited</th>
	</tr>
	<?php
	foreach($entries as $entry) { 
		$parts = explode(separator(), $entry);
		?>
	<tr>
		<td><?= get_formatted_date(($parts[0])) ?></td>
		<td style="text-align: right"><?= number_format((double)$parts[1], 2) ?></td>
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
