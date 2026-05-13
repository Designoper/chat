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

	<title>Chat público</title>
	<meta name="description" content="Habla con todo el mundo">

	<link href="./manifest.jsonc" rel="manifest">

	<link href="./assets/img/icons/favicon.svg" rel="icon" type="image/svg+xml">
	<link href="./assets/img/icons/icon-512.png" rel="icon" type="image/png">
	<link href="./assets/img/icons/icon-512.png" rel="apple-touch-icon" type="image/png">

	<link href="./assets/fonts/rubik/rubik.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/layout.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/chat.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/chat-publico.js" type="module"></script>

	<script src="./sw-register.js" type="module"></script>

</head>

<body>

	<main id="main">

		<h1></h1>

		<output></output>

		<section>

			<a href="./sala-principal.php">
				<svg viewBox="0 0 122.88 108.06">
					<path d="M63.94,24.28a14.28,14.28,0,0,0-20.36-20L4.1,44.42a14.27,14.27,0,0,0,0,20l38.69,39.35a14.27,14.27,0,0,0,20.35-20L48.06,68.41l60.66-.29a14.27,14.27,0,1,0-.23-28.54l-59.85.28,15.3-15.58Z" />
				</svg>
			</a>

			<form name="crear-mensaje">

				<textarea placeholder="Mensaje" name="contenido" autocomplete="off" minlength="1" maxlength="255" required></textarea>

				<!-- <label for="soundfile">Audio</label>
				<input type="file" id="soundfile" name="audio" capture="user" accept="audio/*">

				<label for="videofile">Video</label>
				<input type="file" id="videofile" name="video" capture="user" accept="video/*">

				<label for="imagefile">Image</label>
				<input type="file" id="imagefile" capture="user" accept="image/*" /> -->

				<button>
					<svg viewBox="0 0 512 512">
						<path
							d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
					</svg>
				</button>

			</form>

		</section>

	</main>

</body>

</html>