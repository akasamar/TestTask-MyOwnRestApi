<?php
	try
	{
		$conn = new PDO("mysql:host=localhost;", "kasamara1_andrey", "kasamara111");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch (PDOException $e)
	{
		echo "DB ERROR CONNECTION";
		exit(0);
	}
	$result = $conn->prepare("show databases like 'admin_andrey'");
	$result->execute();
	$res = $result->rowCount();

	if (!$res)
	{
		$result = $conn->prepare("CREATE DATABASE IF NOT EXISTS `admin_andrey`");
		$result->execute();
		
		try
		{
			$conn = new PDO("mysql:host=localhost;dbname=kasamara1_andrey", "kasamara1_andrey", "kasamara111");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			echo "DB ERROR CONNECTION";
			exit(0);
		}

		$result = $conn->prepare("CREATE TABLE IF NOT EXISTS `tasks` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`title` varchar(255) NOT NULL,
								`priority` varchar(255) NOT NULL,
								`mark_done` int(11) NOT NULL DEFAULT '0',
								`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								`user_id` int(11) NOT NULL,
								PRIMARY KEY (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
		$result->execute();

		$result = $conn->prepare("CREATE TABLE IF NOT EXISTS `users` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `email` varchar(255) NOT NULL,
								  `password` varchar(255) NOT NULL,
								  `access_token` varchar(255) NOT NULL,
								  `access_expired` int(11) NOT NULL,
								  `refresh_token` varchar(255) NOT NULL,
								  `refresh_expired` int(11) NOT NULL,
								  PRIMARY KEY (`id`),
								  UNIQUE KEY `email` (`email`)
								) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
		$result->execute();
	}

?>

