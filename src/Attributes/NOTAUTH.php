<?php
namespace Injix\Mvc\Attributes;

use \Attribute;
use Injix\Mvc\Session;

#[Attribute]
class NOTAUTH
{
    public bool $result;

    public function __construct()
    {
        $this->result = !Session::isAuth();
    }
}