<?php
namespace SandyPHP\Exceptions;
class EscapePolicyViolation extends SandyPHPException implements Throwable {
    public function __construct() {
    $this->message = 'Escape Policy Violation: The script tried to escape the PHP context, which it was not supposed to.';
    }
}
?>