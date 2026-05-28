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

	<link href="./assets/fonts/rubik/rubik.woff2" rel="preload" as="font" type="font/woff2" crossorigin>

	<link href="./assets/css/common/reset.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/colors.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/text.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/common/layout.css" rel="preload stylesheet" as="style">
	<link href="./assets/css/sala-grupal.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/sala-grupal.js" type="module"></script>

</head>

<body>

	<main id="main">

		<h1>Sala grupal</h1>

		<section>

			<h2>Tus grupos</h2>

			<output></output>

		</section>

		<section>

			<h2>Invitaciones pendientes</h2>

			<output></output>

		</section>

		<section>

			<h2>Crear nuevo grupo</h2>

			<form method="POST" name="crear-grupo">

				<label for="nombre_grupo">Nombre del grupo</label>
				<input name="nombre_grupo" id="nombre_grupo" autocomplete="off" required>
				<button>Crear grupo</button>

			</form>

		</section>

		<a href="./sala-principal.php">Volver</a>

	</main>

</body>

</html>