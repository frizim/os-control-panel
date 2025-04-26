    <?php require '__head.php'; ?>
    <link href="./css/dashboard.css" rel="stylesheet">
</head>
<body id="page-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark static-top">
        <button type="button" class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" aria-label="<?= $t('dashboard.shrinkMenu') ?>">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <div class="dropdown ms-auto px-2">
            <a class="bg-dark btn btn-dark dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $v['username'] ?> <i class="fas fa-user-circle fa-fw"></i></a>
            <ul class="dropdown-menu bg-dark" aria-labelledby="userDropdown">
                <li><a class="dropdown-item text-light" href="?page=profile"><?= $t('dashboard.profile.title') ?></a></li>
                <li><hr class="dropdown-divider bg-light"></hr></li>
                <li><a class="dropdown-item text-light" href="index.php?logout=1"><?= $t('dashboard.logout') ?></a></li>
            </ul>
        </div>
    </nav>

    <div id="wrapper">
        <ul class="sidebar navbar-nav bg-dark ps-2 pe-2">
            <li class="nav-item">
                <a class="nav-link<?php !isset($_GET['page']) ? ' active' : '' ?>" href="index.php">
                    <i class="fas fa-home"></i>
                    <span><?= $t('dashboard.home.title') ?></span>
                </a>
            </li>

            <?php foreach(['profile' => 'address-card', 'identities' => 'fingerprint', 'user-online-state' => 'link', 'regions' => 'globe-europe', 'groups' => 'users', 'friends' => 'street-view'] as $item => $icon): ?>
            <li class="nav-item">
                <a class="nav-link<?php $item == $_GET['page'] ? ' active' : '' ?>" href="index.php?page=<?= $item ?>">
                    <i class="fas fa-<?= $icon ?>"></i>
                    <span><?= $t("dashboard.$item.title") ?></span>
                </a>
            </li>
            <?php endforeach ?>

            <?php if ($v['admin']): ?>
            <li class="nav-link text-light text-center fw-bold ps-2">
                <?= $t('dashboard.admin.title') ?>
            </li>

            <?php foreach(['users' => 'user-plus', 'groups' => 'users'] as $item => $icon): ?>
            <li class="nav-item">
                <a class="nav-link<?php $item == $_GET['page'] ? ' active' : '' ?>" href="index.php?page=<?= $item ?>">
                    <i class="fas fa-<?= $icon ?>"></i>
                    <span><?= $t("dashboard.admin.$item") ?></span>
                </a>
            </li>
            <?php endforeach ?>

            <li class="nav-item<?php "regions" == $_GET['page'] && isset($_GET['SHOWALL']) ? ' active' : '' ?>">
                <a class="nav-link" href="index.php?page=regions&SHOWALL=1">
                    <i class="fas fa-globe-europe"></i>
                    <span><?= $t('dashboard.admin.regions') ?></span>
                </a>
            </li>
            <?php endif ?>
        </ul>
        <div id="content-wrapper">
            <div class="container-fluid">
                <ol class="breadcrumb mt-3 mb-0">
                    <li class="breadcrumb-item"><a href="index.php"><?= $t('dashboard.title') ?></a></li>
                    <li class="breadcrumb-item active"><?= $t($v['title']) ?></li>
                </ol>
                <hr class="mt-3 mb-3" />

                <?php if(strlen($v['message']) > 0 ): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $t($v['message'], $v['message-params'] ? $v['message-params'] : []) ?>
                    </div>
                <?php endif ?>

                <?php require $v['child-template']; ?>
            </div>
        </div>
    </div>
    <script src="./js/dashboard.js" defer></script>
    <?= $v['custom-js'] ?>
</body>
</html>
