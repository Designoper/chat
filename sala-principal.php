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

			<a href="./configuracion.php">
				<svg viewBox="0 0 2481 2481">
					<path d="M1482.104 23.818c-159.743-31.743-324.154-31.743-483.897 0 26.431 132.969-20.005 231.582-139.352 295.839-129.842 38.94-232.396 2.056-307.705-110.65a1239.96 1239.96 0 0 0-342.146 342.146c112.707 75.309 149.59 177.863 110.65 307.705-64.257 119.346-162.87 165.783-295.839 139.352-31.743 159.743-31.743 324.154 0 483.897 132.969-26.431 231.582 20.005 295.839 139.352 38.94 129.842 2.056 232.396-110.65 307.705a1239.96 1239.96 0 0 0 342.146 342.146c75.309-112.707 177.863-149.59 307.705-110.65 119.346 64.257 165.783 162.87 139.352 295.839 159.743 31.743 324.154 31.743 483.897 0-26.431-132.969 20.005-231.582 139.352-295.839 129.842-38.94 232.396-2.056 307.705 110.65a1239.96 1239.96 0 0 0 342.146-342.146c-112.707-75.309-149.59-177.863-110.65-307.705 64.257-119.346 162.87-165.783 295.839-139.352 31.743-159.743 31.743-324.154 0-483.897-132.969 26.431-231.582-20.005-295.839-139.352-38.94-129.842-2.056-232.396 110.65-307.705a1239.96 1239.96 0 0 0-342.146-342.146c-75.309 112.707-177.863 149.59-307.705 110.65-119.346-64.257-165.783-162.87-139.352-295.839zM1240.156 747.65c271.806 0 492.507 220.701 492.507 492.507s-220.701 492.507-492.507 492.507-492.507-220.701-492.507-492.507S968.349 747.65 1240.156 747.65z" />
				</svg>
				<!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path d="M259.1 73.5C262.1 58.7 275.2 48 290.4 48L350.2 48C365.4 48 378.5 58.7 381.5 73.5L396 143.5C410.1 149.5 423.3 157.2 435.3 166.3L503.1 143.8C517.5 139 533.3 145 540.9 158.2L570.8 210C578.4 223.2 575.7 239.8 564.3 249.9L511 297.3C511.9 304.7 512.3 312.3 512.3 320C512.3 327.7 511.8 335.3 511 342.7L564.4 390.2C575.8 400.3 578.4 417 570.9 430.1L541 481.9C533.4 495 517.6 501.1 503.2 496.3L435.4 473.8C423.3 482.9 410.1 490.5 396.1 496.6L381.7 566.5C378.6 581.4 365.5 592 350.4 592L290.6 592C275.4 592 262.3 581.3 259.3 566.5L244.9 496.6C230.8 490.6 217.7 482.9 205.6 473.8L137.5 496.3C123.1 501.1 107.3 495.1 99.7 481.9L69.8 430.1C62.2 416.9 64.9 400.3 76.3 390.2L129.7 342.7C128.8 335.3 128.4 327.7 128.4 320C128.4 312.3 128.9 304.7 129.7 297.3L76.3 249.8C64.9 239.7 62.3 223 69.8 209.9L99.7 158.1C107.3 144.9 123.1 138.9 137.5 143.7L205.3 166.2C217.4 157.1 230.6 149.5 244.6 143.4L259.1 73.5zM320.3 400C364.5 399.8 400.2 363.9 400 319.7C399.8 275.5 363.9 239.8 319.7 240C275.5 240.2 239.8 276.1 240 320.3C240.2 364.5 276.1 400.2 320.3 400z" />
				</svg> -->
			</a>

		</header>

		<output>

			<menu></menu>
			<menu></menu>
			<menu></menu>

		</output>

		<section>
			<form method="POST" name="createGrupo">
				<input name="nombre_grupo" id="nombre_grupo" placeholder="Crear grupo" autocomplete="username" maxlength="20" required>
				<button>
					<svg viewBox="0 0 800 800">
						<circle cx="400" cy="400" r="400" />
						<path d="M420.234 137.001c22.346 0 40.461 18.116 40.461 40.462v161.848h161.848c22.347 0 40.462 18.115 40.462 40.461v40.462c0 22.346-18.115 40.461-40.462 40.461H460.695v161.848c0 22.347-18.115 40.462-40.461 40.462h-40.462c-22.346 0-40.461-18.115-40.461-40.462V460.695H177.463c-22.346 0-40.462-18.115-40.462-40.461v-40.462c0-22.346 18.116-40.461 40.462-40.461H339.31V177.463c0-22.346 18.115-40.462 40.461-40.462h40.462z" />
					</svg>
				</button>
			</form>

			<form method="POST" name="solicitarContacto">
				<input name="codigo_contacto" id="codigo_contacto" placeholder="Código contacto" autocomplete="off" minlength="6" maxlength="6" required>
				<button>
					<svg viewBox="0 0 800 800">
						<circle cx="400" cy="400" r="400" />
						<path d="M420.234 137.001c22.346 0 40.461 18.116 40.461 40.462v161.848h161.848c22.347 0 40.462 18.115 40.462 40.461v40.462c0 22.346-18.115 40.461-40.462 40.461H460.695v161.848c0 22.347-18.115 40.462-40.461 40.462h-40.462c-22.346 0-40.461-18.115-40.461-40.462V460.695H177.463c-22.346 0-40.462-18.115-40.462-40.461v-40.462c0-22.346 18.116-40.461 40.462-40.461H339.31V177.463c0-22.346 18.115-40.462 40.461-40.462h40.462z" />
					</svg>
				</button>
			</form>
		</section>

	</main>

</body>

</html>