<?php
namespace SandyPHP\Exceptions;
class StorageAccessPolicyViolation extends SandyPHPException implements Throwable {
    protected string $resource_id;
    public function __construct(string $resource_id) {
        $this->resource_id = $resource_id;
        $this->message = 'Storage Access Policy Violation: The script tried to access a file resource it was not supposed to. Binary ID: '.$binary_id;
    }
}
?>