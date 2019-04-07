<?php


class Router
{
	private $routes;

	public function __construct()
	{
		$routesPath = ROOT.'/configs/routes.php';
		$this->routes = include($routesPath);
	}

	private function getURI()
	{
		if (!empty($_SERVER['REQUEST_URI']))
			$uri = trim($_SERVER['REQUEST_URI'], '/');
		if (empty($uri))
			header('Location: /');
		$uri = strtok($uri, '?');
		return $uri;
	}

	public function run() 
	{
		$uri = $this->getURI();
		foreach ($this->routes as $key => $path)
			if (preg_match("~^$key$~", $uri))
			{
				$internalRoute = preg_replace("~^$key$~", $path, $uri); 

				$segments = explode('/', $internalRoute);
				$controllerName = array_shift($segments).'Controller';
				$controllerName = ucfirst($controllerName);
				$actionName = 'action' . ucfirst(array_shift($segments));
				$params = $segments;	
				$controllerFile = ROOT . '/controllers/' .$controllerName . '.php';

				if (!file_exists($controllerFile))
					exit("404 - Not object");
				include_once($controllerFile);
				$controllerObject = new $controllerName;
				$result = $controllerObject->$actionName($params);
				break ;
			}
	}
}

?>