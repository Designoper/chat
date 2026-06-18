<?php

declare(strict_types=1);

require_once __DIR__ . '/models/Usuario.php';

new Usuario()->sessionRedirect();

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
	<link href="./assets/css/index.css" rel="preload stylesheet" as="style">

	<script src="./assets/js/pages/login.js" type="module"></script>

</head>

<body>

	<main id="main">

		<form method="POST" name="createUsuario">

			<h1>Crear cuenta</h1>

			<menu>
				<li>
					<label for="nombre_usuario">Nombre usuario*</label>
					<!-- <input id="nombre_usuario" name="nombre_usuario" autocomplete="off" pattern="\w{3,20}" required> -->
					<input id="nombre_usuario" name="nombre_usuario" autocomplete="off" required>
				</li>

				<li>
					<label for="password">Contraseña*</label>
					<input id="password" name="password" type="password" minlength="1" autocomplete="off" required>
				</li>

				<li>
					<button>Registrarme</button>
				</li>
			</menu>

			<output></output>

			<p>¿Ya tienes cuenta? <a href="./index.php">Iniciar sesión</a></p>

		</form>

	</main>

</body>

</html>