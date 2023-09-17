<!DOCTYPE html>
<html lang="de">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }

        body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
    </style>
</head>
<body>
    <span class="preheader" style="display: none"><?= $v['preheader'] ?></span>
    <div class="container" style="background-color: #afafaf">
        <div class="header" style="background-color: #434343; height: 64px">
            <img style="vertical-align: middle; height: 100%" src="https://<?= $v["domain"] ?>/img/logo.png" alt="Logo">
            <h2 style="vertical-align: middle; color: #fff; font-weight: bold; margin: 0 0 0 10px; display: inline"><?= $v['title'] ?></h2>
        </div>
        <div class="content" style="background-color: #fff; padding: 2px">
            <?php if (strlen($v['child-template']) != 0) { require $v['child-template']; } else { echo $v['child-content']; } ?>
        </div>
    </div>
</body>
</html>
