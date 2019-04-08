<?php

class Task
{
	private function prioritySorting($arr, &$mainArray)
	{
		$prior = ['low', 'normal', 'high'];
		$arr['order_by'] === 'desc' ? $prior = array_reverse($prior) : 0;
		for ($i = 0; $i < 3; $i++)
			$this->fillMainArray($mainArray, $this->orderTasks($arr, 'none', " AND priority = '$prior[$i]'"), $arr['email']);
	}

	private function fillMainArray(&$mainArray, $res, $arr)
	{
		$res->execute([$arr]);
		$arr2 = $res->fetchAll(PDO::FETCH_ASSOC);

		foreach ($arr2 as $task)
		{
			$id = $task['id'];
			unset($task['id']);
			$id_arr = [$id => $task];
			$mainArray[] = $id_arr;
		}
	}

	private function orderTasks($arr, $option, $sql = '')
	{
		if (!empty($arr['order_by']))
		{
			$asc = " ORDER BY $option ASC";
			$desc = " ORDER BY $option DESC";
			if ($option === 'none')
			{
				$asc = '';
				$desc = '';
			}
			if ($arr['order_by'] === 'asc')
				$res = Db::$connection->prepare(SQL_JOIN_TABLES . $sql . $asc);
			else if ($arr['order_by'] === 'desc')
				$res = Db::$connection->prepare(SQL_JOIN_TABLES . $sql . $desc);
			else
			{
				echo json_encode(['error' => 'Wrong input order parameters']);
				exit();
			}
		}
		else			
			$res = Db::$connection->prepare(SQL_JOIN_TABLES . $sql);
		return $res;
	}

	private function paginationTasks(&$mainArray)
	{
		$count = 0;
		$newArray = [];
		$addArray = [];
		$size = count($mainArray);
		for ($i = 0; $i < $size; $i++)
		{
			$addArray += $mainArray[$i];
			if (($i + 1) % 3 == 0)
			{
				$count++;
				$newArray['page' . $count] = $addArray;
				$addArray = [];
			}
			if (($i + 1) == $size)
			{
				$count++;
				$newArray['page' . $count] = $addArray;
				break;
			}
		} 
		$mainArray = array_filter($newArray);
	}

	private function getInfoTask($arr) //СОЗДАТЬ МАССИВ СУЩЕСТВУЮЩИХ И УДАЛЯТЬ ТЕ ЧТО УЖЕ ЕСТЬ
	{
		$this->showErrorManager($arr);
		$res = Db::$connection->prepare(SQL_IS_USER_EXIST);
		$res->execute([$arr['email']]);
		$count = $res->fetchColumn(); 
		if ($count)
		{
			$res = Db::$connection->prepare(SQL_JOIN_TABLES);
			$res->execute([$arr['email']]);
			if ($res->fetchColumn())
			{
				$mainArray = [];

				if (!empty($arr['sort_option']))
				{
					if ($arr['sort_option'] === 'priority')
						$this->prioritySorting($arr, $mainArray);
					else if ($arr['sort_option'] === 'date')
						$this->fillMainArray($mainArray, $this->orderTasks($arr, 'date'), $arr['email']);
					else if ($arr['sort_option'] === 'title')
						$this->fillMainArray($mainArray, $this->orderTasks($arr, 'title'), $arr['email']);
					else if ($arr['sort_option'] === 'mark')
						$this->fillMainArray($mainArray, $this->orderTasks($arr, 'mark_done'), $arr['email']);
					else
					{
						echo json_encode(['error' => 'Wrong input sort-option parameter']);
						exit();
					}
				}
				else
					$this->fillMainArray($mainArray, $this->orderTasks($arr, 'none'), $arr['email']); // если нет sorted то только order работает

				$this->paginationTasks($mainArray);
				echo json_encode(['owner' => $arr['email'], 'tasks' => $mainArray]);
			}
			else
				echo json_encode(['error' => 'There is no tasks yet']);
		}
		else
			echo json_encode(['error' => 'This user does not exist']);
	}

	private function showErrorManager($arr)
	{
		if ((count($arr) == 1 && array_key_exists('email', $arr)) ||
			(count($arr) == 3 && array_key_exists('email', $arr) && array_key_exists('sort_option', $arr)
			 && array_key_exists('order_by', $arr) && strlen($arr['sort_option']) && strlen($arr['order_by'])))
			return;
		else
		{
			echo json_encode(['error' => 'Input parameters have incorrect input or have empty fields']);
			exit();
		}
	}

	private function addErrorManager($arr)
	{
		if (count($arr) == 3 && array_key_exists('token', $arr) && array_key_exists('title', $arr) &&
			array_key_exists('priority', $arr) && strlen($arr['title']) && strlen($arr['priority']) && 
			(strtolower($arr['priority']) === 'low' || strtolower($arr['priority']) === 'normal' ||
			 strtolower($arr['priority']) === 'high'))
			return;
		else
		{
			echo json_encode(['error' => 'Input parameters have incorrect input or have empty fields']);
			exit();
		}
	}

