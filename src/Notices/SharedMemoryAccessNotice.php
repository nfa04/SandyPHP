<?php
namespace SandyPHP\Notices;
class SharedMemoryAccessNotice extends SandyPHPNotice {
    public function __construct(bool $print_message) {
        $this->message = 'Script accessed shared memory';
        parent::__construct($print_message);
    }
}
?>