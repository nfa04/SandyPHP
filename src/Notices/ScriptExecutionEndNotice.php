<?php
namespace SandyPHP\Notices;
class ScriptExecutionEndNotice extends SandyPHPNotice {
    public function __construct(string $sandbox_id, string $exec_id, bool $print_message) {
        $this->message = 'Script execution ended. ExecID: '.$exec_id.', SandyPHPSandboxID: '.$sandbox_id;
        parent::__construct($print_message);
    }
}
?>