<?php
namespace Injix\Mvc;

use \DateTime;
use \ReflectionClass;
use \ReflectionMethod;

class Route
{
    public static function startApps(array $routing)
    {
        $key = '';
        $url = '';
        $link = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $keys = array_keys($routing);
        $count = count($keys);
        for ($i = 0; $i < $count; $i++)
        {
            if (str_starts_with($link, $keys[$i]))
            {
                $key = $keys[$i];
                $url = str_replace($key, '', $link);
                $i = $count;
            } 
        }
        if ($key != '') self::startApp($routing[$key], $url);
        else self::startApp(end($routing), $_SERVER['REQUEST_URI']);
    }

    public static function startApp(string $controllerNamespace, string $url, string $defaultController = 'Home', string $defaultAction = 'index')
    {
        $method = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));
        $isPost = $method == 'Post';

        $urlArray = explode('/', explode('?', $url)[0]);
        $countUrlArray = count($urlArray);

        $controllerClass = "{$controllerNamespace}\\{$defaultController}Controller";
        $actionName = $defaultAction;

        $args = array();

        for ($i = 1; $i < $countUrlArray; $i++)
        {
            $item = $urlArray[$i];
            if ($item === '') continue;

            if ($i == 1) 
            {
                $controllerClassCandidate = "{$controllerNamespace}\\" . self::normalize($item, true) . 'Controller';
                if (class_exists($controllerClassCandidate)) $controllerClass = $controllerClassCandidate;
                else $args[] = $item;
            }
            else if ($i == 2) 
            {
                $actionNameCandidate = self::normalize($item, false) . ($method != 'Get' ? $method : '');
                if (method_exists($controllerClass, $actionNameCandidate)) $actionName = $actionNameCandidate;
                else $args[] = $item;
            }
            else $args[] = $item;
        }

        $controller = new $controllerClass();
        $reflectionClass = new ReflectionClass($controllerClass);
        $reflectionMethod = new ReflectionMethod($controllerClass, $actionName);

        if (self::runAttribute($reflectionClass) && self::runAttribute($reflectionMethod))
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
                    if ($type == 'array')
                    {
                        $value = $isPost ? $_REQUEST : $args; 
                        $count = $i;
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

    private static function runAttribute($reflection) : bool
    {
        $reflectionAttrs = $reflection->getAttributes();
        $countAttrs = count($reflectionAttrs);
        $result = $countAttrs == 0;
        for ($i = 0; $i < $countAttrs; $i++)
        {
            $listener = $reflectionAttrs[$i]->newInstance();
            $result = $listener->result;
        }
        return $result;
    }
}
