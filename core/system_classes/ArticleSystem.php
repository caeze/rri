<?php
class ArticleSystem {
    private $articleDao = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $hashUtil = null;
    private $currencyUtil = null;
    private $email = null;
    private $log = null;

    function __construct($articleDao, $dateUtil, $fileUtil, $hashUtil, $currencyUtil, $email) {
        $this->articleDao = $articleDao;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        $this->hashUtil = $hashUtil;
        $this->currencyUtil = $currencyUtil;
        $this->email = $email;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }

    /**
     * Returns all articles from the DB with the given status or an empty array if none were not found.
     */
    function getAllArticles() {
        return $this->articleDao->getAllArticles();
    }

    /**
     * Returns all articles from the DB with the given status or an empty array if none were not found.
     */
    function getAllArticlesWithStatus($status) {
        return $this->articleDao->getAllArticlesWithStatus($status);
    }
    
    /**
     * Returns the article from the DB according to the given unique article ID or NULL if the article was not found.
     */
    function getArticle($articleID) {
        return $this->articleDao->getArticle($articleID);
    }
    
    /**
     * Adds an article to the database and moves the protocol file to the protocols location with a randomly generated name.
     * Returns the just added protocol with the ID set if the operation was successful, NULL otherwise.
     */
    function addArticle($currentUser, $title, $startingPrice, $expiresOnDate, $description, $uploadedImageFilePaths, $uploadedImageFileExtensions) {
        $imageFileNames = [];
        
        for ($i = 0; $i < count($uploadedImageFilePaths); $i++) {
            $filePath = $uploadedImageFilePaths[$i];
            $fileExtension = $uploadedImageFileExtensions[$i];
            $newFileName = $this->hashUtil->generateRandomString() . '.' . $fileExtension;
            if ($filePath != NULL && $filePath != '' && $fileExtension != NULL && $fileExtension != '') {
                $imageFileNames[] = $newFileName;
                move_uploaded_file($filePath, $this->fileUtil->getFullPathToBaseDirectory() . Constants::UPLOADED_IMAGES_DIRECTORY . '/' . $newFileName);
            }
        }
        
        for ($i = count($imageFileNames); $i < 5; $i++) {
            $imageFileNames[] = '';
        }
        
        $status = Constants::ARTICLE_STATUS['active'];
        $addedByUserID = $currentUser->getID();
        $addedDate = $this->dateUtil->getDateTimeNow();
        $remark = '';
        $pictureFileName1 = $imageFileNames[0];
        $pictureFileName2 = $imageFileNames[1];
        $pictureFileName3 = $imageFileNames[2];
        $pictureFileName4 = $imageFileNames[3];
        $pictureFileName5 = $imageFileNames[4];
        $startingPrice = $this->currencyUtil->getAmountFromCurrencyString($startingPrice);
        $expiresOnDate = $this->dateUtil->stringToDateTime($expiresOnDate);
        $expiresOnDate = $expiresOnDate->setTime(23, 59);
        $biddings = [];
        
        $article = new Article(NULL, $status, $addedByUserID, $addedDate, $remark, $title, $pictureFileName1, $pictureFileName2, $pictureFileName3, $pictureFileName4, $pictureFileName5, $startingPrice, $expiresOnDate, $description, $biddings);
        
        $article = $this->articleDao->addArticle($article);
        if ($article == false) {
            $this->log->error(static::class . '.php', 'Error on adding article!');
            return NULL;
        }
        return $article;
    }
    
    /**
     * Updates the article in the database with the given data.
     * Returns TRUE if the operation was successful, FALSE otherwise.
     */
    function updateArticle($articleID, $remark, $examiner) {
        $article = $this->articleDao->getArticle($articleID);
        if ($article == NULL) {
            $this->log->error(static::class . '.php', 'Article to ID ' . $articleID . ' not found!');
            return false;
        }
        $article->setRemark($remark);
        $article->setExaminer($examiner);
        return $this->articleDao->updateArticle($article);
    }
    
    /**
     * Updates the article in the database with the given data.
     * Returns TRUE if the operation was successful, FALSE otherwise.
     */
    function updateArticleStatus($articleID, $newStatus) {
        $article = $this->articleDao->getArticle($articleID);
        if ($article == NULL) {
            $this->log->error(static::class . '.php', 'Article to ID ' . $articleID . ' not found!');
            return false;
        }
        $article->setStatus($newStatus);
        return $this->articleDao->updateArticle($article);
    }
    
