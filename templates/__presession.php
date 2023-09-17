<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?= $v['title'] ?></title>
		<link rel="stylesheet" type="text/css" href="./css/presession.css">
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

		<script src="./js/presession.js"></script>
	</body>
</html>