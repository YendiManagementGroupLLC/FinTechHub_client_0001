<?php
include "functs.php";
// define variables and set to empty values
$entries = array();
$valuations = array();
$credits_outstanding = array();
// fetch all the articles and their current inventory levels 
$entries = select_article_levels_from_db();
// fetch the internal value at risk (total assets value) from db
$valuations = select_valuations_from_db();
$valuation = explode(separator(), $valuations[0]);
// fetch the external value at risk (credits outstanding) from db
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
<h1>Inventory Report</h1>
<table>
	<tr>
		<th>KPI</th>
		<th>Measure</th>
	</tr>
	<tr>
		<td>Valuation Date</td>
		<td><?php echo get_formatted_date(($valuation[0]));?></td>
	</tr>
	<tr>
		<td>Valuation Total</td>
		<td style="text-align: right;"><?php echo number_format((double)$valuation[1], 2);?></td>
	</tr>
	<tr>
		<td>Outstanding Credit</td>
		<td style="text-align: right;"><?php echo number_format((double)$amount_outstanding, 2);?></td>
	</tr>
</table>
<br><br>
<?php
if (count($entries) > 0) {
?>
<table>
	<tr>
		<th>Last Updated Date</th>
		<th>Article</th> 
		<th>Remaining Quantity</th>
	</tr>
	<?php
	foreach($entries as $entry) { 
		$parts = explode(separator(), $entry);
		?>
	<tr>
		<td style="text-align: right"><?= get_formatted_date(($parts[0])) ?></td>
		<td><?= $parts[1] ?></td>
		<td style="text-align: right"><?= number_format((int)$parts[2]) ?></td>
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
