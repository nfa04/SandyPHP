<?php
namespace SandyPHP\Exceptions;
class DatabaseActionPolicyViolation extends SandyPHPException implements Throwable {
    protected string $action;
    public function __construct(string $action) {
        $this->message = 'Database Action Policy Violation: The script tried to execute an sql statement of the following category, which was denied: '.$action;
        $this->action = $action;
    }
}
?>