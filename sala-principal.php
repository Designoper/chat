<?php

declare(strict_types=1);

require_once __DIR__ . '/models/usuario/Usuario.php';

new Usuario()->auth();

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

	<script src="./assets/js/Usuario.js" type="module" async></script>

	<script src="./sw-register.js" defer></script>

</head>

<body>

	<main id="main">

		<h1>¿Con quién quieres chatear?</h1>

		<menu>
			<li><a href="./chat-publico.php">Chat público</a></li>
		</menu>

		<form name="logout-usuario">

			<button>
				<svg viewBox="0 0 654 752">
					<path
						d="M418.624 610.432L653.12 375.936 418.512 141.328l-56.56 56.56L500.032 336H176v80h323.92L362.064 553.872l56.56 56.56zM80 80h368V0H0v752h448v-80H80V80z" />
				</svg>
			</button>

		</form>

	</main>

</body>

</html>