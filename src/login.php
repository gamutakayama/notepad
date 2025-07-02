<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: 0");

function redirect()
{
  $redirect = "/";
  if (isset($_GET["redirect"])) {
    $redirect = urldecode($_GET["redirect"]);
    if (!preg_match("#^/.*$#", $redirect)) {
      $redirect = "/";
    }
  }
  header("Location: $redirect");
  die;
}

function canLogin($loginFailFilePath)
{
  $today = date("Y-m-d");

  $data = ["date" => $today, "count" => 0];

  if (file_exists($loginFailFilePath)) {
    $json = file_get_contents($loginFailFilePath);
    $data = json_decode($json, true);
  }

  return $data["date"] !== $today || $data["count"] < 3;
}

function saveLoginFail($loginFailFilePath)
{
  $today = date("Y-m-d");

  $data = ["date" => $today, "count" => 0];

  if (file_exists($loginFailFilePath)) {
    $json = file_get_contents($loginFailFilePath);
    $data = json_decode($json, true);

    if ($data["date"] === $today) {
      $data["count"]++;
    } else {
      $data = ["date" => $today, "count" => 1];
    }
  } else {
    $data["count"] = 1;
  }

  file_put_contents($loginFailFilePath, json_encode($data));
}

function sendMessage($success)
{
  if (!TELEGRAM_BOT_TOKEN || !TELEGRAM_CHAT_ID) {
    return;
  }

  $text = "<b>" . SITE_TITLE . " - " . $_SERVER["SERVER_NAME"] . "</b>\n\n";
  $text .= "<b>" . ($success ? "✅ Login Successful" : "❌ Login Failed") . "</b>\n\n";
  $text .= "Time: " . gmdate("Y-m-d\TH:i:s\Z") . "\n";
  $text .= "IP: " . getClientIP();

  $params = [
    "chat_id" => TELEGRAM_CHAT_ID,
    "text" => $text,
    "parse_mode" => "HTML",
  ];

  $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage?" . http_build_query($params);
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_TIMEOUT, 5);
  curl_exec($curl);
  curl_close($curl);
}

function getClientIP()
{
  if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    return $_SERVER["HTTP_CF_CONNECTING_IP"];
  }

  if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    return trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
  }

  return $_SERVER["REMOTE_ADDR"] ?? "";
}

$directory = __DIR__ . "/../_notes";

checkDirectory($directory);

$loginFailFilePath = "$directory/login_fail.json";

$failed = false;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  if (checkLogged()) {
    redirect();
  }
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!PRIVATE_MODE || !USER_ID || !USERNAME || !PASSWORD || !JWT_KEY) {
    http_response_code(500);
    die;
  }

  if (canLogin($loginFailFilePath)) {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === USERNAME && $password === PASSWORD) {
      generateToken();
      sendMessage(true);
      redirect();
    } else {
      $failed = true;
      saveLoginFail($loginFailFilePath);
      sendMessage(false);
    }
  } else {
    $failed = true;
  }
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml" />
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png" />
  <link rel="manifest" href="/public/manifest.json" />
  <title>Sign in to <?= SITE_TITLE; ?></title>
  <link rel="stylesheet" href="/public/css/index.css" />
  <link rel="stylesheet" href="/public/css/login.css" />
</head>

<body>
  <img alt="<?= SITE_TITLE; ?>" class="logo" height="48" loading="lazy" src="/public/images/favicon.svg">
  <h1>Sign in to <?= SITE_TITLE; ?></h1>
  <?php if ($failed): ?>
    <div class="error">Incorrect username or password.</div>
  <?php endif; ?>
  <form action="" id="form" method="POST">
    <label for="username">Username</label>
    <input autocapitalize="off" autocomplete="username" autocorrect="off" autofocus="autofocus" id="username" name="username" required="required" type="text" />
    <label for="password">Password</label>
    <input autocomplete="current-password" id="password" name="password" required="required" type="password" />
    <input type="submit" value="Sign in" />
  </form>
  <script>
    const form = document.getElementById("form");
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const submitButton = form.querySelector('input[type="submit"]');
      submitButton.disabled = true;
      submitButton.value = "Signing in…";

      form.submit();
    });
  </script>
</body>

</html>