    <?php require '__head.php'; ?>
    <link rel="stylesheet" type="text/css" href="./css/presession.css">
    </head>
    <body>
        <div class="container-sm">
            <div class="row">
                <div class="col">
                    <h1 class="text-center text-uppercase fw-bold mt-5 mb-5"><?= $t($v['title']) ?></h1>
                    <?php if(strlen($v['message']) > 0 ): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $t($v['message'], $v['message-params'] ? $v['message-params'] : []) ?>
                        </div>
                    <?php endif ?>
                    <?php require $v['child-template']; ?>
                </div>
            </div>
        </div>
    </body>
</html>