	private function deleteErrorManager($arr)
	{
		if (count($arr) == 2 && array_key_exists('token', $arr) && array_key_exists('delete_task_id', $arr)
			 && strlen($arr['delete_task_id']))
			return;
		else
		{
			echo json_encode(['error' => 'Input parameters have incorrect input or have empty fields']);
			exit();
		}
	}

	private function markErrorManager($arr)
	{
		if (count($arr) == 2 && array_key_exists('token', $arr) && array_key_exists('mark_task_id', $arr)
			 && strlen($arr['mark_task_id']))
			;
		else
		{
			echo json_encode(['error' => 'Input parameters have incorrect input or have empty fields']);
			exit();
		}
		$res = Db::$connection->prepare(SQL_GET_TASK_BY_ID);
		$res->execute([$arr['mark_task_id']]);
		$count = $res->fetchColumn();
		if ($count)
		{
			$res = Db::$connection->prepare(SQL_GET_TASK_BY_ID);
			$res->execute([$arr['mark_task_id']]);
			$arr2 = $res->fetchAll(PDO::FETCH_ASSOC)[0];
			if ($arr2['mark_done'])
			{
				echo json_encode(['error' => 'The task with id ' . $arr['mark_task_id'] . ' had marked yet']);
				exit();
			}
		}

	}

	private function checkTokenLife($arr)
	{
		if ($arr['access_expired'] - time() < 0 || $arr['refresh_expired'] - time() < 0)
		{
			$res = Db::$connection->prepare(SQL_UPDATE_AC_TOKEN);
			$res->execute([Helper::makeRandom(10 ,$arr['email']), (time() + 3600), $arr['email']]);
			$res = Db::$connection->prepare(SQL_UPDATE_REF_TOKEN);
			$res->execute([Helper::makeRandom(10 ,$arr['email']), (time() + 2592000), $arr['email']]);
			echo json_encode(['error' => 'The token has expired or not exist']);
			exit();
		}
	}

	private function addNewTask($arr)
	{
		$this->addErrorManager($arr);
		$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
		$res->execute([$arr['token']]);
		$count = $res->fetchColumn(); 
		if ($count)
		{
			$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
			$res->execute([$arr['token']]);
			$arr2 = $res->fetchAll(PDO::FETCH_ASSOC)[0];

			$this->checkTokenLife($arr2);

			$res = Db::$connection->prepare(SQL_CREATE_TASK);
			$res->execute([$arr['title'], strtolower($arr['priority']), $arr2['id']]);
			echo json_encode(['Info' => 'The task was added with title ' . $arr['title']]);
		}
		else
			echo json_encode(['error' => 'The token has expired or not exist']);
	}

	private function deleteTask($arr)
	{
		$this->deleteErrorManager($arr);
		$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
		$res->execute([$arr['token']]);
		$count = $res->fetchColumn(); 
		if ($count)
		{
			$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
			$res->execute([$arr['token']]);
			$arr2 = $res->fetchAll(PDO::FETCH_ASSOC)[0];

			$this->checkTokenLife($arr2);

			$res = Db::$connection->prepare(SQL_GET_TASK_BY_ID);
			$res->execute([$arr['delete_task_id']]);
			$count = $res->fetchColumn();

			if ($count)
			{
				$res = Db::$connection->prepare(SQL_DELETE_TASK);
				$res->execute([$arr['delete_task_id']]);
				echo json_encode(['Info' => "The task with id " . $arr['delete_task_id'] . " successfully deleted"]);
			} 
			else
				echo json_encode(['error' => 'The task id has not found']); 
		}
		else
			echo json_encode(['error' => 'The token has expired or not exist']);
	}

	private function markTaskHasDone($arr)
	{
		$this->markErrorManager($arr);
		$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
		$res->execute([$arr['token']]);
		$count = $res->fetchColumn(); 
		if ($count)
		{
			$res = Db::$connection->prepare(SQL_GET_USER_BY_TOKEN);
			$res->execute([$arr['token']]);
			$arr2 = $res->fetchAll(PDO::FETCH_ASSOC)[0];

			$this->checkTokenLife($arr2);

			$res = Db::$connection->prepare(SQL_GET_TASK_BY_ID);
			$res->execute([$arr['mark_task_id']]);
			$count = $res->fetchColumn();

			if ($count)
			{
				$res = Db::$connection->prepare(SQL_UPDATE_MARK);
				$res->execute([$arr['mark_task_id']]);
				echo json_encode(['Info' => "The task with id had marked " . $arr['mark_task_id'] . " and have option as done"]);
			} 
			else
				echo json_encode(['error' => 'The task id has not found']); 
		}
		else
			echo json_encode(['error' => 'The token has expired or not exist']);
	}

	public function workWithTask()
	{
		$json = file_get_contents('php://input');
		$arr = json_decode($json, TRUE);
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
			$this->getInfoTask($arr);
		else if ($_SERVER['REQUEST_METHOD'] === 'POST')
			$this->addNewTask($arr);
		else if ($_SERVER['REQUEST_METHOD'] === 'DELETE')
			$this->deleteTask($arr);
		else if ($_SERVER['REQUEST_METHOD'] === 'PUT')
			$this->markTaskHasDone($arr);
		else
			echo json_encode(['error' => 'Incorrect http method']);
	}
}

