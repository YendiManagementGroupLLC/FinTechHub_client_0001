<?php
include "functs.php";
// define variables and set to empty values
$article_id = $quantity = $price = $mode = "";
$articles = array();
$balances = array();
$active_form_key = "edit";
// validate incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	print_array($_POST);
	
	$article_id = sanitize_input($_POST["article_id"]);
	if (empty($article_id) || $article_id == "-1") {
		$article_id_Err = "Article required";
	}
	
	$quantity = sanitize_input($_POST["quantity"]);
	if (empty($quantity)) {
		$quantity_Err = "Quantity required";
	}
	
	$price = sanitize_input($_POST["price"]);
	if (empty($price)) {
		$price_Err = "Price required";
	}
	
	$mode = sanitize_input($_POST["mode"]);
	set_telemetry_data(("Mode : " . $mode));
}
// proceed here with edit mode, add new inventory management record
if ($mode == $active_form_key) {
	if (empty($article_id) || $article_id == "-1" || empty($quantity) || empty($price)) {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		$balances = select_article_threshold_from_db($article_id);
		foreach($balances as $balance) { 
			$parts = explode(separator(), $balance);
			$curr_balance = (int)$parts[2];
			if ($curr_balance - (int)$quantity < 0) {
				$threshold_Err = "[ERROR] :: Only " . $curr_balance . " available, cannot move " . $quantity . "<br><br>";
			}
		}
		// use the fact that no error was generated to drive whether to allow operation to proceed or not
		if (empty($threshold_Err)) {
			save_inventory_data($article_id, $quantity, $price);	
			// okay, let us refresh the balances now
			$balances = select_article_threshold_from_db($article_id);
		}
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
<h1>Manage Inventory</h1>
<?php 
if ($mode == $active_form_key && count($balances) > 0) { ?>
<table>
	<tr>
		<th>Date</th>
		<th>Article</th> 
		<th>Remaining Quantity</th>
	</tr>
	<?php
	foreach($balances as $balance) { 
		$parts = explode(separator(), $balance);
		?>
	<tr>
		<td><?= get_formatted_date(($parts[0])) ?></td>
		<td><?= $parts[1] ?></td>
		<td style="text-align: right"><?= number_format((int)$parts[2]) ?></td>
	</tr>
	<?php
	} ?>
</table>
<?php
} ?>
<p><span class="error"><?php echo $threshold_Err;?>* required field</span></p>
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
				$parts = explode(separator(), $article); ?>
			<option value="<?= $parts[0] ?>"><?= $parts[1] ?></option>
			<?php
			} ?>
			</select>
		</div>
	</div>	
	<div class="row">
		<div class="col-25">
			<label for="quantity">Quantity<span class="error">* <?php echo $quantity_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="quantity" id="quantity" placeholder="Enter article quantity...">
		</div>
	</div>	
	<div class="row">
		<div class="col-25">
			<label for="price">Price<span class="error">* <?php echo $price_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="price" id="price" placeholder="Enter article price...">
		</div>
	</div>	
	<div class="row">
		<div class="col-25">
			<input type="hidden" name="mode" id="mode" value="<?php echo $active_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Update Inventory">
		</div>
	</div>
</form>
</div>
<hr>
<br>
<br>
<iframe src="navigation.php" style="border:none;height:250px;width:100%;"></iframe>
<br><br><span class="telemetry"><?php echo get_telemetry_data();?></span></body>
</html>
