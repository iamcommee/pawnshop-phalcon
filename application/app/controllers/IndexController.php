<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\Controller;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("เข้าสู่ระบบ ");
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
    
        $this->assets->addCss('bootstrap/css/bootstrap.css');    
        $this->assets->addCss('css/style/style.css');  
        $this->assets->addJs('jquery/jquery-3.3.1.js');
        $this->assets->addJs('js/style/style.js');

        $owner = Owner::findFirst('1');
        $this->view->setVars(
            [
                "storename"      =>  $owner->storename,
            ]
        );

        if($this->session->get("user_id")){
            $this->response->redirect("main/");
        }
    }
    
    public function authenticationAction(){
        $username  = $this->request->getPost('username');
        $password  = $this->request->getPost('password');
        $user = User::findFirstByusername($username);
        if($this->request->isPost())
        {
            if($user)
            {
                if($this->security->checkHash($password, $user->password))
                {
                    $this->session->set("user_id", $user->user_id); 
                    $this->session->set("user", $user->firstname.' '.$user->lastname); 
                    $this->session->set("role", $user->role); 
                    $this->response->redirect("main/");
                }
                else
                {
                    $this->flashSession->error("ชื่อผู้ใช้ หรือ รหัสผ่าน ไม่ถูกต้อง");
                    return $this->response->redirect("");
                }
            }
            else
            {
                $this->flashSession->error("ชื่อผู้ใช้ หรือ รหัสผ่าน ไม่ถูกต้อง");
                return $this->response->redirect("");
            }
        }
    }


    public function logoutAction(){
        $this->session->destroy(); 
        $this->response->redirect("");
    }

    
    public function signupAction(){
        $user = new User();
        $user->firstname = $this->request->getPost("firstname");
        $user->lastname = $this->request->getPost("lastname");
        $user->username = $this->request->getPost("username");
        $user->password = $this->security->hash($this->request->getPost('password'));
        $user->role   = $this->request->getPost("role");;
        $user->save();
        return $this->response->redirect("main");
    }
    
    public function error404Action(){
        $this->tag->setTitle("| ประเสริฐรัตน์บริการ");
        $this->tag->prependTitle("ไม่พบหน้าที่ต้องการ ");
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
    
        $this->assets->addCss('bootstrap/css/bootstrap.css');    
        $this->assets->addCss('css/style/style.css');   
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function unauthorizedAction()
    {
        
    }


}