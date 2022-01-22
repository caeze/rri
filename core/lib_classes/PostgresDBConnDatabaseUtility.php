<?php
class PostgresDBConnDatabaseUtility {
    private $dbConn = null;
    private $dateUtil = null;

    function __construct($dbConn, $dateUtil) {
        $this->dbConn = $dbConn;
        $this->dateUtil = $dateUtil;
    }

    /**
     * Get all the table names from the database.
     */
    function getAllDatabaseTableNames() {
        $sql = "SELECT table_name
                FROM information_schema.tables
                WHERE table_schema='public'
                AND table_type='BASE TABLE';";
        return $this->dbConn->query($sql);
    }

    /**
     * Drop all tables from the database and recreate them with only the initial values.
     */
    function recreateDatabase() {
        $this->dropAllTables();
        $this->setUpEmptyUsersTable();
        $this->setUpEmptyArticlesTable();
        $this->setUpEmptyBiddingsTable();
        $this->setUpEmptyLogEventsTable();
        $this->setUpEmptyRecurringTasksTable();
    }

    /**
     * Drop all tables from the database.
     */
    function dropAllTables() {
        $tableNames = $this->getAllDatabaseTableNames();
        foreach ($tableNames as $row) {
            foreach ($row as $tableName) {
                $this->dbConn->exec('DROP TABLE IF EXISTS "' . $tableName . '";', array());
            }
        }
    }

    /**
     * Set up the empty users table. Insert the admin user.
     */
    function setUpEmptyUsersTable() {
        $sql = 'CREATE TABLE "Users" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"username"          TEXT NOT NULL UNIQUE,
"passwordHash"      TEXT NOT NULL,
"role"              TEXT NOT NULL,
"status"            TEXT NOT NULL,
"lastLoggedIn"      TEXT NOT NULL,
"language"          TEXT NOT NULL,
"comment"           TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
        
        $adminUsername = Constants::USERNAME_ADMIN;
        $adminPasswordHash = password_hash(Constants::PASSWORD_ADMIN, PASSWORD_DEFAULT, array('cost' => Constants::PASSWORD_COST));
        $now = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeNow());
        $sql = 'INSERT INTO "Users" ("username", "passwordHash", "role", "status", "lastLoggedIn", "language", "comment") VALUES (?, ?, ?, ?, ?, ?, ?)';
        $this->dbConn->exec($sql, [$adminUsername, $adminPasswordHash, Constants::USER_ROLES['admin'], '', $now, 'de', 'admin user created by system']);
    }

    /**
     * Set up the empty articles table.
     */
    function setUpEmptyArticlesTable() {
        $sql = 'CREATE TABLE "Articles" (
"ID"               SERIAL PRIMARY KEY UNIQUE,
"status"           TEXT NOT NULL,
"addedByUserID"    INT NOT NULL,
"addedDate"        TEXT NOT NULL,
"remark"           TEXT NOT NULL,
"title"            TEXT NOT NULL,
"pictureFileName1" TEXT NOT NULL,
"pictureFileName2" TEXT NOT NULL,
"pictureFileName3" TEXT NOT NULL,
"pictureFileName4" TEXT NOT NULL,
"pictureFileName5" TEXT NOT NULL,
"startingPrice"    INT NOT NULL,
"expiresOnDate"    TEXT NOT NULL,
"description"      TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty biddings table.
     */
    function setUpEmptyBiddingsTable() {
        $sql = 'CREATE TABLE "Biddings" (
"ID"            SERIAL PRIMARY KEY UNIQUE,
"articleID"     INT NOT NULL,
"biddingUserID" INT NOT NULL,
"date"          TEXT NOT NULL,
"amount"        INT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty log events table.
     */
    function setUpEmptyLogEventsTable() {
        $sql = 'CREATE TABLE "LogEvents" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"date"              TEXT NOT NULL,
"username"          TEXT NOT NULL,
"level"             TEXT NOT NULL,
"remark"            TEXT NOT NULL,
"origin"            TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
    }

    /**
     * Set up the empty recurring tasks table. Insert the recurring tasks and pretend they ran last a very long time ago.
     */
    function setUpEmptyRecurringTasksTable() {
        $sql = 'CREATE TABLE "RecurringTasks" (
"ID"                SERIAL PRIMARY KEY UNIQUE,
"name"              TEXT NOT NULL,
"lastRunDate"       TEXT NOT NULL,
"periodTimeframe"   TEXT NOT NULL,
"periodUnit"        TEXT NOT NULL
);';
        $this->dbConn->exec($sql, array());
        
        $recurringTasks = array_values(Constants::RECURRING_TASKS);
        $datetimeFarInPast = $this->dateUtil->dateTimeToString($this->dateUtil->getDateTimeFarInThePast());
        $recurringTasksTimeframes = array_values(Constants::RECURRING_TASKS_TIMEFRAMES);
        $recurringTasksUnits = array_values(Constants::RECURRING_TASKS_UNITS);
        for ($i = 0; $i < count($recurringTasks); $i++) {
            $sql = 'INSERT INTO "RecurringTasks" ("name", "lastRunDate", "periodTimeframe", "periodUnit") VALUES (?, ?, ?, ?)';
            $this->dbConn->exec($sql, [$recurringTasks[$i], $datetimeFarInPast, $recurringTasksTimeframes[$i], $recurringTasksUnits[$i]]);
        }
    }
}
?>
