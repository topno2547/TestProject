<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "cockfighting_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ : " . $conn->connect_error);
}

$conn->set_charset("utf8");