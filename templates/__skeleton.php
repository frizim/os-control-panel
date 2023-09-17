<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Benutzer online</title>
        <link rel="stylesheet" href="/css/online-display.css">
        <script src="/js/online-display.js" defer></script>
    </head>
    <body>
        <?php if (strlen($v['child-template']) != 0) { require $v['child-template']; } else { echo $v['child-content']; } ?>
    </body>
</html>
