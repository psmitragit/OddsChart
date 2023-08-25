<?php

$host = 'localhost'; 

// $username = 'eppdxmmsqw'; 
// $password = '7k5B2NrbTa';
// $database = 'eppdxmmsqw';

$username = 'root'; 
$password = '';
$database = 'odds';

// Create connection
$conn = new mysqli($host, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
?>