<?php
namespace SandyPHP\Exceptions;
class BinaryExecutionPermissionViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Binary Execution Policy Violation: The script tried to run a binary, which it was not supposed to.';
    }
}
?>