<?php
    require_once('core/Main.php');
    if (!$userSystem->isLoggedIn()) {
        $log->info('articles.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    else {
        $redirect->redirectTo('articles.php');
    }
?>
