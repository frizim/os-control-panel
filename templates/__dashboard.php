<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>MCP - <?= $v['username'] ?> - <?= $v['title'] ?></title>

    <link href="./css/dashboard.css" rel="stylesheet"> <?= $v['custom-css'] ?>
</head>

<body id="page-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark static-top">
        <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0"></div>

        <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $v['username'] ?> <i class="fas fa-user-circle fa-fw"></i></a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="?page=profile">Profil</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <div id="wrapper">
        <ul class="sidebar navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=profile">
                    <i class="far fa-address-card"></i>
                    <span>Profil</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=identities">
                    <i class="fas fa-fingerprint"></i>
                    <span>Identität</span></a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=user-online-state">
                    <i class="fas fa-link"></i>
                    <span>Online Anzeige</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=regions">
                    <i class="fas fa-globe-europe"></i>
                    <span>Deine Regionen</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=groups">
                    <i class="fas fa-users"></i>
                    <span>Deine Gruppen</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=friends">
                    <i class="fas fa-street-view"></i>
                    <span>Deine Freunde</span></a>
            </li>
            <?php if ($v['admin']): ?>
            <div class="nav-link" style="padding: 1.75rem 1rem 0.75rem; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; color: rgba(255, 255, 255, 0.25)">
                Administration
            </div>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=users">
                    <i class="fas fa-user-plus"></i>
                    <span>Benutzer verwalten</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=groups">
                    <i class="fas fa-users"></i>
                    <span>Gruppen verwalten</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?page=regions&SHOWALL=1">
                    <i class="fas fa-globe-europe"></i>
                    <span>Regionen verwalten</span></a>
            </li>
            <?php endif ?>
        </ul>
        <div id="content-wrapper">
            <div class="container-fluid">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php">Gridverwaltung</a>
                    </li>
                    <li class="breadcrumb-item active"><?= $v['title'] ?></li>
                </ol>

                <hr><br>
                
                <?php if (strlen($v['child-template']) != 0) { require $v['child-template']; } else { echo $v['child-content']; } ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bist du sicher?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Wähle 'Logout' wenn du dich wirklich abmelden möchtest.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Abbrechen</button>
                    <a class="btn btn-primary" href="index.php?logout=1">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/dashboard.js"></script>
    <?= $v['custom-js'] ?>
</body>
</html>
