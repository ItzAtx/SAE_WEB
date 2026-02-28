<?php
	function connex($param, $base){
		include_once($param.".inc.php");
		$dsn="mysql:host=".MYHOST.";
		dbname=".$base;
		$user=MYUSER;
		$pass=MYPASS;

		try{
			$idcom = new PDO($dsn,$user,$pass);
			return $idcom;
		}
		catch(PDOException $except){
			die('Erreur : ' . $except->getMessage());
		}
	}
?>