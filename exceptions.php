<?php

class SandyPHPException extends Exception implements Throwable, Loggable {
    protected $timestamp;

    public function __construct() {
        $this->timestamp = time();
    }

    public function getLoggableMessage() {
        return date("[d.m.Y - H:i]", $this->timestamp).'[Exception]['.get_class($this).']: '.$this->message;
    }

}

class MYSQLINotFoundException extends SandyPHPException implements Throwable {

    public function __construct() {
        $this->message = 'SandyPHP couldn\'t find the MySQLI extension.';
    }

}

class StorageAccessPolicyViolation extends SandyPHPException implements Throwable {
    protected string $resource_id;
    public function __construct(string $resource_id) {
        $this->resource_id = $resource_id;
        $this->message = 'Storage Access Policy Violation: The script tried to access a file resource it was not supposed to. Binary ID: '.$binary_id;
    }
}

class DatabaseAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Database Access Policy Violation: The script tried to access a database resource it was not supposed to.';
    }
}

class NetworkAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Network Access Policy Violation: The script tried to access a network resource it was not supposed to.';
    }
}

class SharedMemoryAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Shared Memory Access Policy Violation: The script tried to access shared memory, which it was not supposed to.';
    }
}

class BinaryExecutionPermissionViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Binary Execution Policy Violation: The script tried to run a binary, which it was not supposed to.';
    }
}

class EscapePolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Escape Policy Violation: The script tried to escape the PHP context, which it was not supposed to.';
    }
}

class InterprocessCommunicationPermissionViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Interprocess Communication Policy Violation: The script tried to communicate to another process, which it was not supposed to.';
    }
}

?>
