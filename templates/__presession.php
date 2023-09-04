<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?= $v['title'] ?></title>
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/animate/animate.css">
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/css-hamburgers/hamburgers.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/animsition/css/animsition.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/select2/select2.min.css">
		<link rel="stylesheet" type="text/css" href="./style/login/vendor/daterangepicker/daterangepicker.css">
		<link rel="stylesheet" type="text/css" href="./style/login/css/util.css">
		<link rel="stylesheet" type="text/css" href="./style/login/css/main.css">
		<link href="./style/4Creative.ico" rel="icon">
    	<link href="./style/4Creative.ico" rel="apple-touch-icon">
	</head>

	<body>
		<div class="limiter">
			<div class="container-login100">
				<div class="wrap-login100 p-t-50 p-b-90">
					<?php if (strlen($v['child-template']) != 0) { require $v['child-template']; } else { echo $v['child-content']; } ?>
				</div>
			</div>
		</div>

		<div id="dropDownSelect1"></div>

		<script src="./style/login/vendor/jquery/jquery-3.2.1.min.js"></script>
		<script src="./style/login/vendor/animsition/js/animsition.min.js"></script>
		<script src="./style/login/vendor/bootstrap/js/popper.js"></script>
		<script src="./style/login/vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="./style/login/vendor/select2/select2.min.js"></script>
		<script src="./style/login/vendor/daterangepicker/moment.min.js"></script>
		<script src="./style/login/vendor/daterangepicker/daterangepicker.js"></script>
		<script src="./style/login/vendor/countdowntime/countdowntime.js"></script>
		<script src="./style/login/js/main.js"></script>
	</body>
</html>