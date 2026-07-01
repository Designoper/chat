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

	<title>Whatschat</title>
	<meta name="description" content="Habla con todo el mundo">
	<meta name="apple-mobile-web-app-title" content="Whatschat">

	<link href="./manifest.json" rel="manifest">

	<link href="./assets/img/icons/favicon.svg" rel="icon" type="image/svg+xml">
	<link href="./assets/img/icons/apple-touch-icon.png" rel="apple-touch-icon" type="image/png">

	<link href="./assets/fonts/rubik/static/rubik-regular.woff2" rel="preload" as="font" type="font/woff2" crossorigin>
	<link href="./assets/fonts/rubik/static/rubik-bold.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/chat.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/chat.js" type="module"></script>

</head>

<body>

	<main id="main">

		<header>

			<div>

				<a href="./sala-principal.php">
					<svg viewBox="0 0 2481 2481">
						<path d="M1413.825 2045.88c97.57 97.57 97.57 255.99 0 353.55-97.57 97.57-255.989 97.57-353.549 0l-982.499-982.5c-97.631-97.63-97.631-255.92 0-353.55l982.499-982.499c97.56-97.566 255.979-97.566 353.549 0s97.57 255.987 0 353.553l-805.72 805.726 805.72 805.72z" />
						<path d="M2226.031 990.157c137.979 0 249.999 112.023 249.999 250.003s-112.02 250-249.999 250H254.552c-137.977 0-249.999-112.02-249.999-250s112.022-250.003 249.999-250.003h1971.479z" />
					</svg>
				</a>

				<h1></h1>

			</div>

		</header>

		<output></output>

		<section>

			<!-- <details open name="message"> -->
			<!-- <summary>Opciones</summary> -->
			<form method="POST">

				<textarea placeholder="Mensaje" name="contenido" autocomplete="off" minlength="1" maxlength="255" required></textarea>

				<button>
					<svg viewBox="0 0 512 512">
						<path
							d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
					</svg>
				</button>

			</form>
			<!-- </details> -->

			<!-- <details name="message"> -->
			<!-- <summary>Opciones</summary> -->

			<form method="POST">

				<!-- <label for="imagen">Imagen</label> -->
				<input type="file" id="imagen" name="imagen" accept="image/*" required>

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
			<!-- </details> -->
		</section>

	</main>

</body>

</html>