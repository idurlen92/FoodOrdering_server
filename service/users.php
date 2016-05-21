<?php
	include 'Utils.php';
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
	else if($_SERVER['REQUEST_METHOD'] === 'PUT'){
		$resultArray = updateUser($dbHandler);
	}
	else{
		$resultArray = array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => 'Wrong method');
	}
	
	echo json_encode($resultArray);
	exit(0);



	function getUser($dbHandler){
		$resultArray = Utils::isValidParams($_GET, array(UsersTable::COL_EMAIL, UsersTable::COL_PASSWORD));
		if($resultArray[Utils::STATUS_ERROR]){
			$resultArray['user'] = array();
		}
		else{
			$sQuery = 'SELECT ' . UsersTable::COL_ID . ',' . UsersTable::COL_FIRST_NAME .  ',' .
						UsersTable::COL_LAST_NAME . ',' . UsersTable::COL_EMAIL . ',' .
						UsersTable::COL_CITY . ',' . UsersTable::COL_ADDRESS . ',' .
						UsersTable::COL_BIRTH_DATE .
						' FROM ' . UsersTable::TABLE_NAME .
						' WHERE ' . UsersTable::COL_EMAIL . ' = ? '. 
						' AND ' . UsersTable::COL_PASSWORD . ' = ? ' .
						' AND ' . UsersTable::COL_IS_ACTIVE . ' = 1';
			$user = $dbHandler->executeSelect($sQuery, array($_GET[UsersTable::COL_EMAIL], 
			                                                                $_GET[UsersTable::COL_PASSWORD]));
			$resultArray['user'] = count($user) > 0 ? $user[0] : array();
		}
		
		return $resultArray;
	}



	function insertUser($dbHandler){
		$paramsMandatory = array(
      		UsersTable::COL_FIRST_NAME , UsersTable::COL_LAST_NAME, UsersTable::COL_EMAIL, UsersTable::COL_PASSWORD,
      		UsersTable::COL_CITY, UsersTable::COL_ADDRESS
      	);

		$resultArray = Utils::isValidParams($_POST, $paramsMandatory);

		if($resultArray[Utils::STATUS_ERROR]){
			$resultArray[Utils::INSERT_ID] = 0;
		}
		else{
			$insertCols = array();
			foreach ($_POST as $key => $value) {
				$insertCols[$key] = $value;
			}
			
			// -------- Generate activation code ---------
			$insertCols[UsersTable::COL_ACTIVATION_CODE] = Utils::generateActivationCode();

			// ----- Generate timestamp ----------
			$timestampArray = $dbHandler->executeSelect('SELECT DATE_ADD(NOW(), INTERVAL 48 HOUR) t', array());
			$timestamp = $timestampArray[0]['t'];

			$insertCols[UsersTable::COL_CODE_VALID_UNTIL] =  $timestamp;

			// --------- Perform insert ---------
			$aInsertParams = Utils::createInsertStatement($insertCols, UsersTable::TABLE_NAME);
			if($dbHandler->execNonSelect($aInsertParams[0], $aInsertParams[1])){
				$resultArray[Utils::INSERT_ID] = $dbHandler->getLastInsertId();
				Utils::sendActivationEmail($insertCols[UsersTable::COL_EMAIL], $insertCols[UsersTable::COL_FIRST_NAME], 
				                           $insertCols[UsersTable::COL_ACTIVATION_CODE]);
			}
			else{
				$resultArray[Utils::ERROR_MSG] = $dbHandler->getLastError();
				$resultArray[Utils::INSERT_ID] = -1;
			}
		}

		return $resultArray;
	}


	function updateUser($dbHandler){
		$urlPart = $_SERVER['REQUEST_URI'];
		$userId = substr($urlPart, strrpos($urlPart, '/') + 1);
		if(!is_numeric($userId)){
			return array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => 'No id in request URL');
		}

		$params = array();
		parse_str(file_get_contents("php://input"), $params);
		if(count($params) === 0){
			return array(Utils::STATUS_ERROR => true, Utils::ERROR_MSG => 'No params');	
		}

		$updateParams = Utils::createUpdateStatement($params, array(UsersTable::COL_ID => $userId),
                        UsersTable::TABLE_NAME);
		if($dbHandler->execNonSelect($updateParams[0], $updateParams[1])){
				$resultArray[Utils::UPDATE_STATE] = true;
			}
			else{
				$resultArray[Utils::ERROR_MSG] = $dbHandler->getLastError();
				$resultArray[Utils::UPDATE_STATE] = false;
			}
    	return $resultArray;
	}
