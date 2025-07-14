<?php
// src/logout.php
require_once '../config/auth.php';

session_start();
session_destroy();
header('Location: index.php');
exit();
