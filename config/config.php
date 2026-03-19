<?php
define("SITE_TITLE", getenv("SITE_TITLE") ?: "Notepad");

define("ENABLE_MARKDOWN", filter_var(getenv("ENABLE_MARKDOWN") ?? "1", FILTER_VALIDATE_BOOL));

define("PRIVATE_MODE", getenv("PRIVATE_MODE") ?: "");

define("USER_ID", getenv("USER_ID") ?: "");

define("USERNAME", getenv("USERNAME") ?: "");

define("PASSWORD", getenv("PASSWORD") ?: "");

define("JWT_KEY", getenv("JWT_KEY") ?: "");

define("TELEGRAM_BOT_TOKEN", getenv("TELEGRAM_BOT_TOKEN") ?: "");

define("TELEGRAM_CHAT_ID", getenv("TELEGRAM_CHAT_ID") ?: "");
