<?php

class SandyPHPException extends Exception implements Throwable {
    protected $timestamp;

    public function __construct() {
        $this->timestamp = time();
    }

    public function getLoggableMessage() {
        return date("[d.m.Y - H:i]", $this->timestamp).' SandyPHPException: '.$this->message;
    }

}

class MYSQLINotFoundException extends SandyPHPException implements Throwable {

    public function __construct() {
        $this->message = 'SandyPHP couldn\'t find the MySQLI extension.';
    }

}

class StorageAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
        $this->message = 'Storage Access Policy Violation: The script tried to access a ressource it was not supposed to.';
    }
}

?>