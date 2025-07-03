<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  header("Allow: POST");
  die;
}

if (checkLogged()) {
  setcookie("token", "", [
    "expires" => time() - 60 * 60 * 24 * 7,
    "path" => "/",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Lax",
  ]);
}
