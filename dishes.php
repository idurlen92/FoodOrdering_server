<?php
	include 'DatabaseHandler.php';
	include 'DishesTable.php';
	header('Content-Type: application/json');

	$dbHandler = DatabaseHandler::getInstance();
	$resultArray = array();

	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$resultArray = getDishTypes($dbHandler);
	}
	else{
		$resultArray = array('isError' => true, 'errorMsg' => 'Wrong method');
	}
	
	echo json_encode($resultArray);
	exit(0);


	function getDishTypes($dbHandler){
		$resultArray = array('isError' => false, 'errorMsg' => '');
		$sQuery = 'SELECT * FROM ' . DishesTable::TABLE_NAME . 
					' ORDER BY id ASC';
		$resultArray['dishes'] = $dbHandler->executeSelect($sQuery, array());
		return $resultArray;
	}