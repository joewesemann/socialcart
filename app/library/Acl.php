<?php

/* 
 * Acl object makes session data available 
 *
 */
class Acl{
    
    private $session;
    private $auth;

    /* setup the acl object that will contain the user information */
    public function __construct(){
        $this->session = new Phalcon\Session\Adapter\Files();
        if($this->session->has("authorized")){
            $this->auth = unserialize($this->session->get("authorized"));
        } else {
            $this->auth = false;
        }
    }

    /* supply the user data from the session  */
    public function getUser(){
        return !empty($this->auth->user_data)?$this->auth->user_data:array();
    }
}
