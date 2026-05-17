<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';

new Usuario()->authBrowser();

?>

<!DOCTYPE html>
<html lang="es">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="text-scale" content="scale">

	<title>Chat</title>
	<meta name="description" content="description">

	<link href="./manifest.jsonc" rel="manifest">

	<link href="./assets/img/icons/favicon.svg" rel="icon" type="image/svg+xml">
	<link href="./assets/img/icons/icon-512.png" rel="icon" type="image/png">
	<link href="./assets/img/icons/icon-512.png" rel="apple-touch-icon" type="image/png">

	<link href="./assets/fonts/rubik/rubik.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/layout.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/sala-principal.css" rel="preload stylesheet" as="style">

	<script src="./sw-register.js" type="module"></script>

	<script src="./assets/js/realtimevideo/source-code.js" defer></script>
	<script src="./assets/js/realtimevideo/videollamada.js" type="module"></script>

</head>

<body>

	<main id="main">

		<h1>Sala de videollamada</h1>

		<video id="yo" autoplay playsinline controls muted></video>
		<video id="otro" autoplay playsinline controls></video>

		<button id="llamar">Llamar</button>

		<!-- <script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script> -->

		<a href="./sala-principal.php">Salir</a>

	</main>

</body>

</html>