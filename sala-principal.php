<?php

declare(strict_types=1);

include_once __DIR__ . '/models/universal/Auth.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="text-scale" content="scale">

	<title>Chat</title>
	<meta name="description" content="description">

	<link href="./manifest.json" rel="manifest">

	<link href="./assets/img/icons/favicon.svg" rel="icon" type="image/svg+xml">
	<link href="./assets/img/icons/icon-512.png" rel="icon" type="image/png">
	<link href="./assets/img/icons/icon-512.png" rel="apple-touch-icon" type="image/png">

	<link href="./assets/fonts/rubik/rubik.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/layout.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/index.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/Usuario.js" type="module" async></script>

	<script src="./sw-register.js" defer></script>

</head>

<body>

	<main id="main">

		<menu>
			<li><a href="./chat.php">Chat general</a></li>
			<li><a href="./sala-chat-directo.php">Chat directo</a></li>
		</menu>

	</main>

</body>

</html>