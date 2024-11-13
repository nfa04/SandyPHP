<?php
namespace SandyPHP\Notices;
class NetworkAccessNotice extends SandyPHPNotice {
    public function __construct(bool $print_message) {
        $this->message = 'Script accessed a network resource';
        parent::__construct($print_message);
    }
}
?>