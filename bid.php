<?php
    require_once('core/Main.php');
    
    if (!$userSystem->isLoggedIn()) {
        $log->info('bid.php', 'User was not logged in');
        $redirect->redirectTo('login.php');
    }
    
    $status = 'DISPLAY';
    $article = NULL;
    $imageNumber = 1;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $articleIDInput = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_ENCODED);
        $amountInput = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($_POST['amount']) && isset($_POST['id'])) {
            $amount = $currencyUtil->getAmountFromCurrencyString($amountInput);
            if (is_numeric($articleIDInput) && $amount != NULL && is_numeric($amount)) {
                $article = $articleSystem->getArticle($articleIDInput);
                if ($article != NULL) {
                    $result = $articleSystem->bid($article->getID(), $amount, $currentUser);
                    if ($result) {
                        $status = 'BIDDING_SUCCESSFUL';
                        $log->debug('bid.php', 'Successfully added bidding for user: ' . $currentUser->getUsername());
                    } else {
                        $status = 'BIDDING_FAILED';
                        $log->error('bid.php', 'Bidding failed as ArticleSystem returned NULL!');
                    }
                } else {
                    $status = 'ARTICLE_NOT_FOUND';
                }
            } else {
                $status = 'BIDDING_FAILED';
                $log->error('bid.php', 'Bidding failed as given article ID and/or amount malformed!');
            }
        } else {
            $status = 'BIDDING_FAILED';
            $log->error('bid.php', 'Bidding failed as article ID and/or amount were not given!');
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $articleIDInput = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['id'])) {
            if (is_numeric($articleIDInput)) {
                $article = $articleSystem->getArticle($articleIDInput);
                if ($article == NULL) {
                    $status = 'ARTICLE_NOT_FOUND';
                }
            } else {
                $status = 'ARTICLE_ID_NOT_NUMERIC';
            }
        } else {
            $status = 'ARTICLE_ID_NOT_SET';
        }
        
        $imageNumberInput = filter_input(INPUT_GET, 'imageNumber', FILTER_SANITIZE_ENCODED);
        if (isset($_GET['imageNumber'])) {
            if (is_numeric($imageNumberInput)) {
                $imageNumber = $imageNumberInput;
            } else {
                $status = 'IMAGE_NUMBER_NOT_NUMERIC';
            }
        }
    }

    echo $header->getHeader($i18n->get('title'), $i18n->get('bidOnArticle'), array('upload.css', 'button.css'));
    
    echo $mainMenu->getMainMenu($i18n, $currentUser);
    
    function getItemDetails($currentArticle, $imageNumber, $currentUser, $articleSystem, $dateUtil, $currencyUtil, $i18n) {
        $expiresOnDateString = $dateUtil->getDifferenceToNowAsString($currentArticle->getExpiresOnDate(), false, $i18n->get('day'), $i18n->get('days'), $i18n->get('hour'), $i18n->get('hours'), $i18n->get('minute'), $i18n->get('minutes'));
        
        $currentlyHighestBiddingValue = $articleSystem->getCurrentlyHighestBidding($currentArticle);
        $currentlyHighestBidding = $currencyUtil->formatCentsToCurrency($currentlyHighestBiddingValue);
        
        $numberOfImages = 0;
        $images = [];
        if ($currentArticle->getPictureFileName1() != NULL && $currentArticle->getPictureFileName1() != '') {
            $numberOfImages += 1;
            $images[] = $currentArticle->getPictureFileName1();
        }
        if ($currentArticle->getPictureFileName2() != NULL && $currentArticle->getPictureFileName2() != '') {
            $numberOfImages += 1;
            $images[] = $currentArticle->getPictureFileName2();
        }
        if ($currentArticle->getPictureFileName3() != NULL && $currentArticle->getPictureFileName3() != '') {
            $numberOfImages += 1;
            $images[] = $currentArticle->getPictureFileName3();
        }
        if ($currentArticle->getPictureFileName4() != NULL && $currentArticle->getPictureFileName4() != '') {
            $numberOfImages += 1;
            $images[] = $currentArticle->getPictureFileName4();
        }
        if ($currentArticle->getPictureFileName5() != NULL && $currentArticle->getPictureFileName5() != '') {
            $numberOfImages += 1;
            $images[] = $currentArticle->getPictureFileName5();
        }
        
        $previousImageNumber = $imageNumber - 1;
        $nextImageNumber = $imageNumber + 1;
        if ($previousImageNumber < 1) {
            $previousImageNumber = $numberOfImages;
        }
        if ($nextImageNumber > $numberOfImages) {
            $nextImageNumber = 1;
        }
        
        if ($imageNumber <= sizeof($images)) {
            $image = Constants::UPLOADED_IMAGES_DIRECTORY . '/' . $images[$imageNumber - 1];
        }
        
        $biddingHistoryMap = [];
        foreach ($currentArticle->getBiddings() as $bidding) {
            $biddingWasTimeAgoString = $dateUtil->getDifferenceToNowAsString($bidding->getDate(), true, $i18n->get('day'), $i18n->get('days'), $i18n->get('hour'), $i18n->get('hours'), $i18n->get('minute'), $i18n->get('minutes'));
            if ($currentUser->getRole() == Constants::USER_ROLES['admin']) {
                $biddingHistoryMap[$bidding->getID()] = $currencyUtil->formatCentsToCurrency($bidding->getAmount()) . ' (' . $i18n->get('ago') . ' ' . $biddingWasTimeAgoString . '), ' . $i18n->get('byUserID') . ': ' . $bidding->getBiddingUserID();
            } else if ($currentUser->getID() == $bidding->getBiddingUserID()) {
                $biddingHistoryMap[$bidding->getID()] = $currencyUtil->formatCentsToCurrency($bidding->getAmount()) . ' (' . $i18n->get('ago') . ' ' . $biddingWasTimeAgoString . '), ' . $i18n->get('byYou') . ' (' . $currentUser->getUsername() . ')';
            } else {
                $biddingHistoryMap[$bidding->getID()] = $currencyUtil->formatCentsToCurrency($bidding->getAmount()) . ' (' . $i18n->get('ago') . ' ' . $biddingWasTimeAgoString . ')';
            }
        }
        ksort($biddingHistoryMap);
        $biddingHistoryMap = array_reverse($biddingHistoryMap, true);
        
        $biddingHistory = '';
        foreach ($biddingHistoryMap as $key => $val) {
            $biddingHistory = $biddingHistory . '<br>' . $val;
        }
        
        if ($biddingHistory == '') {
            $biddingHistory = '<i>' . $i18n->get('noContent') . '</i>';
        }
    
        return '<form action="bid.php" method="POST">
                    <input type="hidden" id="id" name="id" value="' . $currentArticle->getID() . '" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="' . Constants::MAX_UPLOAD_FILE_SIZE_BYTES . '" />
                    <h3 style="padding: 20px 40px 0px 40px;">' . $currentArticle->getTitle() . '</h3>
                    <table style="width: 100%; padding: 20px;">
                        <tr>
                            <td style="width: 50%; padding: 20px; vertical-align: center; text-align: center;">
                                <img src="' . $image . '" style="max-width: 100%; max-height: 300px; width: auto;">
                                <table style="width: 100%; padding: 0px;">
                                    <tr>
                                        <td style="text-align: left;">
                                            <a id="styledButton" href="?id=' . $currentArticle->getID() . '&imageNumber=' . $previousImageNumber . '">&laquo;&nbsp;' . $i18n->get('previousImage') . '</a>
                                        </td>
                                        <td style="text-align: right;">
                                            <a id="styledButton" href="?id=' . $currentArticle->getID() . '&imageNumber=' . $nextImageNumber . '">' . $i18n->get('nextImage') . '&nbsp;&raquo;</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 50%; padding: 20px; vertical-align: center;">
                                ' . $i18n->get('biddingEndsIn') . ':&nbsp;' . $expiresOnDateString . '<br><br>
                                ' . $i18n->get('currentBid') . ':&nbsp;' . $currentlyHighestBidding . '<br><br>
                                ' . $i18n->get('newBid') . ':&nbsp;
                                <input type="text" id="amount" name="amount" size="4" style="-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;" required value="' . $currencyUtil->formatCentsToCurrency($currentlyHighestBiddingValue + 100) . '">&nbsp;
                                <input type="submit" value="' . $i18n->get('submit') . '"><br><br>
                                ' . $i18n->get('biddingHistory') . ':<br>
                                ' . $biddingHistory . '
                            </td>
                        </tr>
                    </table>
                    <p style="padding: 0px 40px 40px 40px;">
                        ' . $currentArticle->getDescription() . '
                    </p>
                </form>';
    }

    $content = '';
    if ($status == 'DISPLAY') {
        $content = getItemDetails($article, $imageNumber, $currentUser, $articleSystem, $dateUtil, $currencyUtil, $i18n);
    } else if ($status == 'BIDDING_SUCCESSFUL') {
        $content = '<br><br><center>' . $i18n->get('biddingSuccessful') . '<br><br><a id="styledButton" href="articles.php">&laquo;&nbsp;' . $i18n->get('backToArticlesList') . '</a>&nbsp;&nbsp;<a id="styledButton" href="?id=' . $article->getID() . '">' . $i18n->get('viewArticleAgain') . '</a></center><br><br>';
    } else if ($status == 'BIDDING_FAILED') {
        $content = $i18n->get('bidingFailed');
    } else if ($status == 'ARTICLE_NOT_FOUND') {
        $content = $i18n->get('articleNotFoundError');
    } else if ($status == 'ARTICLE_ID_NOT_SET') {
        $content = $i18n->get('articleIdNotSetError');
    } else if ($status == 'ARTICLE_ID_NOT_NUMERIC') {
        $content = $i18n->get('articleIdNotNumericError');
    } else if ($status == 'IMAGE_NUMBER_NOT_NUMERIC') {
        $content = $i18n->get('imageNumberNotNumericError');
    }

    echo '<div style="margin-left: 20%; width: 60%; padding-top: 30px;">
                <div id="uploadField">'
                    . $content . 
                '</div>
            </div>';

    echo $footer->getFooter();
?>
