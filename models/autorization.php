<?php 
	class Autorization
	{
		private function getMemberCount($email)
		{
			$res = Db::$connection->prepare(SQL_IS_USER_EXIST);
			$res->execute([$email]);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$row = $res->fetch();
			$members = $row['count'];
			return $members;
		}

		private function checkForms($email, $pass)
		{
			if (!preg_match('/^([a-zA-Z0-9_-]+\.)*[a-zA-Z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/', $email))
			{
				echo json_encode(["error" => "The mail have to have a form like (example@google.com)"]);
				return 1;
			}
			if (!preg_match('/^\S{5,}$/', $pass))
			{
				echo json_encode(["error" => "The password have to have at least 5 characters"]);
				return 1;
			}
			return 0;
		}

		private function createDataMysql($email, $pass)
		{
			$res = Db::$connection->prepare(SQL_ADD_USER);
			$res->execute([$email, $pass, Helper::makeRandom(10 ,$email), (time() + 3600),Helper::makeRandom(10, $email),(time() + 2592000)]);
		}

		public function makeReg()
		{
			if ($_SERVER['REQUEST_METHOD'] === 'POST') 
			{
				$json = file_get_contents('php://input');
				$arr = json_decode($json, TRUE);

				if (count($arr) == 2 && !empty($arr['email']) && !empty($arr['password']))
				{
					if ($this->getMemberCount($arr['email']))
					{
						echo json_encode(["error" => "The email was registered before"]);
						return ;
					}
					else if ($this->checkForms($arr['email'], $arr['password']))
						return ;

					$this->createDataMysql($arr['email'], $arr['password']);
					$user = Db::infoUser($arr['email']);
					echo json_encode(["access_token" => $user['access_token'], "access_expire" => $user['access_expired'] - time(), 
								"refresh_token" => $user['refresh_token'], "refresh_expire" => $user['refresh_expired'] - time()]);
				}
				else
					echo json_encode(["error" => "Incorrect JSON input or incoming data"]);
			}
			else
				echo json_encode(["error" => "The invalid request method"]);
		}

		public function makeAuth()
		{
			if ($_SERVER['REQUEST_METHOD'] === 'GET')
			{
				$json = file_get_contents('php://input');
				$arr = json_decode($json, TRUE);

				if (count($arr) == 2 && isset($arr['email']) && isset($arr['password']))
				{
					if ($this->getMemberCount($arr['email']))
					{
						$user = Db::infoUser($arr['email']);

						if ($user['password'] === $arr['password'])
						{
							$res = Db::$connection->prepare(SQL_UPDATE_AC_TOKEN);
							$res->execute([Helper::makeRandom(10 ,$arr['email']), (time() + 3600), $arr['email']]);
							$res = Db::$connection->prepare(SQL_UPDATE_REF_TOKEN);
							$res->execute([Helper::makeRandom(10 ,$arr['email']), (time() + 2592000), $arr['email']]);
							
							$user = Db::infoUser($arr['email']);
							echo json_encode(["access_token" => $user['access_token'], "access_expire" => $user['access_expired'], 
								"refresh_token" => $user['refresh_token'], "refresh_expire" => $user['refresh_expired']]);
						}
						else
							echo json_encode(["error" => "The password is incorrect"]);
					}
					else
						echo json_encode(["error" => "User not found"]);
				}
				else
					echo json_encode(["error" => "Incorrect JSON input or incoming data"]);
			}
			else
				echo json_encode(["error" => "The invalid request method"]);
		}
	}

?>
