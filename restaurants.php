<?php
	include 'Utils.php';
	include 'DatabaseHandler.php';
	include 'RestaurantsTable.php';
	header('Content-Type: application/json');

	$dbHandler = DatabaseHandler::getInstance();
	$resultArray = array();

	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$resultArray = getRestaurants($dbHandler);
	}
	else{
		$resultArray = array('isError' => true, 'errorMsg' => 'Wrong method');
	}
	
	echo json_encode($resultArray);
	exit(0);



	function getRestaurants($dbHandler){
		$resultArray = array('isError' => false, 'errorMsg' => '');
		$sQuery = 'SELECT * FROM ' . RestaurantsTable::TABLE_NAME . 
						' ORDER BY id ASC';
		$resultArray['restaurants'] = $dbHandler->executeSelect($sQuery, array());
		return $resultArray;
	}