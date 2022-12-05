<?php
namespace Stuhi\Mvc\Attributes;

use \Attribute;
use Stuhi\Mvc\Session;

#[Attribute]
class NOTAUTH
{
    public bool $result;

    public function __construct()
    {
        $this->result = !Session::isAuth();
    }
}
