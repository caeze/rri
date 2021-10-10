<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('articleslist.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    if ($currentUser->getRole() != Constants::USER_ROLES['admin']) {
        $log->error('articleslist.php', 'User was not admin');
        $redirect->redirectTo('articles.php');
    }
    
    $postStatus = NULL;
    $page = 0;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $articleID = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_ENCODED);
        $addedByUserID = filter_input(INPUT_POST, 'addedByUserID', FILTER_SANITIZE_ENCODED);
        $addedDate = filter_input(INPUT_POST, 'addedDate', FILTER_SANITIZE_SPECIAL_CHARS);
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_SPECIAL_CHARS);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $startingPrice = filter_input(INPUT_POST, 'startingPrice', FILTER_SANITIZE_ENCODED);
        $expiresOnDate = filter_input(INPUT_POST, 'expiresOnDate', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
        
        $pageValue = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
        if (is_numeric($pageValue)) {
            $page = intval($pageValue);
        } else {
            if ($pageValue != '') {
                $log->debug('articleslist.php', 'Page value is not numeric: ' . $pageValue);
            }
        }
        if ($articleID != '' && $status != '' && $addedByUserID != '' && $addedDate != '' && $title != '' && $startingPrice != '' && $expiresOnDate != '' && $description) {
            $result = $articleSystem->updateArticleFully($articleID, $status, $addedByUserID, $dateUtil->stringToDateTime($addedDate), $remark, $title, NULL, NULL, NULL, NULL, NULL, $startingPrice, $dateUtil->stringToDateTime($expiresOnDate), $description, NULL);
            if ($result) {
                $postStatus = 'UPDATED_ARTICLE_DATA';
                $log->debug('articleslist.php', 'Successfully updated article data');
            } else {
                $postStatus = 'ERROR_ON_UPDATING_ARTICLE_DATA';
                $log->debug('articleslist.php', 'Error on updating article data');
            }
        } else {
            $postStatus = 'ERROR_ON_UPDATING_ARTICLE_DATA';
            $log->debug('articleslist.php', 'Values missing on updating article data');
        }
    }
    
    $statusFilter = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $pageValue = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_ENCODED);
        $deleteID = filter_input(INPUT_GET, 'deleteID', FILTER_SANITIZE_ENCODED);
        $statusFilter = filter_input(INPUT_GET, 'statusFilter', FILTER_SANITIZE_ENCODED);
        if (is_numeric($pageValue)) {
            $page = intval($pageValue);
        } else {
            if ($pageValue != '') {
                $log->debug('articleslist.php', 'Page value is not numeric: ' . $pageValue);
            }
        }
        if (isset($_GET['deleteID'])) {
            if (is_numeric($deleteID)) {
                $article = $articleSystem->getArticle($deleteID);
                if ($article != null) {
                    $result = $articleSystem->updateArticleStatus($deleteID, Constants::ARTICLE_STATUS['toBeDeleted']);
                    if (!$result) {
                        $postStatus = 'ERROR_ON_UPDATING_ARTICLE_DATA';
                        $log->error('articleslist.php', 'Article could not be marked for deletion with article ID: ' . $deleteID);
                    }
                } else {
                    $postStatus = 'ERROR_ON_UPDATING_ARTICLE_DATA';
                    $log->error('articleslist.php', 'Article ID to be deleted not found: ' . $deleteID);
                }
            } else {
                $postStatus = 'ERROR_ON_UPDATING_ARTICLE_DATA';
                $log->error('articleslist.php', 'ID of article to be deleted is not numeric: ' . $deleteID);
            }
        }
    }
    
    echo $header->getHeader($i18n->get('title'), $i18n->get('allArticles'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    if ($postStatus == 'UPDATED_ARTICLE_DATA') {
        echo '<br><center>' . $i18n->get('updatedArticleDataSuccessfully') . '</center><br>';
    } else if ($postStatus == 'ERROR_ON_UPDATING_ARTICLE_DATA') {
        echo '<br><center>' . $i18n->get('errorOnUpdatingArticleData') . '</center><br>';
    }
    
    echo '<center>
            <details open>
                <summary id="styledButton" style="line-height: 10px; margin: 5px;">' . $i18n->get('filter') . '</summary>
                <div style="left: 0%; width: 100%; display: inline-block; text-align: center; margin: 0px;">
                    <a href="?statusFilter=" id="styledButtonGreen" style="margin: 0px;">' . $i18n->get('noFilter') . '</a>
                    <a href="?statusFilter=' . Constants::ARTICLE_STATUS['active'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::ARTICLE_STATUS['active'] . '</a>
                    <a href="?statusFilter=' . Constants::ARTICLE_STATUS['expired'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::ARTICLE_STATUS['expired'] . '</a>
                    <a href="?statusFilter=' . Constants::ARTICLE_STATUS['toBeDeleted'] . '" id="styledButtonGreen" style="margin: 0px;">' . Constants::ARTICLE_STATUS['toBeDeleted'] . '</a>
                </div>
            </details>
        </center>';
    
    $numberOfArticlesTotal = $articleSystem->getNumberOfArticlesTotal($statusFilter);
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfArticlesTotal);
    
    echo '<br><br>';
    
    echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $i18n->get('ID') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('status') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('addedByUserID') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('addedDate') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('remark') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('articleTitle') . '</div>';
    echo '<div style="width: 5%; display: inline-block;">' . $i18n->get('startingPrice') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('expiresOnDate') . '</div>';
    echo '<div style="width: 10%; display: inline-block;">' . $i18n->get('description') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('details') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('markForDeletion') . '</div>';
    echo '<div style="width: 10%; display: inline-block; text-align: center;">' . $i18n->get('save') . '</div>';
    
    $allArticles = $articleSystem->getArticles(Constants::NUMBER_OF_ENTRIES_PER_PAGE, $page, $statusFilter);
    foreach ($allArticles as &$article) {
        $color = '';
        if ($article->getStatus() == Constants::ARTICLE_STATUS['expired']) {
            $color = ' style="background-color: yellow"';
        }
        if ($article->getStatus() == Constants::ARTICLE_STATUS['toBeDeleted']) {
            $color = ' style="background-color: red"';
        }
        echo '<form method="POST" action="articleslist.php?page=' . $page . '"' . $color . '>';
        echo '<div style="width: 5%; display: inline-block; text-align: center;">' . $article->getID() . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" readonly name="status" value="' . $article->getStatus() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="addedByUserID" value="' . $article->getAddedByUserID() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="addedDate" value="' . $dateUtil->dateTimeToStringForDisplaying($article->getAddedDate(), $currentUser->getLanguage()) . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="remark" value="' . $article->getRemark() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="title" value="' . $article->getTitle() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 5%; display: inline-block;">' . '<input type="text" name="startingPrice" value="' . $article->getStartingPrice() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="expiresOnDate" value="' . $dateUtil->dateTimeToStringForDisplaying($article->getExpiresOnDate(), $currentUser->getLanguage()) . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block;">' . '<input type="text" name="description" value="' . $article->getDescription() . '" style="display: table-cell; width: calc(100% - 18px);">' . '</div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">
                    <a href="showarticle.php?id=' . $article->getID() . '" id="styledButton">
                        <img src="static/img/viewBiddings.png" alt="delete" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">
                    <a href="?deleteID=' . $article->getID() . '" id="styledButtonRed">
                        <img src="static/img/delete.png" alt="delete" style="height: 24px; vertical-align: middle;">
                    </a>
                </div>';
        echo '<div style="width: 10%; display: inline-block; text-align: center;">' . 
                    '<button type="submit" id="styledButton" name="id" value="' . $article->getID() . '" style="padding: 3px; width: 40px; height: 40px; vertical-align: middle;">
                        <img src="static/img/save.png" alt="submit" style="height: 24px;">
                    </button>' .
                '</div>';
        echo '</form>';
    }
    
    echo '<br>';
    
    echo $pagedContentUtil->getNavigation($page, Constants::NUMBER_OF_ENTRIES_PER_PAGE, $numberOfArticlesTotal);
    
    echo $footer->getFooter();
?>
