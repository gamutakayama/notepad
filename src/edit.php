<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: 0");

$directory = __DIR__ . "/../_notes";

checkNoteName();
checkPrivateMode("edit");
checkDirectory($directory);

$directory .= "/" . $_GET["note"];
$mdFilename = "$directory.md";

function deleteDirectory($directory)
{
  if (!is_dir($directory)) {
    return false;
  }

  $filenames = array_diff(scandir($directory), [".", ".."]);

  foreach ($filenames as $filename) {
    $filePath = "$directory/$filename";

    if (is_dir($filePath)) {
      deleteDirectory($filePath);
    } else {
      unlink($filePath);
    }
  }

  return rmdir($directory);
}

function handleEdit($mdFilename, $text)
{
  $success = false;

  if (strlen($text)) {
    $success = file_put_contents($mdFilename, $text);
  } else {
    $success = unlink($mdFilename);
  }

  if (!$success) {
    http_response_code(500);
  }
}

function handleDelete($directory, $mdFilename)
{
  if (
    (is_dir($directory) && !deleteDirectory($directory)) ||
    (is_file($mdFilename) && !unlink($mdFilename))
  ) {
    http_response_code(500);
  }
}

function handleFiles($directory)
{
  header("Content-Type: application/json");

  if (is_dir($directory)) {
    $filenames = array_values(array_filter(
      array_diff(scandir($directory), [".", ".."]),
      fn($filename) => is_file("$directory/$filename"),
    ));

    echo json_encode($filenames);
  } else {
    echo json_encode([]);
  }
}

function handleFileUpload($directory)
{
  if (!is_dir($directory) && !mkdir($directory)) {
    http_response_code(500);
    return;
  }

  $originalName = basename($_FILES["file"]["name"]);
  $filename = "$directory/$originalName";

  $fileInfo = pathinfo($originalName);
  $counter = 1;
  while (file_exists($filename)) {
    $newName = $fileInfo["filename"] . "_" . $counter;
    if (!empty($fileInfo["extension"])) {
      $newName = $newName . "." . $fileInfo["extension"];
    }
    $filename = "$directory/$newName";
    $counter++;
  }

  if (!move_uploaded_file($_FILES["file"]["tmp_name"], $filename)) {
    http_response_code(500);
  }
}

