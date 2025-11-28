<?php

$con = new mysqli("localhost","root","","sportswh");
$result = $con->query("select * from item");
$data = $result->fetch_all(MYSQLI_ASSOC);
echo "<pre>";
var_dump($data);





