<?php
	include 'DatabaseHandler.php';
	include 'UsersTable.php';
	header('Content-Type: application/json');

	$dbHandler = DatabaseHandler::getInstance();
	$resultArray = array();

	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$resultArray = getUser($dbHandler);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$resultArray = insertUser($dbHandler);
	}
	
	echo json_encode($resultArray);
	exit(0);



	function getUser($dbHandler){
		$resultArray = array('isError' => false, 'errorMsg' => '', 'user' => array());
		if(!isset($_GET[UsersTable::$COL_EMAIL]) || !isset($_GET[UsersTable::$COL_PASSWORD])){
			$resultArray['isError'] = true;
            $resultArray['errorMsg'] = 'Missing GET params';
		}
		else{
			$sQuery = 'SELECT * FROM ' . UsersTable::$TABLE_NAME .
						' WHERE ' . UsersTable::$COL_EMAIL . ' = ? '. 
						' AND ' . UsersTable::$COL_PASSWORD . ' = ?';
			$resultArray['user'] = $dbHandler->executeSelect($sQuery, array($_GET[UsersTable::$COL_EMAIL], $_GET[UsersTable::$COL_PASSWORD]));
		}
		
		return $resultArray;
	}



	function insertUser($dbHandler){
		$tableColumns = array(
      		UsersTable::$COL_FIRST_NAME , UsersTable::$COL_LAST_NAME, UsersTable::$COL_EMAIL, UsersTable::$COL_PASSWORD,
      		UsersTable::$COL_CITY, UsersTable::$COL_ADDRESS, UsersTable::$COL_BIRTH_DATE
      	);

		$resultArray = array('isError' => false, 'errorMsg' => '');

		$sInsertCols = 'INSERT INTO ' . UsersTable::$TABLE_NAME . '(';
		$sInsertVals = ' VALUES(';
		$aInsertParams = array();
		
		$iLimit = count($tableColumns) - 1;

		foreach ($tableColumns as $key => $value) {
			if(!isset($_POST[$value])){
				$resultArray['isError'] = true;
				$resultArray['errorMsg'] = 'Missing POST params: ' . $value;
				break;
			}
			$sInsertCols .= $value . ($key < $iLimit ? ',' : ')');
			$sInsertVals .= '?' . ($key < $iLimit ? ',' : ')');
			$aInsertParams[$key] = $_POST[$value];
		}

		if(!$resultArray['isError']){
			$sInsertStatement = $sInsertCols . $sInsertVals;
			if($dbHandler->execNonSelect($sInsertStatement, $aInsertParams)){
				$resultArray['id'] = $dbHandler->getLastInsertId();
			}
			else{
				$resultArray['id'] = -1;
			}
		}

		return $resultArray;
	}

