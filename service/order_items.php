<?php
	include 'Utils.php';
	include 'DatabaseHandler.php';
	include 'OrderItemsTable.php';
	include 'OrdersTable.php';
	header('Content-Type: application/json');

	$dbHandler = DatabaseHandler::getInstance();
	$resultArray = array();

	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$resultArray = getOrdersItemsOfUser($dbHandler);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$resultArray = insertOrderItem($dbHandler);
	}
	else{
		$resultArray = array('isError' => true, 'errorMsg' => 'Wrong method');
	}
	
	echo json_encode($resultArray);
	exit(0);



	/**
	 * Returns all order items of user with user_id;
	 * @param  DatabaseHandler $dbHandler
	 * @return string - list of order items
	 */
	function getOrdersItemsOfUser($dbHandler){
		$resultArray = array('isError' => false, 'errorMsg' => '');
		
		if(!isset($_GET[OrdersTable::COL_USER_ID])){
			$resultArray['isError'] = false;
			$resultArray['errorMsg'] = 'Missing GET params';
		}
		else{
			$sQuery = 'SELECT * FROM ' . OrderItemsTable::TABLE_NAME . 
						' WHERE ' . OrderItemsTable::COL_ORDER_ID . ' IN ' .
							' (SELECT ' . OrdersTable::COL_ID . ' FROM ' . OrdersTable::TABLE_NAME .
							' WHERE ' . OrdersTable::COL_USER_ID . ' = ?)' .
						' ORDER BY ' . OrderItemsTable::COL_ORDER_ID . ' ASC';
			$resultArray['orderItems'] = $dbHandler->executeSelect($sQuery, array($_GET[OrdersTable::COL_USER_ID]));
		}

		return $resultArray;
	}



	function insertOrderItem($dbHandler){
		$paramsMandatory = array(
      		OrderItemsTable::COL_DISH_ID, OrderItemsTable::COL_ORDER_ID, OrderItemsTable::COL_QUANTITY
      	);
		
		$resultArray = Utils::isValidParams($_POST, $paramsMandatory);

		if(!$resultArray[Utils::STATUS_ERROR]){
			$aInsertParams = Utils::createInsertStatement($_POST, OrderItemsTable::TABLE_NAME);
			if(! $dbHandler->execNonSelect($aInsertParams[0], $aInsertParams[1])){
				$resultArray[Utils::ERROR_MSG] = $dbHandler->getLastError();
			}
		}

		return $resultArray;
	}