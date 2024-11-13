<?php
namespace SandyPHP\Notices;
class SandboxDestructionNotice extends SandyPHPNotice {
    public function __construct(string $sandbox_id, bool $print_message) {
        $this->message = 'SandyPHP sandbox destructed. SandyPHPSandboxID: '.$sandbox_id;
        parent::__construct($print_message);
    }
}
?>