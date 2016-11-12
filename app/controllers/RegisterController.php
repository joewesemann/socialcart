<?php

use Phalcon\Mvc\Controller;

class RegisterController extends Controller
{

    public function indexAction()
    {
        $user = new User();
        $post = $this->request->getPost();

        foreach ($post as $key => $p_val) {
            if($key=='password'){
                $pw = md5($p_val);
                $user->password = $pw;
                continue;
            }
            $user->{$key} = $post[$key]; 
        }

        if (!$user->save()) {
            var_dump($user->getMessages());
        }
    }

}
