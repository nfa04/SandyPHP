<?php
namespace SandyPHP\Notices;

class ScriptExecutionStartNotice extends SandyPHPNotice {
    public function __construct(string $sandbox_id, string $exec_id, bool $print_message) {
        $this->message = 'Script execution started. ExecID: '.$exec_id.', SandyPHPSandboxID: '.$sandbox_id;
        parent::__construct($print_message);
    }
}
?>