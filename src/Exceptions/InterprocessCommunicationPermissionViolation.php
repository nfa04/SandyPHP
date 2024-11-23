<?php
namespace SandyPHP\Exceptions;

class InterprocessCommunicationPermissionViolation extends SandyPHPException {
    public function __construct() {
    $this->message = 'Interprocess Communication Policy Violation: The script tried to communicate to another process, which it was not supposed to.';
    }
}
?>