<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: 0");

$directory = __DIR__ . "/../_notes";

checkPrivateMode("list");
checkDirectory($directory);

$filenames = array_diff(scandir($directory), [".", ".."]);
$notes = array_values(array_unique(array_filter(
  array_map(fn($filename) => pathinfo($filename, PATHINFO_FILENAME), $filenames),
  fn($filename) => $filename,
)));
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
  <title>List</title>
  <link rel="stylesheet" href="/public/css/github-markdown-5.8.1.min.css" />
  <link rel="stylesheet" href="/public/css/index.css" />
</head>

<body>
  <div class="menu">
    <span class="title">Notepad</span>
    <a href="/edit/">New</a>
    <?php if (checkLogged()): ?>
      <a href="" id="logout">Logout</a>
    <?php endif; ?>
  </div>
  <div class="markdown-body" id="markdown"></div>
  <script src="/public/js/markdown-it-14.1.0.min.js"></script>
  <script>
    const logoutElement = document.getElementById("logout");
    if (logoutElement) {
      logoutElement.addEventListener("click", async (e) => {
        e.preventDefault();

        if (confirm("Do you really want to logout?")) {
          try {
            const response = await fetch("/logout", {
              method: "POST"
            });
            if (response.ok) {
              location.reload();
            } else {
              throw new Error();
            }
          } catch {
            alert("Logout failed!");
          }
        }
      });
    }

    const notes = <?= json_encode($notes); ?>;
    const list = notes.map((note, index) => `${index + 1}. [${note}](${note})`).join("\n");
    const hostedOn = "<?= HOSTED_ON; ?>";
    const hostedOnUrl = "<?= HOSTED_ON_URL; ?>";

    let content = `# List\n\n${list}`;
    if (hostedOn) {
      if (hostedOnUrl) {
        content = `${content}\n\nHosted on <a href="${hostedOnUrl}" target="_blank">${hostedOn}</a>`;
      } else {
        content = `${content}\n\nHosted on ${hostedOn}`;
      }
    }

    const md = window.markdownit({
      html: true
    });
    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>