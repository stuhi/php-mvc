<?php
namespace Injix\Mvc\Attributes;

use \Attribute;
use Injix\Mvc\Session;

#[Attribute]
class AUTH
{
    public bool $result;

    public function __construct(array $roles = array())
    {
        $this->result = Session::isAuth() && (count($roles) == 0  || Session::hasRoles($roles));
    }
}