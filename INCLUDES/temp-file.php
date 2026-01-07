<?php 
//TO GET THE HASHED PASSWORD
include 'db-con.php';
echo password_hash('12345678', PASSWORD_DEFAULT);
?>