function handleFileRemove($directory, $filename)
{
  $filename = $directory . "/" . basename($filename);

  if (
    !is_file($filename) ||
    !unlink($filename) ||
    (count(scandir($directory)) === 2 && !deleteDirectory($directory))
  ) {
    http_response_code(500);
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
    handleFileUpload($directory);
  } else {
    $data = json_decode(file_get_contents("php://input"), true);
    switch ($data["method"] ?? "") {
      case "edit":
        handleEdit($mdFilename, $data["text"] ?? "");
        break;
      case "delete":
        handleDelete($directory, $mdFilename);
        break;
      case "files":
        handleFiles($directory);
        break;
      case "fileRemove":
        handleFileRemove($directory, $data["filename"] ?? "");
        break;
      default:
        http_response_code(400);
        break;
    }
  }

  die;
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#f6f8fa" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#151b23" media="(prefers-color-scheme: dark)">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2048-2732.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1320-2868.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1206-2622.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2732-2048.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2388-1668.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2048-1536.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2360-1640.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2224-1668.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2160-1620.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2266-1488.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2868-1320.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2622-1206.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2796-1290.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2556-1179.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2532-1170.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2778-1284.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2436-1125.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2688-1242.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-2208-1242.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2048-2732.jpg" media="(prefers-color-scheme: dark) and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1668-2388.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1536-2048.jpg" media="(prefers-color-scheme: dark) and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1640-2360.jpg" media="(prefers-color-scheme: dark) and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1668-2224.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1620-2160.jpg" media="(prefers-color-scheme: dark) and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1488-2266.jpg" media="(prefers-color-scheme: dark) and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1320-2868.jpg" media="(prefers-color-scheme: dark) and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1206-2622.jpg" media="(prefers-color-scheme: dark) and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1290-2796.jpg" media="(prefers-color-scheme: dark) and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1179-2556.jpg" media="(prefers-color-scheme: dark) and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1170-2532.jpg" media="(prefers-color-scheme: dark) and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1284-2778.jpg" media="(prefers-color-scheme: dark) and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1125-2436.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1242-2688.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-828-1792.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1242-2208.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-750-1334.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-640-1136.jpg" media="(prefers-color-scheme: dark) and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2732-2048.jpg" media="(prefers-color-scheme: dark) and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2388-1668.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2048-1536.jpg" media="(prefers-color-scheme: dark) and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2360-1640.jpg" media="(prefers-color-scheme: dark) and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2224-1668.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2160-1620.jpg" media="(prefers-color-scheme: dark) and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2266-1488.jpg" media="(prefers-color-scheme: dark) and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2868-1320.jpg" media="(prefers-color-scheme: dark) and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2622-1206.jpg" media="(prefers-color-scheme: dark) and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2796-1290.jpg" media="(prefers-color-scheme: dark) and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2556-1179.jpg" media="(prefers-color-scheme: dark) and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2532-1170.jpg" media="(prefers-color-scheme: dark) and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2778-1284.jpg" media="(prefers-color-scheme: dark) and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2436-1125.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2688-1242.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1792-828.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-2208-1242.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1334-750.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="/public/images/apple-splash-dark-1136-640.jpg" media="(prefers-color-scheme: dark) and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="manifest" href="/public/manifest.json">
  <title><?= $_GET["note"] . " | " . SITE_TITLE; ?></title>
  <link rel="stylesheet" href="/public/css/github-markdown-5.8.1.min.css">
  <link rel="stylesheet" href="/public/css/index.css">
  <link rel="stylesheet" href="/public/css/edit.css">
</head>

<body>
  <div class="menu">
    <div id="status"></div>
    <span class="title"><?= $_GET["note"]; ?></span>
    <div class="menu-item">
      <a href="">File</a>
      <div class="menu-dropdown">
        <a class="menu-dropdown-item" href="/edit/">New</a>
        <a class="menu-dropdown-item" href="/<?= $_GET["note"]; ?>">View</a>
        <div class="menu-dropdown-divider"></div>
        <a class="menu-dropdown-item" href="" id="delete">Delete</a>
      </div>
    </div>
    <div class="menu-item">
      <a href="">Copy</a>
      <div class="menu-dropdown">
        <a class="menu-dropdown-item" href="" id="copy-raw">Raw</a>
        <a class="menu-dropdown-item" href="" id="copy-link">Link</a>
      </div>
    </div>
    <a href="/">List</a>
    <?php if (checkLogged()): ?>
      <a href="" id="logout">Logout</a>
    <?php endif; ?>
  </div>
  <div id="editor">
    <textarea autofocus id="textarea"><?php
                                      if (is_file($mdFilename)) {
                                        echo htmlspecialchars(file_get_contents($mdFilename), ENT_QUOTES, "UTF-8");
                                      }
                                      ?></textarea>
    <div class="markdown-body" id="markdown"></div>
  </div>
  <div id="file-drop">
    <span>
      Drop your file here, or
      <a id="browse" href="">browse</a>
      (Max <?= ini_get("upload_max_filesize"); ?>)
    </span>
    <input id="input-file" style="display: none" type="file">
    <div id="files"></div>
    <div id="loader">
      <div class="loader"></div>
    </div>
  </div>
  <script src="/public/js/markdown-it-14.1.0.min.js"></script>
  <script src="/public/js/markdown-it-task-lists-2.1.0.min.js"></script>
  <script src="/public/js/split-1.6.5.min.js"></script>
  <script src="/public/js/common.js"></script>
  <script src="/public/js/edit.js"></script>
</body>

</html>