    /**
     * Updates the article in the database with the given data.
     * Returns TRUE if the operation was successful, FALSE otherwise.
     */
    function updateArticleFully($articleID, $collaboratorIDs, $status, $uploadedByUserID, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileExtension) {
        $article = $this->articleDao->getArticle($articleID);
        if ($article == NULL) {
            $this->log->error(static::class . '.php', 'Article to ID ' . $articleID . ' not found!');
            return false;
        }
        $article->setStatus($status);
        $article->setUploadedByUserID($uploadedByUserID);
        $article->setCollaboratorIDs($collaboratorIDs);
        $article->setUploadedDate($uploadedDate);
        $article->setRemark($remark);
        $article->setExaminer($examiner);
        $article->setFileName($fileName);
        $article->setFileSize($fileSize);
        $article->setFileType($fileType);
        $article->setFileExtension($fileExtension);
        return $this->articleDao->updateArticle($article);
    }
    
    /**
     * Returns the number of articles that are in the DB or NULL if something went wrong.
     */
    function getNumberOfArticlesTotal($articleID, $uploadedByUserID, $borrowedByUserID) {
        return $this->articleDao->getNumberOfArticlesTotal($articleID, $uploadedByUserID, $borrowedByUserID);
    }
    
    /**
     * Returns articles from the DB according to the number of wanted results and the start page.
     */
    function getArticles($numberOfResultsWanted, $page, $articleID, $uploadedByUserID, $borrowedByUserID) {
        return $this->articleDao->getArticles($numberOfResultsWanted, $page, $articleID, $uploadedByUserID, $borrowedByUserID);
    }
    
    /**
     * Reports via mail to the admins that the article is outdated.
     */
    function reportArticleAsOutdated($articleID, $reportingUsername) {
        if (!is_numeric($articleID)) {    
            $this->log->error(static::class . '.php', 'Article ID is not numeric!');
            return false;
        }
        $article = $this->getArticle($articleID);
        if ($article == NULL) {
            $this->log->error(static::class . '.php', 'Article to ID ' . $articleID . ' not found! Can not report as outdated!');
            return false;
        }
        $message = 'The user ' . $reportingUsername . ' has reported that the article ' . $article->getTitle() . ' with ID ' . $articleID . ' is outdated. Please verify this and handle it (if necessary).';
        $this->email->send(Constants::EMAIL_ADMIN, 'Article reported as outdated in RRI', $message);
        return true;
    }
    
    /**
     * Adds a new bid on an article. Returns the article with the new bidding set if successful or false if something went wrong.
     */
    function bid($articleID, $amount, $biddingUser) {
        $article = $this->getArticle($articleID);
        if ($article != NULL) {
            if ($amount <= $this->getCurrentlyHighestBidding($article)) {
                $this->log->error(static::class . '.php', 'Can not make bid that is smaller than or equal to the highest previous bid!');
                return false;
            }
            $biddings = $article->getBiddings();
            $bidding = new Bidding(NULL, $article->getID(), $biddingUser->getID(), $this->dateUtil->getDateTimeNow(), $amount);
            $biddings[] = $bidding;
            $article->setBiddings($biddings);
            $result = $this->articleDao->updateArticle($article);
            if ($result == false) {
                $this->log->error(static::class . '.php', 'Error on updating article bidding data on the DB!');
                return false;
            }
            return $article;
        }
        $this->log->error(static::class . '.php', 'Article to ID ' . $articleID . ' not found!');
        return false;
    }
    
    /**
     * Returns the currently highest bidding amount in cents for the given article.
     * This highest amount may be the starting amount if no bids have been placed on this article yet.
     */
    function getCurrentlyHighestBidding($article) {
        $currentlyHighestBidding = $article->getStartingPrice();
        foreach ($article->getBiddings() as $bidding) {
            if ($bidding->getAmount() > $currentlyHighestBidding) {
                $currentlyHighestBidding = $bidding->getAmount();
            }
        }
        return $currentlyHighestBidding;
    }
}
?>
