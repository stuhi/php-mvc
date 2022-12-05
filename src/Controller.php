<?php
namespace Stuhi\Mvc;

class Controller
{
    public function __construct()
    {
        Session::start();
    }

    public function view(Model $model)
    {
        require_once($model->_layout);
    }

    public function viewPartial(Partial $model)
    {
        require_once($model->_html);
    }

    public function getPartial(Partial $model) : string
    {
        ob_start();
        require_once($model->_html);
        $body = ob_get_contents();
        ob_end_clean();
        return $body;
    }

    public function notFound()
    {
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body style="text-align: center"><h1>Not Found</h1><div style="font-size: xxx-large;font-weight: bold;">404.</div><p>The requested URL was not found on this server.</p><hr></body></html>';
    }

    public function redirect(string $url = '/')
    {
        header("Location: {$url}");
    }
}
