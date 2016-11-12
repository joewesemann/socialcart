<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class LoginController extends \Phalcon\Mvc\Controller{
    
    public function indexAction(){
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        
        if($this->session->get("auth_failed")){
            $this->view->setVar("notify", "failure");
        }
    }
}
