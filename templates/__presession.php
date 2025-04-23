    <?php require '__head.php'; ?>
    <link rel="stylesheet" type="text/css" href="./css/presession.css">
    </head>

    <body>
        <div class="container-sm">
            <div class="row">
                <div class="col">
                    <h1 class="text-center text-uppercase fw-bold mt-5 mb-5"><?= $v['title'] ?></h1>
                    <?php if(strlen($v['message']) > 0 ): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $v['message'] ?>
                        </div>
                    <?php endif ?>
                    <?php require $v['child-template']; ?>
                </div>
            </div>
        </div>
    </body>
</html>
