    <?php require '__head.php'; ?>
    <link href="./css/dashboard.css" rel="stylesheet">
</head>
<body id="page-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark static-top">
        <button type="button" class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" aria-label="Menü verkleinern">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <div class="dropdown ms-auto px-2">
            <a class="bg-dark btn btn-dark dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $v['username'] ?> <i class="fas fa-user-circle fa-fw"></i></a>
            <ul class="dropdown-menu bg-dark" aria-labelledby="userDropdown">
                <li><a class="dropdown-item text-light" href="?page=profile">Profil</a></li>
                <li><hr class="dropdown-divider bg-light"></hr></li>
                <li><a class="dropdown-item text-light" href="index.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div id="wrapper">
        <ul class="sidebar navbar-nav bg-dark ps-2 pe-2">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Übersicht</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=profile">
                    <i class="far fa-address-card"></i>
                    <span>Profil</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=identities">
                    <i class="fas fa-fingerprint"></i>
                    <span>Identitäten</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=user-online-state">
                    <i class="fas fa-link"></i>
                    <span>Online Anzeige</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=regions">
                    <i class="fas fa-globe-europe"></i>
                    <span>Deine Regionen</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=groups">
                    <i class="fas fa-users"></i>
                    <span>Deine Gruppen</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=friends">
                    <i class="fas fa-street-view"></i>
                    <span>Deine Freunde</span>
                </a>
            </li>
            <?php if ($v['admin']): ?>
            <li class="nav-link text-light text-center fw-bold ps-2">
                Administration
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=users">
                    <i class="fas fa-user-plus"></i>
                    <span>Benutzer verwalten</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=groups">
                    <i class="fas fa-users"></i>
                    <span>Gruppen verwalten</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=regions&SHOWALL=1">
                    <i class="fas fa-globe-europe"></i>
                    <span>Regionen verwalten</span>
                </a>
            </li>
            <?php endif ?>
        </ul>
        <div id="content-wrapper">
            <div class="container-fluid">
                <ol class="breadcrumb mt-3 mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Gridverwaltung</a></li>
                    <li class="breadcrumb-item active"><?= $v['title'] ?></li>
                </ol>
                <hr class="mt-3 mb-3" />

                <?php if(strlen($v['message']) > 0 ): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $v['message'] ?>
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
