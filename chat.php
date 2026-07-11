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

			<menu>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" />
								<path d="M359.269 175.875c0 55.868-46.007 101.875-101.875 101.875s-101.875-46.007-101.875-101.875S201.525 74 257.394 74s101.875 46.007 101.875 101.875zm172.169 165.16c-7.824-3.912-17.319-2.363-23.513 3.79L356.742 536.757 248.347 423.798c-8.028-5.338-18.827-4.279-25.672 2.527L74.1 603.75v81.5c0 22.372 18.378 40.75 40.75 40.75h570.5c22.372 0 40.75-18.378 40.75-40.75V501.875l-194.663-160.84z" fill-rule="nonzero" />
							</svg>
						</button>

						<input type="file" id="imagen" name="archivo" accept="image/jpeg, image/jxl, image/png, image/webp, image/gif, image/avif, image/bmp, image/tiff, image/x-icon, image/heic, image/heif" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" />
								<path d="M653.5 188.755h-63.375L569 146.505c-12.443-24.568-18.928-42.25-42.25-42.25h-253.5c-23.322 0-31.054 20.153-42.25 42.25l-21.125 42.25H146.5c-46.665 0-84.5 37.835-84.5 84.5v338c0 46.665 37.835 84.5 84.5 84.5h507c46.665 0 84.5-37.835 84.5-84.5v-338c0-46.665-37.835-84.5-84.5-84.5zM400 611.255c-93.33 0-169-75.67-169-169s75.67-169 169-169 169 75.67 169 169-75.67 169-169 169zm0-295.75c-69.987 0-126.75 56.763-126.75 126.75s56.763 126.75 126.75 126.75 126.75-56.763 126.75-126.75-56.763-126.75-126.75-126.75z" />
							</svg>
						</button>

						<input type="file" id="camara" name="archivo" capture="user" accept="image/*" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" fill-rule="nonzero" />
								<path d="M620.771 108.9H329.567c-32.126 0-58.229 25.899-58.229 58.2v306.423c-29.1-9.603-36.753-15.423-57.88-15.423-51.041 0-96.845 32.01-111.278 80.898-22.902 77.988 33.203 149.283 107.001 151.902 66.755 2.328 120.387-55.581 120.387-122.22V196.2c0-16.005 13.066-29.1 29.129-29.1h232.945c16.063 0 29.129 13.095 29.129 29.1v277.323c-29.1-9.603-36.753-15.423-57.909-15.423-51.041 0-96.845 32.01-111.249 80.898-22.931 77.988 33.203 149.283 106.972 151.902C625.369 693.228 679 635.319 679 568.68V167.1c0-32.301-26.074-58.2-58.229-58.2" />
							</svg>
						</button>

						<input type="file" id="audio" name="archivo" accept="audio/*" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" />
								<path d="M274 190c0-69.594 56.406-126 126-126s126 56.406 126 126v168c0 69.594-56.406 126-126 126s-126-56.406-126-126V190zm168 459.018V736h-84v-86.982C215.536 628.648 106 506.134 106 358v-42h84v42c0 115.962 94.038 210 210 210s210-94.038 210-210v-42h84v42c0 148.134-109.536 270.648-252 291.018z" />
							</svg>
						</button>

						<input type="file" id="micro" name="archivo" capture accept="audio/*" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" />
								<path d="M678.702 367.954L170.325 96.782c-30.16-13.445-64.708-12.049-64.708 36.214v533.973c0 44.121 37.064 51.076 64.708 36.214L678.702 432.01c20.927-17.697 20.927-46.358 0-64.055" />
							</svg>
						</button>

						<input type="file" id="video" name="archivo" accept="video/*" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>

				<li>
					<form method="POST">

						<button type="button" aria-label="Enviar imagen">
							<svg viewBox="0 0 800 800">
								<path d="M0 0h800v800H0z" fill="#fff" />
								<path d="M66.667 384.145v31.708c0 104.257 0 156.354 30.267 191.455 5.533 6.405 11.733 12.303 18.467 17.566 36.9 28.791 91.7 28.791 201.266 28.791 109.6 0 164.367 0 201.266-28.791 6.733-5.264 12.933-11.161 18.467-17.566 23.4-27.111 28.7-64.4 29.9-128.038l22.3 10.432c64.867 30.852 97.3 46.294 121.033 32.342 23.7-13.952 23.7-48.419 23.7-117.416v-9.259c0-68.966 0-103.464-23.7-117.416-23.733-13.952-56.167 1.49-121.033 32.342l-22.3 10.432c-1.2-63.639-6.5-100.928-29.9-128.038-5.533-6.405-11.733-12.303-18.467-17.566-36.9-28.791-91.667-28.791-201.266-28.791-109.567 0-164.367 0-201.266 28.791-6.733 5.264-12.933 11.161-18.467 17.566-30.267 35.101-30.267 87.23-30.267 191.455z" fill-rule="nonzero" />
							</svg>
						</button>

						<input type="file" id="grabacion" name="archivo" capture accept="video/*" required>

						<button type="submit"></button>

						<output></output>

					</form>
				</li>
				<!-- <li>
					<form method="POST">

						<geolocation></geolocation>

						<input hidden name="contenido" autocomplete="off" minlength="1" maxlength="255" required>

						<button>
							<svg viewBox="0 0 512 512">
								<path
									d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
							</svg>
						</button>

						<output></output>

					</form>
				</li> -->

				<!-- <li>

				</li> -->

			</menu>

			<form method="POST">

				<textarea placeholder="Mensaje" name="contenido" autocomplete="off" minlength="1" maxlength="255" required></textarea>

				<button>
					<svg viewBox="0 0 512 512">
						<path
							d="M5.091 175.195c-2.418-.846-4.092-3.522-4.091-6.54V7.202c0-2.279.949-4.402 2.53-5.664S7.113.043 8.866.913l499.636 249.199c2.114 1.053 3.498 3.54 3.498 6.283s-1.384 5.229-3.498 6.282L8.866 511.876c-1.753.87-3.758.635-5.337-.625S1 507.866 1 505.587V344.134c-.001-3.018 1.673-5.694 4.091-6.54l213.958-74.667c2.426-.844 4.098-3.508 4.098-6.533s-1.671-5.69-4.098-6.533L5.091 175.195z" />
					</svg>
				</button>

				<output></output>

			</form>

		</section>

	</main>

</body>

</html>