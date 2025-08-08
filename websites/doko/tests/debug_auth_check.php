<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/Controllers/AuthController.php';

$pdo = Database::getInstance()->getConnection();
$email = 'login@test.com';
$pwd = 'testpassword123';
$username = 'loginuser_' . uniqid();

$pdo->exec("DELETE FROM users WHERE email = 'login@test.com'");
$hash = password_hash($pwd, PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (username,email,password,first_name,last_name,phone,role,status,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())")
    ->execute([$username,$email,$hash,'Test','User','123','customer','active']);

$auth = new AuthController();
$res = $auth->login($email, $pwd);
var_dump($res);
