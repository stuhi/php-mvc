<?php
namespace Mvc;

use \DateTime;
use \ReflectionClass;
use \ReflectionMethod;

class Route
{
    public static function startApp(array $controllerNamespaces, string $singletonFunction = '', string $defaultController = 'Home', string $defaultAction = 'index')
    {
        $method = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));
        $isPost = $method == 'Post';

        $urlArray = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
        $countUrlArray = count($urlArray);

        $countControllerNamespaces = count($controllerNamespaces);
        $controllerClass = "{$controllerNamespaces[0]}\\{$defaultController}Controller";
        $actionName = $defaultAction  . ($method != 'Get' ? $method : '');

        $args = array();

        for ($i = 1; $i < $countUrlArray; $i++)
        {
            $item = $urlArray[$i];
            if ($item === '') continue;

            if ($i == 1) 
            {
                $controllerSet = false;
                for ($j = 0; $j < $countControllerNamespaces; $j++)
                {
                    $controllerClassCandidate = "{$controllerNamespaces[$j]}\\" . self::normalize($item, true) . 'Controller';
                    if (class_exists($controllerClassCandidate)) 
                    {
                        $controllerClass = $controllerClassCandidate;
                        $controllerSet = true;
                        break;
                    }
                }
                if (!$controllerSet) $args[] = $item;
            }
            else if ($i == 2) 
            {
                $actionNameCandidate = self::normalize($item, false) . ($method != 'Get' ? $method : '');
                if (method_exists($controllerClass, $actionNameCandidate)) $actionName = $actionNameCandidate;
                else $args[] = $item;
            }
            else $args[] = $item;
        }

        $controller = ($singletonFunction != '') ? $controllerClass::$singletonFunction() : new $controllerClass();
        $reflectionClass = new ReflectionClass($controllerClass);
        $reflectionMethod = new ReflectionMethod($controllerClass, $actionName);

        $hasAttributeContent = false;
        if (self::runAttribute($reflectionClass, $hasAttributeContent) && self::runAttribute($reflectionMethod, $hasAttributeContent))
        {
            $reflectionParams = $reflectionMethod->getParameters();
            $countParams = count($reflectionParams);
            if ($countParams == 0) $controller->$actionName();
            else
            {
                $params = array();
                $count = $isPost ? $countParams : count($args);
                for ($i = 0; $i < $count; $i++)
                {
                    $reflectionParam = $reflectionParams[$i];
                    $name = $reflectionParam->getName();
                    $type = $reflectionParam->getType()->getName();
                    $value = null;
                    if ($hasAttributeContent)
                    {
                        $value = file_get_contents('php://input'); 
                        $count = $i;
                    }
                    else if ($type == 'array')
                    {
                        if ($isPost) 
                        {
                            $value = $_REQUEST;
                            $count = $i;
                        }
                        else
                        {
                            $value = array();
                            for (; $i < $count; $i++)
                            {
                                $value[] = $args[$i];
                            }
                        }
                    }
                    else $value = $isPost ? $_REQUEST[$name] : $args[$i];

                    if (($value == null || $value == 'null') && $reflectionParam->getType()->allowsNull())
                    {
                        $params[] = null;
                    }
                    else
                    {
                        switch ($type)
                        {
                            case 'array':
                            case 'string': $params[] = $value; break;
                            case 'int': $params[] = intval($value); break;
                            case 'bool': $params[] = boolval($value); break;
                            case 'DateTime': $params[] = new DateTime($value); break;
                        }
                    }
                }
                $reflectionMethod->invokeArgs($controller, $params);
            }
        }
        else
        {
            $controller->notFound();
        }
    }

    private static function normalize(string $item, bool $isClass) : string
    {
        $result = '';
        $args = explode('-', $item);
        $argsCount = count($args);
        for ($i = 0; $i < $argsCount; $i++)
        {
            if ($i == 0 && !$isClass) $result .= $args[$i];
            else $result .= ucfirst(strtolower($args[$i]));
        }
        return $result;
    }

    private static function runAttribute($reflection, &$hasAttributeContent) : bool
    {
        $reflectionAttrs = $reflection->getAttributes();
        $countAttrs = count($reflectionAttrs);
        $result = $countAttrs == 0;
        for ($i = 0; $i < $countAttrs; $i++)
        {
            if ($reflectionAttrs[$i]->getName() == 'Mvc\Attributes\CONTENT')
            {
                 $hasAttributeContent = true;
            }
            $listener = $reflectionAttrs[$i]->newInstance();
            $result = $listener->result;
        }
        return $result;
    }
}
