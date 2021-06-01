<?php
class ArticleDao {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Returns the article from the DB according to the given unique article ID or NULL if the article was not found.
     */
    function getArticle($ID) {
        $sql = "SELECT * FROM \"Articles\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getArticlesImpl($sql);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all articles from the DB.
     */
    function getAllArticles() {
        $sql = "SELECT * FROM \"Articles\" ORDER BY \"ID\";";
        return $this->getArticlesImpl($sql);
    }
    
    /**
     * Returns all articles from the DB in alphabetical order.
     */
    function getAllArticlesAlphabeticalOrder() {
        $sql = "SELECT * FROM \"Articles\" ORDER BY \"title\";";
        return $this->getArticlesImpl($sql);
    }
    
    /**
     * Returns all articles from the DB with the given status or an empty array if none were not found.
     */
    function getAllArticlesWithStatus($status) {
        if (!in_array($status, Constants::ARTICLE_STATUS)) {
            return [];
        }
        $sql = "SELECT * FROM \"Articles\" WHERE \"status\"='" . $status . "';";
        return $this->getArticlesImpl($sql);
    }
    
    /**
     * Executes the query to get articles from the DB.
     */
    function getArticlesImpl($sql) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $data = $result[$i];
            $sql = "SELECT * FROM \"Biddings\" WHERE \"articleID\"='" . $data['ID'] . "';";
            $biddingsResult = $this->dbConn->query($sql);
            $biddings = array();
            for ($j = 0; $j < count($biddingsResult); $j++) {
                $biddings[] = $this->createBiddingFromData($biddingsResult[$j]);
            }
            $data['biddings'] = $biddings;
            $retList[] = $this->createArticleFromData($data);
        }
        return $retList;
    }
    
    /**
     * Constructs a article object from the given data array.
     */
    function createArticleFromData($data) {
        $ID = $data['ID'];
        $status = $data['status'];
        $addedByUserID = $data['addedByUserID'];
        $addedDate = $this->dateUtil->stringToDateTime($data['addedDate']);
        $remark = $data['remark'];
        $title = $data['title'];
        $pictureFileName1 = $data['pictureFileName1'];
        $pictureFileName2 = $data['pictureFileName2'];
        $pictureFileName3 = $data['pictureFileName3'];
        $pictureFileName4 = $data['pictureFileName4'];
        $pictureFileName5 = $data['pictureFileName5'];
        $startingPrice = $data['startingPrice'];
        $expiresOnDate = $this->dateUtil->stringToDateTime($data['expiresOnDate']);
        $description = $data['description'];
        $biddings = $data['biddings'];
        return new Article($ID, $status, $addedByUserID, $addedDate, $remark, $title, $pictureFileName1, $pictureFileName2, $pictureFileName3, $pictureFileName4, $pictureFileName5, $startingPrice, $expiresOnDate, $description, $biddings);
    }
    
    /**
     * Constructs a bidding assigned to an article object from the given data array.
     */
    function createBiddingFromData($data) {
        $ID = $data['ID'];
        $articleID = $data['articleID'];
        $biddingUserID = $data['biddingUserID'];
        $date = $this->dateUtil->stringToDateTime($data['date']);
        $amount = $data['amount'];
        return new Bidding($ID, $articleID, $biddingUserID, $date, $amount);
    }
    
    /**
     * Inserts the new article into the DB.
     * Returns the given article with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addArticle($article) {
        $sql = "INSERT INTO \"Articles\" (\"status\", \"addedByUserID\", \"addedDate\", \"remark\", \"title\", \"pictureFileName1\", \"pictureFileName2\", \"pictureFileName3\", \"pictureFileName4\", \"pictureFileName5\", \"startingPrice\", \"expiresOnDate\", \"description\") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbConn->exec($sql, [$article->getStatus(), $article->getAddedByUserID(), $this->dateUtil->dateTimeToString($article->getAddedDate()), $article->getRemark(), $article->getTitle(), $article->getPictureFileName1(), $article->getPictureFileName2(), $article->getPictureFileName3(), $article->getPictureFileName4(), $article->getPictureFileName5(), $article->getStartingPrice(), $this->dateUtil->dateTimeToString($article->getExpiresOnDate()), $article->getDescription()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            return false;
        }
        
        $article->setID($id);
        
        for ($i = 0; $i < count($article->getBiddings()); $i++) {
            $bidding = $article->getBiddings()[$i];
            $bidding->setArticleID($article->getID());
            $sql = "INSERT INTO \"Biddings\" (\"articleID\", \"biddingUserID\", \"date\", \"amount\") VALUES (?, ?, ?, ?)";
            $result = $this->dbConn->exec($sql, [$bidding->getArticleID(), $bidding->getBiddingUserID(), $this->dateUtil->dateTimeToString($bidding->getDate()), $bidding->getAmount()]);
            $id = $result['lastInsertId'];
            if ($id < 1) {
                return false;
            }
            $bidding->setID($id);
        }
        return $article;
    }
    
    /**
     * Updates the article data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateArticle($article) {
        $sql = "UPDATE \"Articles\" SET \"status\"=?, \"addedByUserID\"=?, \"addedDate\"=?, \"remark\"=?, \"title\"=?, \"pictureFileName1\"=?, \"pictureFileName2\"=?, \"pictureFileName3\"=?, \"pictureFileName4\"=?, \"pictureFileName5\"=?, \"startingPrice\"=?, \"expiresOnDate\"=?, \"description\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$article->getStatus(), $article->getAddedByUserID(), $this->dateUtil->dateTimeToString($article->getAddedDate()), $article->getRemark(), $article->getTitle(), $article->getPictureFileName1(), $article->getPictureFileName2(), $article->getPictureFileName3(), $article->getPictureFileName4(), $article->getPictureFileName5(), $article->getStartingPrice(), $this->dateUtil->dateTimeToString($article->getExpiresOnDate()), $article->getDescription(), $article->getID()]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM \"Biddings\" WHERE \"articleID\"=?;";
        $result = $this->dbConn->exec($sql, [$article->getID()]);
        
        for ($i = 0; $i < count($article->getBiddings()); $i++) {
            $bidding = $article->getBiddings()[$i];
            $bidding->setArticleID($article->getID());
            $sql = "INSERT INTO \"Biddings\" (\"articleID\", \"biddingUserID\", \"date\", \"amount\") VALUES (?, ?, ?, ?)";
            $result = $this->dbConn->exec($sql, [$bidding->getArticleID(), $bidding->getBiddingUserID(), $this->dateUtil->dateTimeToString($bidding->getDate()), $bidding->getAmount()]);
            $id = $result['lastInsertId'];
            if ($id < 1) {
                return false;
            }
            $bidding->setID($id);
        }
        return true;
    }
    
    /**
     * Deletes the article from the DB according to the given unique article ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteArticle($ID) {
        $sql = "DELETE FROM \"Biddings\" WHERE \"articleID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        
        $sql = "DELETE FROM \"Articles\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        return true;
    }
}
?>
