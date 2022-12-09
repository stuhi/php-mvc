<?php
namespace Mvc\Attributes;

use \Attribute;
use Mvc\Session;

#[Attribute]
class NOTAUTH
{
    public bool $result;

    public function __construct()
    {
        $this->result = !Session::isAuth();
    }
}
