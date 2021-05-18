<?php
class UserDao {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * Returns the user from the DB according to the given unique user ID or NULL if the user was not found.
     */
    function getUser($ID) {
        $sql = "SELECT * FROM \"Users\" WHERE \"ID\"='" . $ID . "';";
        $result = $this->getUsersImpl($sql);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns the user from the DB according to the given unique username or NULL if the user was not found.
     */
    function getUserByUsername($username) {
        $sql = "SELECT * FROM \"Users\" WHERE \"username\"='" . $username . "';";
        $result = $this->getUsersImpl($sql);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * Returns all users from the DB.
     */
    function getAllUsers() {
        $sql = "SELECT * FROM \"Users\" ORDER BY \"ID\";";
        return $this->getUsersImpl($sql);
    }
    
    /**
     * Returns the number of users that are in the DB.
     */
    function getNumberOfUsersTotal($role, $username, $userID) {
        $sql = "SELECT COUNT(*) FROM \"Users\";";
        if ($role != '') {
            $sql = "SELECT COUNT(*) FROM \"Users\" WHERE \"role\"='" . $role . "';";
        } else if ($username != '') {
            $sql = "SELECT COUNT(*) FROM \"Users\" WHERE \"username\"='" . $username . "';";
        } else if ($userID != '') {
            $sql = "SELECT COUNT(*) FROM \"Users\" WHERE \"ID\"='" . $userID . "';";
        }
        $result = $this->dbConn->query($sql);
        return $result[0]['count'];
    }
    
    /**
     * Returns users from the DB according to the number of wanted results and the start page.
     */
    function getUsers($numberOfResultsWanted, $page, $role, $username, $userID) {
        $offset = $numberOfResultsWanted * $page;
        $sql = "SELECT * FROM \"Users\" ORDER BY \"ID\" LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        if ($role != '') {
            $sql = "SELECT * FROM \"Users\" WHERE \"role\"='" . $role . "' ORDER BY \"ID\" LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        } else if ($username != '') {
            $sql = "SELECT * FROM \"Users\" WHERE \"username\"='" . $username . "' ORDER BY \"ID\" LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        } else if ($userID != '') {
            $sql = "SELECT * FROM \"Users\" WHERE \"ID\"='" . $userID . "' ORDER BY \"ID\" LIMIT " . $numberOfResultsWanted . " OFFSET " . $offset . ";";
        }
        return $this->getUsersImpl($sql);
    }
    
    /**
     * Executes the query to get users from the DB.
     */
    function getUsersImpl($sql) {
        $result = $this->dbConn->query($sql);
        $retList = array();
        for ($i = 0; $i < count($result); $i++) {
            $data = $result[$i];
            $retList[] = $this->createUserFromData($data);
        }
        return $retList;
    }
    
    /**
     * Constructs a user object from the given data array.
     */
    function createUserFromData($data) {
        $ID = $data['ID'];
        $username = $data['username'];
        $passwordHash = $data['passwordHash'];
        $role = $data['role'];
        $status = $data['status'];
        $lastLoggedIn = $data['lastLoggedIn'];
        $language = $data['language'];
        $comment = $data['comment'];
        return new User($ID, $username, $passwordHash, $role, $status, $lastLoggedIn, $language, $comment);
    }

    /**
     * Inserts the new user into the DB.
     * Returns the given user with also the ID set.
     * If the operation was not successful, FALSE will be returned.
     */
    function addUser($user) {
        $sql = "INSERT INTO \"Users\" (\"username\", \"passwordHash\", \"role\", \"status\", \"lastLoggedIn\", \"language\", \"comment\") VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbConn->exec($sql, [$user->getUsername(), $user->getPasswordHash(), $user->getRole(), $user->getStatus(), $user->getLastLoggedIn(), $user->getLanguage(), $user->getComment()]);
        $id = $result['lastInsertId'];
        if ($id < 1) {
            return false;
        }
        
        $user->setID($id);
        
        return $user;
    }
    
    /**
     * Updates the user data in the DB.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function updateUser($user) {
        $sql = "UPDATE \"Users\" SET \"username\"=?, \"passwordHash\"=?, \"role\"=?, \"status\"=?, \"lastLoggedIn\"=?, \"language\"=?, \"comment\"=? WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$user->getUsername(), $user->getPasswordHash(), $user->getRole(), $user->getStatus(), $user->getLastLoggedIn(), $user->getLanguage(), $user->getComment(), $user->getID()]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Deletes the user from the DB according to the given unique user ID.
     * Returns TRUE if the transaction was successful, FALSE otherwise.
     */
    function deleteUser($ID) {
        $sql = "DELETE FROM \"Users\" WHERE \"ID\"=?;";
        $result = $this->dbConn->exec($sql, [$ID]);
        $rowCount = $result['rowCount'];
        if ($rowCount <= 0) {
            return false;
        }
        
        return true;
    }
}
?>
