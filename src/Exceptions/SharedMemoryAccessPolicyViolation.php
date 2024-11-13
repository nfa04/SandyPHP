<?php
namespace SandyPHP\Exceptions;
class SharedMemoryAccessPolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Shared Memory Access Policy Violation: The script tried to access shared memory, which it was not supposed to.';
    }
}
?>