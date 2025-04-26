<!doctype html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; media-src 'self' data:; font-src 'self'; form-action 'self'; connect-src 'self'; upgrade-insecure-requests">

    <title><?= $t('common.title', ['page' => $t($v['title'])]) ?></title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="icon" href="/favicon.ico">
