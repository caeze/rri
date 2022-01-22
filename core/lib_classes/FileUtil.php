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
     * $extension: string or array of string
     */
    function strEndsWith($path, $extension) {
        if (!is_array($extension)) {
            $extension = array($extension);
        }
        foreach ($extension as $value) {
            if(substr_compare($path, $value, -strlen($value)) === 0) {
                return true;
            }
        }
        return false;
    }
}
?>
