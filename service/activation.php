<!DOCTYPE html>
<html lang="hr">
	<head>
		<title>Aktivacija korisničkog računa</title>
		<meta charset="UTF-8">
	    <meta name="author" content="Ivan Durlen">
	    <meta name="description" content="Aktivacijska stranica">
		<style type="text/css" media="screen">
			body{
				background-color: #d3d9de; . 
				color: #595959; 
				font-family: "Helvetica", "Times New Roman", "sans-serif";  
				font-size: 14px; 
			} 
			#content {text-align: center; margin: 5em 1em;}
			h1, h2 { color: #1a1a1a; font-family: Arial, "sanes-serif"} 
			h1{ font-size: 32px;} 
			h2{ font-size: 22px; color: #595959} 
	</style>
	</head>
	<body>
		<div id="content">
			<h1>Food ordering</h1>
			<h2>Aktivacija korisničkog računa</h2>
			
			<?php
				include 'Utils.php';
				include 'DatabaseHandler.php';
				include 'UsersTable.php';

				$dbHandler = DatabaseHandler::getInstance();

				$activationCode = isset($_GET['code']) ? $_GET['code'] : '';
				$query = 'SELECT ' . UsersTable::COL_ID .
							' FROM ' . UsersTable::TABLE_NAME . 
							' WHERE ' . UsersTable::COL_ACTIVATION_CODE . ' = ? ' .
							' AND ' . UsersTable::COL_CODE_VALID_UNTIL . ' >= CURRENT_TIMESTAMP';
				
				$user = $dbHandler->executeSelect($query, array($activationCode));
				
				if(count($user) > 0){
					$stmnt = 'UPDATE ' . UsersTable::TABLE_NAME .
								' SET ' . UsersTable::COL_IS_ACTIVE . ' = ? ' .
								'WHERE ' . UsersTable::COL_ID . ' = ?';
					if($dbHandler->execNonSelect($stmnt, array(1, $user[0][UsersTable::COL_ID]))){
						echo "<p>Aktivacija uspješna - možete se prijaviti u aplikaciju.</p>";
					}
					else{
						echo "<p>Greška prilikom aktivacije.</p>";	
					}
				}
				else{
					echo "<p> Kod nije valjan ili je istekao! </p>";
				}
			?>

		</div>
	</body>
</html>