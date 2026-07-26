<?php

$server = 'mysql';
$username = 'student';
$password = 'student';
$schema = 'uon_website';

$pdo = new PDO('mysql:dbname=' . $schema . ';host=' . $server, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>