<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: 0");

checkNoteName();
checkPrivateMode("view");

$directory = __DIR__ . "/../_notes/" . $_GET["note"];
$mdFilename = "$directory.md";

$content = "";
if (is_file($mdFilename)) {
  $content = htmlspecialchars(file_get_contents($mdFilename), ENT_QUOTES, "UTF-8");
}

$filenames = [];
if (is_dir($directory)) {
  $filenames = array_values(array_filter(
    array_diff(scandir($directory), [".", ".."]),
    fn($filename) => is_file("$directory/$filename"),
  ));
}

if (!$content && !$filenames) {
  header("Location: /edit/" . $_GET["note"]);
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
  <link rel="stylesheet" href="/public/css/view.css" />
</head>

<body>
  <div class="menu">
    <span class="title"><?= $_GET["note"]; ?></span>
    <div class="menu-item">
      <a href="">File</a>
      <div class="menu-dropdown">
        <a class="menu-dropdown-item" href="/edit/">New</a>
        <a class="menu-dropdown-item" href="/edit/<?= $_GET["note"]; ?>">Edit</a>
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
  <div class="markdown-body" id="markdown"></div>
  <?php if ($filenames): ?>
    <div class="markdown-body" id="files"></div>
  <?php endif; ?>
  <script src="/public/js/markdown-it-14.1.0.min.js"></script>
  <script src="/public/js/markdown-it-task-lists-2.1.0.min.js"></script>
  <script src="/public/js/split-1.6.5.min.js"></script>
  <script src="/public/js/common.js"></script>
  <script>
    const getCopyRawText = () => content;

    let content = <?= json_encode($content); ?>;
    const doc = new DOMParser().parseFromString(content, "text/html");
    content = doc.documentElement.textContent;

    const md = initMarkdownIt();

    document.getElementById("markdown").innerHTML = md.render(content);

    let files = <?= json_encode($filenames); ?>;
    if (files.length) {
      files = files
        .map((file) => {
          return `[${file}](/file/<?= $_GET["note"]; ?>/${encodeURIComponent(file)})`;
        })
        .join(" , ");
      document.getElementById("files").innerHTML = md.render(`Files\n\n${files}`);

      initSplit(["#markdown", "#files"], "vertical", "v");
    }
  </script>
</body>

</html>