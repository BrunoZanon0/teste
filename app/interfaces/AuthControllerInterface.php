<?php 

namespace Interfaces;

Interface AuthControllerInterface{
    public function register(): void;

    public function login(): void;

    public function verifyToken():void;

    public function logout(): void;
}