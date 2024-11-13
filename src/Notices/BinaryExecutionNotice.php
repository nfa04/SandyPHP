<?php
namespace SandyPHP\Notices;
class BinaryExecutionNotice extends SandyPHPNotice {
    protected string $binary_id;
    public function __construct(string $binary_id, bool $print_message) {
        $this->binary_id = $binary_id;
        $this->message = 'Script executed the binary identified with: '.$binary_id;
        parent::__construct($print_message);
    }
}
?>