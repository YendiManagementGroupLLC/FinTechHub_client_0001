<?php
include "functs.php";
// define variables and set to empty values
$article_id = $quantity = $price = $mode = "";
$articles = array();
// setup the various form keys for multi-functions
$change_tally_card_data_Msg = "";
$change_tally_card_form_key = "change_tally_card";
$add_article_form_key = "add_article";
$update_article_form_key = "update_article";
// validate incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	print_array($_POST);
	
	$mode = sanitize_input($_POST["mode"]);
	set_telemetry_data(("Mode : " . $mode));
	
	if ($mode == $change_tally_card_form_key) {
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
	}
}
// proceed here with edit mode, add new inventory management record
if ($mode == $change_tally_card_form_key) {
	if (empty($article_id) || $article_id == "-1" || empty($quantity) || empty($price)) {
		set_telemetry_data(("Invalid form data - ignoring request!"));
	}
	else {
		//change_tally_card_data($article_id, $quantity, $price);
		$change_tally_card_data_Msg = "Tally Card updated successfully";
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
<h1>Tally Card</h1>
<p><span class="error">* required field</span></p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="article_id">Article <span class="error">* <?php echo $article_id_Err;?></span></label>			
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
			<label for="quantity">Quantity <span class="error">* <?php echo $quantity_Err;?></span></label>
		</div>
		<div class="col-75">
			<input type="text" name="quantity" id="quantity" placeholder="Enter article quantity...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<label for="price">Price <span class="error">* <?php echo $price_Err;?></span></label>
		</div>
		<div class="col-75">
			<input type="text" name="price" id="price" placeholder="Enter article price...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<?php echo $save_bank_payment_data_Msg;?>
			<input type="hidden" name="mode" id="mode" value="<?php echo $change_tally_card_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Change Tally Card">
		</div>
	</div>
</form>
<hr>
<h1>Manage Articles</h1>
<p><span class="error"><?php echo $threshold_Err;?>* required field</span></p>
<h2>Add New Article</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="name">New Article Name<span class="error">* <?php echo $name_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="name" id="name" placeholder="New article name...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<input type="hidden" name="mode" id="mode" value="<?php echo $add_article_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Add New Article">
		</div>
	</div>
</form>
<?php 
if (count($articles) > 0) { ?>
<h2>Update Existing Article</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="row">
		<div class="col-25">
			<label for="article_id">Existing Article<span class="error">* <?php echo $article_id_Err;?></span></label>			
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
			<label for="name">Updated Name<span class="error">* <?php echo $name_Err;?></span></label>			
		</div>
		<div class="col-75">
			<input type="text" name="name" id="name" placeholder="Updated name...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<input type="hidden" name="mode" id="mode" value="<?php echo $update_article_form_key;?>">
		</div>
		<div class="col-75">
			<input type="submit" value="Update Name">
		</div>
	</div>
</form>
<?php
} 
else { ?>
<h2>No Articles Found!</h2>
<?php
} ?>
</div>
<hr>
<br>
<br>
<iframe src="navigation.php" style="border:none;height:250px;width:100%;"></iframe>
<br><br><span class="telemetry"><?php echo get_telemetry_data();?></span></body>
</html>
