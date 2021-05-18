<?php
class ArticleSystem {
    private $articleDao = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $hashUtil = null;
    private $log = null;

    function __construct($articleDao, $dateUtil, $fileUtil, $hashUtil) {
        $this->articleDao = $articleDao;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        $this->hashUtil = $hashUtil;
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
    function getArticle($protocolID) {
        return $this->articleDao->getArticle($protocolID);
    }
    
    /**
     * Adds an article to the database and moves the protocol file to the protocols location with a randomly generated name.
     * Returns the just added protocol with the ID set if the operation was successful, NULL otherwise.
     */
    function addArticle($currentUser, $collaboratorIDs, $remark, $examiner, $fileNameTmp, $fileNameExtension, $fileSize, $fileType) {
        $fileName = $this->hashUtil->generateRandomString() . '.' . $fileNameExtension;
        move_uploaded_file($fileNameTmp, $this->fileUtil->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $fileName);
        
        $status = Constants::EXAM_PROTOCOL_STATUS['unchecked'];
        $uploadedByUserID = $currentUser->getID();
        $uploadedDate = $this->dateUtil->getDateTimeNow();
        $article = new Article(NULL, $status, $uploadedByUserID, $collaboratorIDs, $uploadedDate, $remark, $examiner, $fileName, $fileSize, $fileType, $fileNameExtension);
    
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
            $this->log->error(static::class . '.php', 'Protocol to ID ' . $articleID . ' not found!');
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
            $this->log->error(static::class . '.php', 'Protocol to ID ' . $articleID . ' not found!');
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
            $this->log->error(static::class . '.php', 'Protocol to ID ' . $articleID . ' not found!');
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
}
?>
