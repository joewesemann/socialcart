<?php

use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Event;

class Security  extends Plugin {

    private $auth;
    private $session;

    /* constructor sets up session and parameters */
    public function __construct(){
        /* create session, set authorization parameter to false */
        $this->session = new Phalcon\Session\Adapter\Files();
        session_start(); 
        $this->session->set("auth_failed",false);
        /* check if session has been authorized, set to auth param */
	if($this->session->has("authorized")){
	    $this->auth = unserialize($this->session->get("authorized"));
        } else {
            $this->auth = false;
	}
    }

    /* check authentication */
    public function isAuthenticated(){
        /* true if already logged in and authenticated  */
        if( !empty( $this->auth ) && !empty( $this->auth->loggedin ) ){
            return true;
        }
        return false;
    }

    /* authenticate the user  */
    private function authenticate($username,$password,$auth_model){
        /* skip everything if already authenticated */
        if($this->isAuthenticated()){
            return false;
        }
		
        /* query the db for the user */
        $auth_results = $auth_model->findFirst(array("conditions" => "username like ?1 AND password = ?2", "bind" => array(1 => $username, 2 => MD5($password))));

        /* authorize the login */
    	if(!empty($auth_results->id)){
            $this->session->set("authorized", serialize((object) array('loggedin'=>true,'user_data'=>$auth_results)));
            $this->auth = unserialize($this->session->get("authorized"));
        } else {
            $user_results = $auth_model->findFirst(array("conditions" => "username like ?1", "bind" => array(1 => $username)));
            if(empty($user_results->id)){
                /* username or password was wrong. failure  */
                $this->session->set("auth_failed",true);
            }
        }
    }
	
    /* before every action is executed */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher){
        $controller = $dispatcher->getControllerName();
       
        /* if you are attempting to access login controller, allow it to execute  */
        if($controller == "login" || $controller == "register"){
            return;
        }

        /* get the username/password from the post  */
        $request = new \Phalcon\Http\Request();
        $username = $request->getPost("username");
        $password = $request->getPost("password");
        $auth_model = new User();
            
        /* authenticate if attempt to login has been made */
        if(!empty($username) && !empty($password)){
            $this->authenticate($username,$password,$auth_model);
        }

        /* logout auth */
        if($controller == "logout"){
            $this->session->set("authorize", serialize((object) array()));
            $response = new \Phalcon\Http\Response();
            $response->redirect()->sendHeaders();
            exit();
        }
		
        /* if authentication failed for some reason */
        if(!$this->isAuthenticated()){
            if($request->isAjax()){
                /* send failure response */
                $response = new \Phalcon\Http\Response();
                $response->setContent(json_encode(array('error'=>'invalid_auth')))->send();
                exit();
	    } else{
                /* stay on the login page  */
                $dispatcher->forward(
                    array(
                        'controller' => 'login',
                        'action'     => 'index'
                    )
                );
                return false;
            }
        }
    }
}
