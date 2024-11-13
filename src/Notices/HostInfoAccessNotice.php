<?php
namespace SandyPHP\Notices;
class HostInfoAccessNotice extends SandyPHPNotice {
        protected string $granted;
        public function __construct(bool $granted, bool $print_message) {
            $this->message = 'The script tried to access information about the system it is running on. Access granted: '.($granted ? 'Yes' : 'No');
            $this->granted = $granted;
            parent::__construct($print_message);
        }
    }

?>