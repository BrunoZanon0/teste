<?php 

namespace Controllers;

class AuthController extends Controller{
        public function register()
    {
        echo json_encode(['msg' => 'Usuário registrado']);
    }

    public function login()
    {
        echo json_encode(['msg' => 'Login efetuado']);
    }
}