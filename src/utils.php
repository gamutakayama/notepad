<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function checkPrivateMode($mode)
{
  if (!PRIVATE_MODE || !USER_ID || !USERNAME || !PASSWORD || !JWT_KEY) {
    return;
  }

  if (PRIVATE_MODE === "all") {
    checkLogged("");
  } else {
    $privateModes = explode(",", PRIVATE_MODE);
    if (in_array($mode, $privateModes)) {
      $location = "";
      if ($mode === "list" && !in_array("edit", $privateModes)) {
        $location = "/edit/";
      }
      checkLogged($location);
    }
  }
}

function checkLogged($location = null)
{
  if (!PRIVATE_MODE || !USER_ID || !USERNAME || !PASSWORD || !JWT_KEY) {
    return false;
  }

  try {
    if (!isset($_COOKIE["token"])) {
      throw new Exception("Token is missing!");
    }

    $token = $_COOKIE["token"];

    $jwt = JWT::decode($token, new Key(JWT_KEY, "HS256"));

    if ($jwt->sub !== USER_ID) {
      throw new Exception("Invalid token!");
    }

    return true;
  } catch (Exception $e) {
    if ($location === null) {
      return false;
    } else {
      if ($location === "") {
        $requestUri = $_SERVER["REQUEST_URI"];
        if ($requestUri === "/") {
          header("Location: /login");
        } else {
          header("Location: /login?redirect=" . urlencode($requestUri));
        }
      } else {
        header("Location: $location");
      }
      die;
    }
  }
}

function generateToken()
{
  $issuedAt = time();
  $expirationTime = $issuedAt + 60 * 60 * 24 * 7;

  $payload = [
    "iss" => "https://notepad.com",
    "aud" => "https://notepad.com",
    "iat" => $issuedAt,
    "exp" => $expirationTime,
    "sub" => USER_ID,
  ];

  $jwt = JWT::encode($payload, JWT_KEY, "HS256");

  setcookie("token", $jwt, [
    "expires" => $expirationTime,
    "path" => "/",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Strict",
  ]);
}

function checkDirectory($directory)
{
  if (!is_dir($directory) && !mkdir($directory)) {
    http_response_code(500);
    die;
  }
}

function checkNoteName()
{
  if (
    !isset($_GET["note"]) ||
    mb_strlen($_GET["note"], "UTF-8") > 64 ||
    !preg_match("/^[a-zA-Z0-9\x{4e00}-\x{9fa5}_-]+$/u", $_GET["note"])
  ) {
    header("Location: /edit/" . substr(str_shuffle("234579abcdefghjkmnpqrstwxyz"), -4));
    die;
  }
}
