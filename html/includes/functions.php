<?php
  session_start();
  $db = new PDO("mysql:host=" . DB_HOST . ";dbname=tjatter_db;charset=utf8mb4", DB_USER, DB_PASSWORD);
?>