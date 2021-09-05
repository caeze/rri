<?php
class FileUtil {
    private $log = null;

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
        
    /**
     * Gets the current RRI root directory file path.
     */
    function getFullPathToBaseDirectory() {
        list($scriptPath) = get_included_files();
        return dirname($scriptPath) . '/';
    }
    
    /**
     * Checks if the given path has the given file extension.
     */
    function strEndsWith($path, $extension) {
        return substr_compare($path, $extension, -strlen($extension)) === 0;
    }
}
?>
