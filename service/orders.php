<?php
	include 'Utils.php';
	include 'DatabaseHandler.php';
	include 'OrdersTable.php';
	header('Content-Type: application/json');

	$dbHandler = DatabaseHandler::getInstance();
	$resultArray = array();

	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$resultArray = getOrdersOfUser($dbHandler);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$resultArray = insertOrder($dbHandler);
	}
	else{
		$resultArray = array('isError' => true, 'errorMsg' => 'Wrong method');
	}
	
	echo json_encode($resultArray);
	exit(0);



	function getOrdersOfUser($dbHandler){
		$resultArray = array('isError' => false, 'errorMsg' => '');
		
		if(!isset($_GET[OrdersTable::COL_USER_ID])){
			$resultArray['isError'] = false;
			$resultArray['errorMsg'] = 'Missing GET params';
		}
		else{
			$sQuery = 'SELECT * FROM ' . OrdersTable::TABLE_NAME . 
						' WHERE ' . OrdersTable::COL_USER_ID . ' = ? ' .
						' ORDER BY id ASC';
			$resultArray['orders'] = $dbHandler->executeSelect($sQuery, array($_GET[OrdersTable::COL_USER_ID]));
		}

		return $resultArray;
	}



	function insertOrder($dbHandler){
		$paramsMandatory = array(
      		OrdersTable::COL_USER_ID, OrdersTable::COL_RESTAURANT_ID, OrdersTable::COL_ORDER_CITY,
      		OrdersTable::COL_ORDER_ADDRESS, OrdersTable::COL_ORDER_TIME, OrdersTable::COL_DELIVERY_TIME
      	);
		
		$resultArray = Utils::isValidParams($_POST, $paramsMandatory);

		if($resultArray[Utils::STATUS_ERROR]){
			$resultArray[Utils::INSERT_ID] = 0;
		}
		else{
			$aInsertParams = Utils::createInsertStatement($_POST, OrdersTable::TABLE_NAME);
			if($dbHandler->execNonSelect($aInsertParams[0], $aInsertParams[1])){
				$resultArray[Utils::INSERT_ID] = $dbHandler->getLastInsertId();
			}
			else{
				$resultArray[Utils::ERROR_MSG] = $dbHandler->getLastError();
				$resultArray[Utils::INSERT_ID] = -1;
			}
		}

		return $resultArray;
	}