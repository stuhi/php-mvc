<?php
namespace Stuhi\Mvc\Attributes;

use \Attribute;
use Stuhi\Mvc\Session;

#[Attribute]
class AUTH
{
    public bool $result;

    public function __construct(array $roles = array())
    {
        $this->result = Session::isAuth() && (count($roles) == 0  || Session::hasRoles($roles));
    }
}
