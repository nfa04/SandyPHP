<?php
namespace SandyPHP\Notices;
class SandboxCreationNotice extends SandyPHPNotice {
    public function __construct(string $sandbox_id, bool $print_message) {
        $this->message = 'SandyPHP sandbox created. SandyPHPSandboxID: '.$sandbox_id;
        parent::__construct($print_message);
    }
}
?>