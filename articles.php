<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('articles.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $reportArticleID = filter_input(INPUT_GET, 'report', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['report'])) {
            if (is_numeric($reportArticleID)) {
                $reportingResult = $articleSystem->reportArticleAsOutdated($reportArticleID, $currentUser->getUsername());
                if ($reportingResult == true) {
                    $log->info('articles.php', 'Successfully reported the article as outdated.');
                }
            } else {
                $log->error('articles.php', 'Article ID is not numeric: ' . $reportArticleID);
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('showArticles'), array('protocols.css', 'button.css', 'searchableTable.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);

    echo '<div id="protocolsTable" style="padding-left: 40px; padding-bottom: 40px; padding-right: 40px; margin: 0px;">';

    $headers = array($i18n->get('articleTitle'), $i18n->get('currentBidding'), $i18n->get('uploadedOnDate'), $i18n->get('expiresOnDate'), $i18n->get('viewAndBid'), $i18n->get('report'));
    $widths = array(50, 10, 10, 10, 10, 10);
    $textAlignments = array('left', 'center', 'center', 'center', 'center', 'center');
    
    $allArticles = $articleSystem->getAllArticles();
    $allArticles = array_reverse($allArticles);
    
    function getHighestBidding($article, $currencyUtil) {
        $highestValue = 0;
        foreach ($article->getBiddings() as &$bidding) {
            $highestValue = max($highestValue, $bidding->getAmount());
        }
        if ($highestValue == 0) {
            $highestValue = $article->getStartingPrice();
        }
        return $currencyUtil->formatCentsToCurrency(intval($highestValue));
    }
    
    $data = array();
    $insertBeginningOfArray = false;
    foreach ($allArticles as &$article) {
        $row = array();
        $row[] = $article->getTitle();
        $row[] = getHighestBidding($article, $currencyUtil);
        $row[] = $dateUtil->dateTimeToStringForDisplaying($article->getAddedDate(), $currentUser->getLanguage());
        $row[] = $dateUtil->dateTimeToStringForDisplaying($article->getExpiresOnDate(), $currentUser->getLanguage());
        $row[] = '<a id="styledButton" href="bid.php?id=' . $article->getID() . '"><nobr><img src="static/img/bid.png" style="height: 24px; vertical-align: middle;">&nbsp;&nbsp;' . $i18n->get('viewAndBid') . '</nobr></a>';
        $reportingPossible = true;
        if (isset($_GET['report'])) {
            if (is_numeric($reportArticleID)) {
                if ($article->getID() == $reportArticleID) {
                    $reportingPossible = false;
                }
            }
        }
        if ($reportingPossible) {
            $row[] = '<a id="styledButtonRed" href="?report=' . $article->getID() . '"><nobr><img src="static/img/report.png" style="height: 24px; vertical-align: middle;"></nobr></a>';
        } else {
            $row[] = '<a id="styledButtonGray" href=""><nobr><img src="static/img/report.png" style="height: 24px; vertical-align: middle;"></nobr></a>';
        }
        
        if ($insertBeginningOfArray) {
            array_splice($data, 0, 0, array($row));
        } else {
            $data[] = $row;
        }
    }
    echo $searchableTable->createTable($headers, $data, $widths, $textAlignments);
    
    echo '</div>';

    echo $footer->getFooter();
?>
