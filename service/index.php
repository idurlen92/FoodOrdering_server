<?php

include 'Utils.php';
include 'DatabaseHandler.php';

$dbHandler = DatabaseHandler::getInstance();
$date = $dbHandler->executeSelect('SELECT DATE_ADD(NOW(), INTERVAL 48 HOUR) t', array());
echo $date[0]['t'];