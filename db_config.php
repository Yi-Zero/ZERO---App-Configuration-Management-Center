<?php
$servername = "127.0.0.1";
$username = "Software_date";
$password = "CcZ7kmb2hACazYfB";
$dbname = "software_date";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}



