<?php
namespace SandyPHP\Logger;
class LoggerManager {
    private array $loggers;

    public function __construct() {
        $this->loggers = array();
    }

    public function createLoggerIfNotExists(string $logfileName) {
        foreach($this->loggers AS $id=>$logger) {
            if($logger->getLogFileName() == $logfileName) return $logger;
        }
        $id = uniqid();
        $this->loggers[$id] = new Logger($logfileName, $id);
        return $this->loggers[$id];
    }

    public function getLoggerByID(string $id) {
        return $this->loggers[$id];
    }
}
?>