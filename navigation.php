<?php
include "functs.php";
// https://www.w3schools.com/Colors/colors_names.asp
?>
<html>
<head><title><?php echo get_app_menu_name();?></title>
<link rel="stylesheet" type="text/css" href="appstyle.css">
</head>
<body>
<h3>A C T I V I T I E S</h3> 
(1) <a href="invmgmt.php" target="_parent">Manage Inventory</a>
<br> 
(2) <a href="invbrws.php" target="_parent">Browse Inventory</a>
<br> 
(3) <a href="moneymgmt.php" target="_parent">Add Bank Payment/Credit Line</a>
<br> 
(4) <a href="artmgmt.php" target="_parent">Manage Articles/Tally Card</a>
<br>
<h3>R E P O R T I N G</h3>
(1) <a href="invrpt.php" target="_parent">Inventory Report</a>
<br> 
(2) <a href="creditrpt.php" target="_parent">Creditors Report</a>
<br> 
(3) <a href="bankrpt.php" target="_parent">Bank Payments Report</a>
</body>
</html>
