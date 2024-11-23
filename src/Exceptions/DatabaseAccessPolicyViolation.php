<?php
namespace SandyPHP\Exceptions;
class DatabaseAccessPolicyViolation extends SandyPHPException {
    public function __construct() {
        $this->message = 'Database Access Policy Violation: The script tried to access a database resource it was not supposed to.';
    }
}
?>