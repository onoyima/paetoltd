<!DOCTYPE html>
<html lang="en">

<head>
	<?php
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$host = $_SERVER['HTTP_HOST'];
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$baseDir = str_replace('\\', '/', dirname($scriptName));
	if ($baseDir !== '/') {
		$baseDir .= '/';
	}
	$baseUrl = $protocol . $host . $baseDir;
	?>
	<base href="<?php echo htmlspecialchars($baseUrl); ?>">

	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">


	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- PAGE TITLE HERE -->
	<title><?php echo htmlspecialchars($pageTitle ?? 'Pa-etos Hostel Accommodation'); ?></title>

	<!-- Favicon icon -->
	<link rel="shortcut icon" type="image/png" href="images/paetoa.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link class="main-css" href="css/style.css" rel="stylesheet">
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="icons/font-awesome/css/all.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script>(function () { try { var t = localStorage.getItem('pt_theme'); if (t !== 'dark' && t !== 'light') { t = (typeof getCookie === 'function' && getCookie('version') === 'dark') ? 'dark' : 'light' } document.documentElement.setAttribute('data-pt-theme', t) } catch (e) { document.documentElement.setAttribute('data-pt-theme', 'light') } })();</script>
</head>