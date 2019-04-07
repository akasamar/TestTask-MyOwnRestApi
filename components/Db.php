<?php

class Db
{
	public static $connection;

	public static function getConnection()
	{
		if (!self::$connection)
		{
			$paramsPath = ROOT . '/configs/database.php';
			$params = include($paramsPath);
			try
			{
				self::$connection = new PDO("mysql:host=" . $params['host'] .";dbname=" . $params['dbname'], $params['user'], $params['password']);
				self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			}catch (PDOException $e)
			{
				echo "DB ERROR CONNECTION";
				exit(0);
			}
		}
		return self::$connection;
	}

	public static function infoUser($email)
	{
		$sth = self::$connection->prepare(SQL_GET_USER_WITH);
		$sth->execute([$email]);
		$result = $sth->fetchAll();
		if (isset($result[0]))
			return $result[0];
		return false;
	}
}
