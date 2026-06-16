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
	<meta name="apple-mobile-web-app-title" content="Whatschat">

	<link href="./manifest.json" rel="manifest">

	<link href="./assets/img/icons/favicon.svg" rel="icon" type="image/svg+xml">
	<link href="./assets/img/icons/apple-touch-icon.png" rel="apple-touch-icon" type="image/png">

	<link href="./assets/fonts/rubik/static/rubik-regular.woff2" rel="preload" as="font" type="font/woff2" crossorigin>
	<link href="./assets/fonts/rubik/static/rubik-bold.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/sala-principal.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/sala-principal.js" type="module"></script>

</head>

<body>



	<main id="main">

		<header>

			<form method="POST" name="logout">
				<button>
					<svg viewBox="0 0 2481 2481">
						<path d="M1413.825 2045.88c97.57 97.57 97.57 255.99 0 353.55-97.57 97.57-255.989 97.57-353.549 0l-982.499-982.5c-97.631-97.63-97.631-255.92 0-353.55l982.499-982.499c97.56-97.566 255.979-97.566 353.549 0s97.57 255.987 0 353.553l-805.72 805.726 805.72 805.72z" />
						<path d="M2226.031 990.157c137.979 0 249.999 112.023 249.999 250.003s-112.02 250-249.999 250H254.552c-137.977 0-249.999-112.02-249.999-250s112.022-250.003 249.999-250.003h1971.479z" />
					</svg>
				</button>
			</form>

			<h1>Whatschat</h1>

			<!-- <form method="POST" name="deleteUsuario">
			<button>
				<svg viewBox="0 0 2481 2481">
					<path d="M1666.169 2480.309c0-460.107-372.979-833.086-833.086-833.086S-.003 2020.202-.003 2480.309h1666.172z" />
					<circle cx="1240.16" cy="1105" r="135.157" transform="matrix(3.49826 0 0 3.49826 -3505.3 -2819.93)" />
					<path d="M1662.123 128.975c-29.188-29.186-29.183-76.579-.005-105.766 29.189-29.187 76.583-29.187 105.771.001l689.482 689.483c29.188 29.188 29.183 76.58.005 105.77-29.189 29.189-76.583 29.182-105.771-.006l-689.482-689.482z" />
					<path d="M2351.597 23.215c29.186-29.188 76.579-29.183 105.766-.005 29.187 29.189 29.187 76.583-.001 105.771L1767.88 818.462c-29.188 29.188-76.58 29.183-105.77.005-29.189-29.189-29.182-76.583.006-105.771l689.482-689.482z" />
				</svg>
			</button>
		</form> -->

		</header>

		<output>

			<menu></menu>
			<menu></menu>

		</output>

		<form method="POST" name="createGrupo">
			<label for="nombre_grupo">Nuevo grupo</label>
			<input name="nombre_grupo" id="nombre_grupo" autocomplete="off" required maxlength="20">
			<button>Crear</button>
		</form>

	</main>

</body>

</html>