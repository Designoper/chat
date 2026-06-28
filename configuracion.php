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

	<title>Configuración</title>
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
	<link href="./assets/css/configuracion.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/login.js" type="module"></script>

</head>

<body>

	<header>

		<a href="./sala-principal.php">
			<button>
				<svg viewBox="0 0 2481 2481">
					<path d="M1413.825 2045.88c97.57 97.57 97.57 255.99 0 353.55-97.57 97.57-255.989 97.57-353.549 0l-982.499-982.5c-97.631-97.63-97.631-255.92 0-353.55l982.499-982.499c97.56-97.566 255.979-97.566 353.549 0s97.57 255.987 0 353.553l-805.72 805.726 805.72 805.72z" />
					<path d="M2226.031 990.157c137.979 0 249.999 112.023 249.999 250.003s-112.02 250-249.999 250H254.552c-137.977 0-249.999-112.02-249.999-250s112.022-250.003 249.999-250.003h1971.479z" />
				</svg>
			</button>
		</a>

		<h1>Configuración</h1>

	</header>

	<main id="main">

		<form method="POST" name="cambiarNombre">
			<input id="nombre_usuario" name="nombre_usuario" placeholder="Nuevo nombre" autocomplete="username" maxlength="20" required>
			<button>
				<svg viewBox="0 0 800 667">
					<path d="M22.25 461.575c-29.668-29.995-29.668-78.989 0-108.984l39.279-39.058c13.297-13.418 31.426-20.975 50.316-20.975 19.439 0 38.045 8.002 51.417 22.109l69.394 71.996a35.43 35.43 0 0 0 51.402 0L637.702 22.122C651.083 8.01 669.698.007 689.145.004a70.91 70.91 0 0 1 50.868 21.517l38.145 38.567c28.922 29.85 28.922 77.933 0 107.783l-469.502 477.11a70.9 70.9 0 0 1-50.948 21.621c-18.882 0-36.999-7.548-50.296-20.953L22.25 461.575z" />
				</svg>
			</button>

			<output></output>

		</form>

		<form method="POST" name="cambiarPassword">
			<input id="password" name="password" type="password" placeholder="Nueva contraseña" autocomplete="new-password" maxlength="20" required>
			<button>
				<svg viewBox="0 0 800 667">
					<path d="M22.25 461.575c-29.668-29.995-29.668-78.989 0-108.984l39.279-39.058c13.297-13.418 31.426-20.975 50.316-20.975 19.439 0 38.045 8.002 51.417 22.109l69.394 71.996a35.43 35.43 0 0 0 51.402 0L637.702 22.122C651.083 8.01 669.698.007 689.145.004a70.91 70.91 0 0 1 50.868 21.517l38.145 38.567c28.922 29.85 28.922 77.933 0 107.783l-469.502 477.11a70.9 70.9 0 0 1-50.948 21.621c-18.882 0-36.999-7.548-50.296-20.953L22.25 461.575z" />
				</svg>
			</button>

			<output></output>

		</form>

		<form method="POST" name="deleteUsuario">
			<button>Eliminar cuenta
				<!-- <svg viewBox="0 0 2481 2481">
					<path d="M1666.169 2480.309c0-460.107-372.979-833.086-833.086-833.086S-.003 2020.202-.003 2480.309h1666.172z" />
					<circle cx="1240.16" cy="1105" r="135.157" transform="matrix(3.49826 0 0 3.49826 -3505.3 -2819.93)" />
					<path d="M1662.123 128.975c-29.188-29.186-29.183-76.579-.005-105.766 29.189-29.187 76.583-29.187 105.771.001l689.482 689.483c29.188 29.188 29.183 76.58.005 105.77-29.189 29.189-76.583 29.182-105.771-.006l-689.482-689.482z" />
					<path d="M2351.597 23.215c29.186-29.188 76.579-29.183 105.766-.005 29.187 29.189 29.187 76.583-.001 105.771L1767.88 818.462c-29.188 29.188-76.58 29.183-105.77.005-29.189-29.189-29.182-76.583.006-105.771l689.482-689.482z" />
				</svg> -->

				<!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path d="M286.1 368C384.6 368 464.4 447.8 464.4 546.3C464.4 562.7 451.1 576 434.7 576L78.1 576C61.7 576 48.4 562.7 48.4 546.3C48.4 447.8 128.2 368 226.7 368L286.1 368zM562.3 172.1C571.7 162.7 586.9 162.7 596.2 172.1C605.5 181.5 605.6 196.7 596.2 206L562.3 239.9L596.2 273.8C605.6 283.2 605.6 298.4 596.2 307.7C586.8 317 571.6 317.1 562.3 307.7L528.4 273.8L494.5 307.7C485.1 317.1 469.9 317.1 460.6 307.7C451.3 298.3 451.2 283.1 460.6 273.8L494.5 239.9L460.6 206C451.2 196.6 451.2 181.4 460.6 172.1C470 162.8 485.2 162.7 494.5 172.1L528.4 206L562.3 172.1zM256.4 312C190.1 312 136.4 258.3 136.4 192C136.4 125.7 190.1 72 256.4 72C322.7 72 376.4 125.7 376.4 192C376.4 258.3 322.7 312 256.4 312z" />
				</svg> -->
			</button>
		</form>

		<!-- <form method="POST" name="logout">
			<button>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z" />
				</svg>

				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path d="M320 80C377.4 80 424 126.6 424 184C424 241.4 377.4 288 320 288C262.6 288 216 241.4 216 184C216 126.6 262.6 80 320 80zM96 152C135.8 152 168 184.2 168 224C168 263.8 135.8 296 96 296C56.2 296 24 263.8 24 224C24 184.2 56.2 152 96 152zM0 480C0 409.3 57.3 352 128 352C140.8 352 153.2 353.9 164.9 357.4C132 394.2 112 442.8 112 496L112 512C112 523.4 114.4 534.2 118.7 544L32 544C14.3 544 0 529.7 0 512L0 480zM521.3 544C525.6 534.2 528 523.4 528 512L528 496C528 442.8 508 394.2 475.1 357.4C486.8 353.9 499.2 352 512 352C582.7 352 640 409.3 640 480L640 512C640 529.7 625.7 544 608 544L521.3 544zM472 224C472 184.2 504.2 152 544 152C583.8 152 616 184.2 616 224C616 263.8 583.8 296 544 296C504.2 296 472 263.8 472 224zM160 496C160 407.6 231.6 336 320 336C408.4 336 480 407.6 480 496L480 512C480 529.7 465.7 544 448 544L192 544C174.3 544 160 529.7 160 512L160 496z" />
				</svg>
			</button>
		</form> -->
	</main>

</body>

</html>