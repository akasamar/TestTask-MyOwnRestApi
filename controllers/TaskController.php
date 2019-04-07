<?php

include_once ROOT . '/models/task.php';

class TaskController
{
	public function actionGetUserInfo($params = [])
	{
		$obj = new Task();
		$obj->workWithTask();
	}
}