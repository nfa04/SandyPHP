<?php
namespace SandyPHP\Notices;
class InterprocessCommunicationNotice extends SandyPHPNotice {
    public function __construct(bool $print_message) {
        $this->message = 'Script communicated with another process';
        parent::__construct($print_message);
    }
}

?>