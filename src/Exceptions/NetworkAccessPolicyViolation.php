<?php
namespace SandyPHP\Exceptions;
class NetworkAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Network Access Policy Violation: The script tried to access a network resource it was not supposed to.';
    }
}
?>