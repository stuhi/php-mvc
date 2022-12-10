<?php
namespace Mvc\Attributes;

use \Attribute;

#[Attribute]
class CONTENT
{
    public bool $result;

    public function __construct()
    {
        $this->result = true;
    }
}
