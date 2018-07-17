<?php
include "functs.php";
// define variables and set to empty values
$article_name = $article_id = $mode = "";
$article_total = 0.00;
$articles = array();
$entries = array();
$active_form_key = "fetch";
// validate incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	print_array($_POST);
	$article_id = sanitize_input($_POST["article_id"]);
	if (empty($article_id) || $article_id == "-1") {
		$article_id_Err = "Article is required";
	}
	
	$mode = sanitize_input($_POST["mode"]);
	set_telemetry_data(("Mode : " . $mode));
}
// proceed here with fetch mode, fetch inventory management records by article_id
if ($mode == $active_form_key) {
	set_telemetry_data((" Your inputs : " . $article_id));
	if (empty($article_id) || $article_id == "-1") {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		$entries = select_article_history_from_db($article_id);
	}
}
// the assumption is at this stage is that this is a fresh page being loaded, so let's load articles dropdown from database
$articles = select_articles_dropdown_from_db();
?>

<html>
<head><title><?php echo get_app_console_name();?></title>
<link rel="stylesheet" type="text/css" href="appstyle.css">
</head>
<body>
<hr>
<div class="container">
<h1>Browse Inventory</h1>
<p><span class="error">* required field</span></p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="article_id">Article<span class="error">* <?php echo $article_id_Err;?></span></label>			
		</div>
		<div class="col-75">
			<select name="article_id" id="article_id">
			<option value="-1" selected="selected">Choose an article...</option>
			<?php
			foreach($articles as $article) { 
				$parts = explode(separator(), $article);
				if ($parts[0] == $article_id) {
					$article_name = $parts[1];
				}
				?>
			<option value="<?= $parts[0] ?>"><?= $parts[1] ?></option>
			<?php
			} ?>
		</select>
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<?php 
			if ($mode == $active_form_key && count($entries) == 0) {
				echo $article_name . " has no transaction history!";
			}
			?>
			<input type="hidden" name="mode" id="mode" value="<?php echo $active_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="View History">
		</div>
	</div>
</form>
<?php 
if ($mode == $active_form_key && count($entries) > 0) { ?>
<h2>Article: <?php echo $article_name;?></h2>
<table>
	<tr>
		<th>Date</th>
		<th>Quantity</th> 
		<th>Price</th> 
		<th>Amount</th>
	</tr>
	<?php
	foreach($entries as $entry) { 
		$parts = explode(separator(), $entry);
		$article_total = $article_total + (double)$parts[3];
		?>
	<tr>
		<td><?= get_formatted_date(($parts[0])) ?></td>
		<td style="text-align: right"><?= number_format((int)$parts[1]) ?></td> 
		<td style="text-align: right"><?= number_format((double)$parts[2], 2) ?></td>
		<td style="text-align: right"><?= number_format((double)$parts[3], 2) ?></td>
	</tr>
	<?php
	} ?>
</table>
<h2>Total Amount: <?php echo number_format((double)$article_total, 2);?></h2>
<?php
} ?>
</div>
<hr>
<br>
<br>
<iframe src="navigation.php" style="border:none;height:250px;width:100%;"></iframe>
<br><br><span class="telemetry"><?php echo get_telemetry_data();?></span></body>
</html>
