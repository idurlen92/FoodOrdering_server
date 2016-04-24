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
		$resultArray = array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => '');
		
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



	/**
	 * Inserts multiple Order Items from json array string
	 * @param  [type] $dbHandler [description]
	 * @return [type]            [description]
	 */
	function insertOrderItem($dbHandler){
		$resultArray = array(Utils::STATUS_ERROR => false, Utils::ERROR_MSG => '');

		$arrayKey = 'orderItems';
		if(!isset($_POST[$arrayKey])){
			return array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => "Missing argument '{$arrayKey}'");
		}

		// ---------- Decode json to assoc array and put to DB ----------
		//orderItems=[{"dish_id":1,"order_id":1,"quantity":3},{"dish_id":2,"order_id":1,"quantity":3}]
		$orderItemsArray = json_decode($_POST['orderItems'], true);
		
		// ---------- Perform DB transaction ---------
		$paramsMandatory = array(
      		OrderItemsTable::COL_DISH_ID, OrderItemsTable::COL_ORDER_ID, OrderItemsTable::COL_QUANTITY
      	);
		$dbHandler->beginTransaction();

		// --------- Insert data ----------
		foreach($orderItemsArray as $index => $orderItem){
			$resultArray = Utils::isValidParams($orderItem, $paramsMandatory);
			if($resultArray[Utils::STATUS_ERROR]){
				return $resultArray;
			}

			$aInsertParams = Utils::createInsertStatement($orderItem, OrderItemsTable::TABLE_NAME);
			if(! $dbHandler->execNonSelect($aInsertParams[0], $aInsertParams[1])){
				//---------- Roll back transaction and return error ----------
				$dbHandler->rollBackTransaction();
				return array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => 'INSERT: ' . $dbHandler->getLastError());
			}
		}

		// ---------- Commit transaction and return data ----------
		$dbHandler->commitTransaction();
		return $resultArray;
	}