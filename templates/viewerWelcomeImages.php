<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?= $v['grid-name'] ?></title>
    <style>
        @keyframes fade {
            0% {
                opacity: 0;
                transform: translate(0, 0) scale(100%);
            }

            2% {
                opacity: 1;
                transform: translate(10%, 10%) scale(125%);
            }

            20% {
                opacity: 1;
                transform: translate(-10%, -10%) scale(125%);
            }

            98% {
                opacity: 1;
                transform: translate(10%, 10%) scale(125%);
            }

            100% {
                opacity: 0;
                transform: translate(0, 0) scale(100%);
            }
        }

        body {
            overflow: hidden;
            display: grid;
            height: 600px;
            margin: 0 auto;
            background-color: #000000;
        }

        body img {
            width: 100%;
            height: 100%;
            grid-row: 1;
            grid-column: 1;
            opacity: 0;
            animation-name: fade;
            animation-iteration-count: infinite;
            animation-duration: <?= strval(count($v['images']) * 3 * 10) ?>s;
        }

        body {
            height: auto;
            width: 100%;
        }

        h1 {
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            padding-bottom: 4px;
            border-bottom: 1px dashed #fafafa;
        }

        .info-box {
            font-family: sans-serif;
            position: absolute;
            color: #fafafa;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px;
            min-width: 200px;
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
            body img:nth-child(<?= strval($i + 1) ?>) {
                animation-delay: <?= strval($i * 10) ?>s;
            }
        <?php endfor ?>
    </style>
    <script src="./js/"></script>
</head>
<body>
    <?php foreach($v['images'] as $image): ?>
        <img loading="lazy" src="<?= $image ?>" alt="" />
    <?php endforeach ?>
    <div class="info-box top-right">
        <h1><?= $v['grid-name'] ?></h1>
        <?= $t('splash.welcome', ['grid' => $v['grid-name'], 'news' => $v['news']]) ?>
    </div>
    <div class="info-box bottom-left">
        <h1><?= $t('splash.status', ['status' => '<span style="color: rgb(0, 255, 0);">'.$t('splash.status.online').'</span>']) ?></h1>
        <?= $t('splash.registered', ['registered' => $v['registered']]) ?><br />
        <?= $t('splash.regions', ['regions' => $v['regions']]) ?><br />
        <?= $t('splash.online', ['online' => $v['online']]) ?><br />
    </div>
</body>
</html>
