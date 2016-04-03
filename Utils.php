<?php

class Utils{

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


	public static function colsToCamelCase(array $arr){
		$resultArray = array();
		foreach($arr as $key => $value){
			$resultArray[Utils::toCamelCase($key)] = $value;
		}
		return $resultArray;
	}


	function isAssocArray(array $array) {
  		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}


}