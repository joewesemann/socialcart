<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {
        $this->dispatcher->forward([
            'controller' => 'feed',
            'action' => 'index'
        ]);
    }

}
