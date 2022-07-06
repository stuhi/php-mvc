<?php
namespace Injix\Mvc;

class Session
{
    public static function start()
    {
        date_default_timezone_set('UTC');
        session_start();
        $userHostAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!self::isset('REMOTE_ADDR')) self::set('REMOTE_ADDR', $userHostAddress);
        if (!self::isset('HTTP_USER_AGENT')) self::set('HTTP_USER_AGENT', $userAgent);
        if($userHostAddress != self::get('REMOTE_ADDR') || $userAgent != self::get('HTTP_USER_AGENT'))
        {
            session_destroy();
            self::set('REMOTE_ADDR', $userHostAddress);
            self::set('HTTP_USER_AGENT', $userAgent);
        }
    }

    public static function signIn(array $roles = array())
    {
        self::set('AUTH', session_name());
        self::set('ROLES', $roles);
    }

    public static function signOut()
    {
        session_destroy();
    }

    public static function isAuth() : bool
    {
        return self::isset('AUTH');
    }

    public static function getRoles() : array
    {
        return self::get('ROLES');
    }

    public static function hasRoles(array $roles) : bool
    {
        return self::isAuth() && count(self::getRoles()) > 0 && count(array_filter(self::getRoles(), function ($role) use (&$roles) { return in_array($role, $roles); } )) > 0;
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function unset($name)
    {
        if (self::isset($name)) unset($_SESSION[$name]);
    }

    public static function isset($name)
    {
        return isset($_SESSION[$name]);
    }

    public static function get($name)
    {
        return $_SESSION[$name];
    }
}