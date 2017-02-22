<?php

namespace CycomiBrowser\Controller;

use \Fuel\Core\Controller;
use \Fuel\Core\Response;
use \Cycomi\Controller\Base;

class System extends Controller
{
    use Base;

    public function action_index()
    {
        Response::redirect($this->_make_global_url(''));
    }
    public function action_404()
    {
        Response::redirect($this->_make_global_url('404.php'));
    }
}
