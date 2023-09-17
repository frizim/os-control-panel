<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?= $v['grid-name'] ?></title>
    <style>
        @keyframes fade {
            0% {
                opacity: 0;
            }

            11.11% {
                opacity: 1;
            }

            33.33% {
                opacity: 1;
            }

            44.44% {
                opacity: 0;
            }

            100% {
                opacity: 0;
            }
        }

        .auto-slideshow {
            overflow: hidden;
            display: grid;
            height: 600px;
            margin: 0 auto;
        }

        .auto-slideshow img {
            width: 100%;
            height: 100%;
            grid-row: 1;
            grid-column: 1;
            opacity: 0;
            animation-name: fade;
            animation-iteration-count: infinite;
            animation-duration: 3s;
        }

        .auto-slideshow {
            height: auto;
            width: 100%;
        }

        .info-box {
            position: absolute;
        }

        .info-box.top-right {
            top: 50px;
            right: 50px;
        }

        .info-box.bottom-left {
            left: 50px;
            bottom: 50px;
        }

        <?php for($i = 0; $i < count($v['images']); $i++): ?>
            .auto-slideshow img:nth-child(<?= strval($i + 1) ?>) {
                animation-delay: <?= strval($i * 3) ?>s;
            }
        <?php endfor ?>
    </style>
</head>
<body>
    <?php foreach($v['images'] as $image): ?>
        <img loading="lazy" src="<?= $image ?>" alt="" />
    <?php endforeach ?>
    <div class="info-box top-right">
        <div><?= $v['grid-name'] ?></div>
        Willkommen<br />
        Melde dich an, um <?= $v['grid-name'] ?> zu betreten.<br />
        <br />
        <?= $v['news'] ?>
    </div>
    <div class="info-box bottom-left">
        <div>
            Status: <span style='color: rgb(0, 255, 0);'>Online</span>
        </div>
        Registrierte User: <?= $v['registered'] ?><br />
        Regionen: <?= $v['regions'] ?><br />
        Aktuell online: <?= $v['online'] ?>
    </div>
</body>
</html>
