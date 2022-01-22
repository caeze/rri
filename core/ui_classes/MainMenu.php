<?php
class MainMenu {
    function getMainMenu($i18n, $user) {
        $menu = '<div style="padding: 10px; text-align: center;">';
        $menu .= '<a id="styledButton" href="articles.php">' . $i18n->get('showArticles') . '</a>';
        $menu .= '<a id="styledButton" href="createarticle.php">' . $i18n->get('createArticle') . '</a>';
        if ($user->getRole() == Constants::USER_ROLES['admin']) {
            $menu .= '<br>';
            $menu .= '<a id="styledButtonGray">' . $i18n->get('adminOptions') . ': ' . '</a>';
            $menu .= '<a id="styledButtonRed" href="userslist.php">' . $i18n->get('usersList') . '</a>';
            $menu .= '<a id="styledButtonRed" href="articleslist.php">' . $i18n->get('articlesList') . '</a>';
            $menu .= '<a id="styledButtonRed" href="logevents.php">' . $i18n->get('logEvents') . '</a>';
            $menu .= '<a id="styledButtonRed" href="unittests.php">' . $i18n->get('unitTests') . '</a>';
            $menu .= '<a id="styledButtonRed" href="recurringtasks.php">' . $i18n->get('recurringTasks') . '</a>';
        }
        $menu .= '</div>';
        return $menu;
    }
}
?>
