<div class="row">
    <div class="col-3 col-sm-6 mb-3">
        <div class="card text-white bg-primary o-hidden h-100">
            <div class="card-body">
                <div class="card-body-icon">
                    <i class="fas fa-fw fa-user"></i>
                </div>
                <div class="mr-5"><?= $v['global-user-count'] ?></div>
            </div>
            <span class="card-footer text-light small z-1"><?= $t('dashboard.home.usersOnline') ?></span>
        </div>
    </div>

    <div class="col-3 col-sm-6 mb-3">
        <div class="card text-white bg-primary o-hidden h-100">
            <div class="card-body">
                <div class="card-body-icon">
                    <i class="fas fa-fw fa-globe"></i>
                </div>
                <div class="mr-5"><?= $v['global-region-count'] ?></div>
            </div>
            <span class="card-footer text-light small z-1"><?= $t('dashboard.home.regions') ?></span>
        </div>
    </div>
</div>
