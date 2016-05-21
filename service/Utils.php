<?php

class Utils{

	const ERROR_MSG = 'errorMessage';
	const INSERT_ID = 'insertId';
	const UPDATE_STATE = 'isUpdated';
	const STATUS_ERROR = 'isError';

	/**
	 * Transforms string to camel case string
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	public static function toCamelCase($str){
		if(strpos($str, '_') === false){
			return $str;
		}

		$camelStr = '';
		$parts = explode('_', $str);
		for($i=0; $i < count($parts); $i++){
			$camelStr .= ($i == 0 ? $parts[$i] : ucfirst($parts[$i]));
		}

		return $camelStr;
	}


	/**
	 * Transforms table column names (underscore notation) to camel case
	 * @param  array  $arr [description]
	 * @return [type]      [description]
	 */
	public static function colsToCamelCase(array $arr){
		$resultArray = array();
		foreach($arr as $key => $value){
			$resultArray[self::toCamelCase($key)] = $value;
		}
		return $resultArray;
	}


	/**
	 * Checks if array is oridinary, indexed array or associative array
	 * @param  array   $array [description]
	 * @return boolean        [description]
	 */
	public static function isAssocArray(array $array) {
  		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}



	/**
	 * Checks if parameters of REQUEST_METHOD are set by definition.
	 * @param  array   $valuesMap       [description]
	 * @param  array   $paramsMandatory [description]
	 * @return boolean                  [description]
	 */
	public static function isValidParams(array $valuesMap, array $paramsMandatory){
		foreach($paramsMandatory as $param){
			if(!isset($valuesMap[$param])){
				return array(self::STATUS_ERROR => true, self::ERROR_MSG => ('Missing param: ' . $param));
			}
		}
		return array(self::STATUS_ERROR => false, self::ERROR_MSG => '');
	}


	/**
	 * Creates insert statement.
	 * @param  array  $valuesMap [description]
	 * @param  string $tableName [description]
	 * @return array            [description]
	 */
	public static function createInsertStatement(array $valuesMap, $tableName){
		$sInsertCols = 'INSERT INTO ' . $tableName . '(';
		$sInsertVals = ' VALUES(';

		$iLimit = count($valuesMap) - 1;
		$aInsertParams = array();

		$j = 0;
		foreach ($valuesMap as $key => $value) {
			$sInsertCols .= $key . ($j < $iLimit ? ',' : ')');
			$sInsertVals .= '?' . ($j < $iLimit ? ',' : ')');
			$aInsertParams[$j++] = $value;
		}

		return array(($sInsertCols . $sInsertVals), $aInsertParams);
	}


	/**
	 * Creates update statement.
	 * @param  array  $valuesMap [description]
	 * @param  [type] $id        [description]
	 * @param  [type] $tableName [description]
	 * @return [type]            [description]
	 */
	public static function createUpdateStatement(array $valuesMap, array $whereParams, $tableName){
		$updateStmnt = 'UPDATE ' . $tableName . ' SET ';
		$updateParams = array();
		$count = count($valuesMap);
		$j = 0;

		foreach ($valuesMap as $key => $value) {
			$updateStmnt .= $key . ' = ? ' . ($j < ($count - 1) ? 'AND ' : '');
			$updateParams[$j++] = $value;
		}

		$updateStmnt .= ' WHERE ';

		$count = count($whereParams);
		$i = 0;
		foreach ($whereParams as $key => $value) {
			$updateStmnt .= $key . ' = ? ' . ($i++ < ($count - 1) ? 'AND ' : '');
			$updateParams[$j++] = $value;
		}

		return array($updateStmnt, $updateParams);
	}


	/**
	 * Generates activation code, used for registering a new user.
	 * @return [string] generated activation code
	 */
	public static function generateActivationCode(){
		$activationCode = '';
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 44; $i++) {
            $activationCode .= $characters[rand(0, strlen($characters)-1)];
        }

		$activationCode .= date('YmdHis');//16 chars

		return $activationCode;
	}


	/**
	 * Sends activation after registering a new user.
	 * @param  [type] $receiverEmail  [description]
	 * @param  [type] $name           [description]
	 * @param  [type] $activationCode [description]
	 * @return [type]                 [description]
	 */
	public static function sendActivationEmail($receiverEmail, $name, $activationCode){
		$headers = "From:" . strip_tags('info@food_ordering.hr') . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type:text/html;charset=UTF-8\r\n";

		$subject = 'Aktivacija korisničkog računa';

		$url = 'http://www.nikola-markotic.from.hr/food_ordering/service/activation.php?code=' . $activationCode;
		$message = 
		'<html>' .
			'<head>' .
				'<title>Aktivacija računa</title>' .
				'<style>' .
					'body{' .
						'background-color: #d3d9de;' . 
						'color: #595959;' .
						'font-family: "Helvetica", "Times New Roman", "sans-serif";' . 
						'font-size: 14px;' .
					'}' .
					'#content {text-align: center; margin: 5em 1em;}'.
					'h1, h2 { color: #1a1a1a; font-family: Arial, "sanes-serif"}' .
					'h1{ font-size: 32px;}' .
					'h2{ font-size: 22px; color: #595959}' .
					'a {' . 
						'text-decoration: none;' .
						'color: #1a1a1a;' .
						'font-weight: bold;' .
					'}' .
					'a:hover{' .
						'font-size: 18px;' .
					'}' .
				'</style>' .
			'</head>' .
			'<body>' .
				'<div id="content">' .
					'<h1> Food ordering </h1>' .
					'<h2>Aktivacija korisničkog računa</h1>' .
					'<p> Poštovani ' . $name . ', <br/>' . 
					'za aktivaciju Vašeg korisničkog računa kliknite na sljedeći ' .
					'<a href="' . $url . '" target="_blank"> link </a></p><br/>' .
					'<p> Vaš <span style="font-weight: bold;"> Food ordering </span> tim </p>' .
				'</div>' .
			'</body>' .
		'</html>';

		return mail($receiverEmail, $subject, $message, $headers);
	}

}