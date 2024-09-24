<?php 

    class SandyPHPNotice implements Loggable {
        protected $stacktrace;
        protected $time;
        protected $message;

        public function __construct() {
            $this->time = time();
            $this->stacktrace = debug_backtrace();
        }

        public function getMessage() {
            return $this->message;
        }

        public function getLoggableMessage() {
            return '['.date("d.m.Y - H:i", $this->time).'][Notice]['.get_class($this).']: '.$this->message;
        }

    }

    class StorageAccessNotice extends SandyPHPNotice {
        protected $resource_id;

        public function __construct(string $resource_id) {
            $this->resource_id = $resource_id;
            $this->message = 'Script accessed the following resource: '.$resource_id;
            parent::__construct();
        }
        
    }

    class NetworkAccessNotice extends SandyPHPNotice {
        public function __construct() {
            $this->message = 'Script accessed a network resource';
        }
    }

    class SharedMemoryAccessNotice extends SandyPHPNotice {
        public function __construct() {
            $this->message = 'Script accessed shared memory';
        }
    }

    class BinaryExecutionNotice extends SandyPHPNotice {
        protected string $binary_id;
        public function __construct(string $binary_id) {
            $this->binary_id = $binary_id;
            $this->message = 'Script executed the binary identified with: '.$binary_id;
        }
    }

    class InterprocessCommunicationNotice extends SandyPHPNotice {
        public function __construct() {
            $this->message = 'Script communicated with another process';
        }
    }

    class HostInfoAccessNotice extends SandyPHPNotice {
        protected string $granted;
        public function __construct(bool $granted) {
            $this->message = 'The script tried to access information about the system it is running on. Access granted: '.($granted ? 'Yes' : 'No');
            $this->granted = $granted;
            parent::__construct();
        }
    }

    ?>