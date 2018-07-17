<?php
include "functs.php";
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
<p><span class="error">* required field</span></p>
<form action="/client_0001/invmgmt.php" method="post">
	<div class="row">
		<div class="col-25">
			<label for="article_id">Article <span class="error">*</span></label>			
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
			<label for="quantity">Quantity <span class="error">*</span></label>
		</div>
		<div class="col-75">
			<input type="text" name="quantity" id="quantity" placeholder="Enter article quantity...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<label for="price">Price <span class="error">*</span></label>
		</div>
		<div class="col-75">
			<input type="text" name="price" id="price" placeholder="Enter article price...">
		</div>
	</div>
	<div class="row">
		<div class="col-25">
			<input type="hidden" name="mode" id="mode" value="edit">
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
