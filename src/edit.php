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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml" />
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png" />
  <link rel="manifest" href="/public/manifest.json" />
  <title><?= $_GET["note"]; ?></title>
  <link rel="stylesheet" href="/public/css/github-markdown-5.8.1.min.css" />
  <link rel="stylesheet" href="/public/css/index.css" />
  <link rel="stylesheet" href="/public/css/edit.css" />
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
    <input id="input-file" style="display: none" type="file" />
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