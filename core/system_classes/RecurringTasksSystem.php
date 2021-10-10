<?php
class RecurringTasksSystem {
    private $recurringTasksDao = null;
    private $articleSystem = null;
    private $userSystem = null;
    private $dateUtil = null;
    private $fileUtil = null;
    private $email = null;
    private $i18n = null;
    private $log = null;
    private $lastResults = [];

    function __construct($recurringTasksDao, $articleSystem, $userSystem, $dateUtil, $fileUtil, $email, $i18n) {
        $this->recurringTasksDao = $recurringTasksDao;
        $this->articleSystem = $articleSystem;
        $this->userSystem = $userSystem;
        $this->dateUtil = $dateUtil;
        $this->fileUtil = $fileUtil;
        $this->email = $email;
        $this->i18n = $i18n;
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Fetches all recurring tasks from the DB.
     * If the last run is too long in the past, they are run.
     * Saves the results to an internal list of the tasks and when they were run last.
     */
    function runRecurringTasks() {
        $recurringTasksList = $this->recurringTasksDao->getAllRecurringTasks();
        for ($i = 0; $i < count($recurringTasksList); $i++) {
            $recurringTask = $recurringTasksList[$i];
            $periodTimeframe = $recurringTask->getPeriodTimeframe();
            $periodUnit = $recurringTask->getPeriodUnit();
            $nextRunDate = $this->dateUtil->addToDateTime($recurringTask->getLastRunDate(), $periodTimeframe, $periodUnit);
            $now = $this->dateUtil->getDateTimeNow();
            $status = 'NOT_TO_BE_RUN';
            if ($this->dateUtil->isSmallerThan($nextRunDate, $now)) {
                $status = $this->runTask($recurringTask->getName());
                if ($status == 'SUCCESS' || $status == 'NO_CHANGE') {
                    $recurringTask->setLastRunDate($now);
                    $result = $this->recurringTasksDao->updateRecurringTask($recurringTask);
                    if ($result == false) {
                        $status = 'FAILED_TO_UPDATE_LAST_RUN_TIME';
                    }
                }
            }
            $nextRunDate = $this->dateUtil->addToDateTime($recurringTask->getLastRunDate(), $periodTimeframe, $periodUnit);
            $this->lastResults[] = [$recurringTask->getID(), $recurringTask->getName(), $recurringTask->getLastRunDate(), $nextRunDate, $status];
        }
    }
    
    /**
     * Returns the results of the last run.
     */
    function getLastResults() {
        return $this->lastResults;
    }
    
    /**
     * Sets up a task to be run by setting its last run time far back in time.
     * Returns TRUE if the process was successful, FALSE otherwise.
     */
    function setUpTaskForRun($recurringTaskID) {
        $recurringTask = $this->recurringTasksDao->getRecurringTask($recurringTaskID);
        if ($recurringTask != NULL) {
            $recurringTask->setLastRunDate($this->dateUtil->getDateTimeFarInThePast());
            return $this->recurringTasksDao->updateRecurringTask($recurringTask);
        }
        return false;
    }
    
    /**
     * Run a recurring task.
     */
    function runTask($taskName) {
        $retVal = 'SUCCESS';
        $result = 'SUCCESS';
        
        // run recurring tasks
        if ($taskName == Constants::RECURRING_TASKS['cleanupLogs']) {
            $result = $this->recurringTasksDao->cleanupLogs();
        }
        if ($taskName == Constants::RECURRING_TASKS['removeToBeDeletedUsers']) {
            $result = $this->recurringTasksDao->removeToBeDeletedUsers();
        }
        if ($taskName == Constants::RECURRING_TASKS['removeToBeDeletedArticles']) {
            $toBeDeleted = $this->articleSystem->getAllArticlesWithStatus(Constants::ARTICLE_STATUS['toBeDeleted']);
            foreach ($toBeDeleted as $article) {
                $deletionResult = $this->articleSystem->deleteArticle($article);
                if ($deletionResult == false) {
                    $result = 'ERROR';
                    $this->log->error(static::class . '.php', 'Recurring task did not run successfully! Could not delete article with ID ' . $article->getID());
                }
            }
        }
        if ($taskName == Constants::RECURRING_TASKS['checkForExpiredBiddings']) {
            $allExpiredArticles = $this->articleSystem->getAllArticlesThatAreExpired();
            $user = $this->userSystem->getLoggedInUser();
            if (sizeof($allExpiredArticles) != 0 && $user != NULL) {
                $expiredArticlesText = '';
                foreach ($allExpiredArticles as $expiredArticle) {
                    if ($expiredArticlesText != '') {
                        $expiredArticlesText .= '<br>';
                    }
                    $expiredArticlesText .= $this->i18n->get('ID') . ': ' . $expiredArticle->getID() . ', ' . $this->i18n->get('articleTitle') . ': ' . $expiredArticle->getTitle();
                }
                $mailResult = $this->email->send($user->getUsername() . Constants::EMAIL_USER_DOMAIN, $this->i18n->get('expiredArticlesFound'), $this->i18n->getWithValues('expiredArticlesFoundPleaseCheckBackWithHighestBidders', [$expiredArticlesText]));
                if ($mailResult) {
                    $result = 'SUCCESS';
                } else {
                    $result = 'ERROR';
                }
            } else {
                $result = 'NO_CHANGE';
            }
        }
        
        // handle results of recurring task run
        if ($result == 'SUCCESS') {
            $this->log->debug(static::class . '.php', 'Recurring task was successful: ' . $taskName);
        }
        if ($result == 'NO_CHANGE') {
            $this->log->debug(static::class . '.php', 'Nothing was to be done by recurring task: ' . $taskName);
            $retVal = 'NO_CHANGE';
        }
        if ($result == 'ERROR') {
            $this->log->error(static::class . '.php', 'Error while running recurring task: ' . $taskName);
            $retVal = 'ERROR';
        }
        return $retVal;
    }
}
?>
