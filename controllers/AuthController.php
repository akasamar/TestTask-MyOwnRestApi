<?php

include_once ROOT . '/models/autorization.php';

class AuthController
{
	public function actionSignUp($params)
	{
		$obj = new Autorization();
		$obj->makeReg();
	}

	public function actionSignIn($params)
	{
		$obj = new Autorization();
		$obj->makeAuth();
	}

	public function actionError($params)
	{
		echo json_encode(['error' => 'Page not found 404']);
	}
}
?>
