<?php 

    class SandyPHPNotice implements Loggable {
        protected $stacktrace;
        protected $time;
        protected $message;

        public function __construct(bool $print_message) {
            $this->time = time();
            $this->stacktrace = debug_backtrace();
            if($print_message) echo $this->message;
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

        public function __construct(string $resource_id, bool $print_message) {
            $this->resource_id = $resource_id;
            $this->message = 'Script accessed the following resource: '.$resource_id;
            parent::__construct($print_message);
        }
        
    }

    class NetworkAccessNotice extends SandyPHPNotice {
        public function __construct(bool $print_message) {
            $this->message = 'Script accessed a network resource';
            parent::__construct($print_message);
        }
    }

    class SharedMemoryAccessNotice extends SandyPHPNotice {
        public function __construct(bool $print_message) {
            $this->message = 'Script accessed shared memory';
            parent::__construct($print_message);
        }
    }

    class BinaryExecutionNotice extends SandyPHPNotice {
        protected string $binary_id;
        public function __construct(string $binary_id, bool $print_message) {
            $this->binary_id = $binary_id;
            $this->message = 'Script executed the binary identified with: '.$binary_id;
            parent::__construct($print_message);
        }
    }

    class InterprocessCommunicationNotice extends SandyPHPNotice {
        public function __construct(bool $print_message) {
            $this->message = 'Script communicated with another process';
            parent::__construct($print_message);
        }
    }

    class HostInfoAccessNotice extends SandyPHPNotice {
        protected string $granted;
        public function __construct(bool $granted, bool $print_message) {
            $this->message = 'The script tried to access information about the system it is running on. Access granted: '.($granted ? 'Yes' : 'No');
            $this->granted = $granted;
            parent::__construct($print_message);
        }
    }

    class ScriptExecutionStartNotice extends SandyPHPNotice {
        public function __construct(string $sandbox_id, string $exec_id, bool $print_message) {
            $this->message = 'Script execution started. ExecID: '.$exec_id.', SandyPHPSandboxID: '.$sandbox_id;
            parent::__construct($print_message);
        }
    }

    class ScriptExecutionEndNotice extends SandyPHPNotice {
        public function __construct(string $sandbox_id, string $exec_id, bool $print_message) {
            $this->message = 'Script execution ended. ExecID: '.$exec_id.', SandyPHPSandboxID: '.$sandbox_id;
            parent::__construct($print_message);
        }
    }

    class SandboxCreationNotice extends SandyPHPNotice {
        public function __construct(string $sandbox_id, bool $print_message) {
            $this->message = 'SandyPHP sandbox created. SandyPHPSandboxID: '.$sandbox_id;
            parent::__construct($print_message);
        }
    }

    class SandboxDestructionNotice extends SandyPHPNotice {
        public function __construct(string $sandbox_id, bool $print_message) {
            $this->message = 'SandyPHP sandbox destructed. SandyPHPSandboxID: '.$sandbox_id;
            parent::__construct($print_message);
        }
    }

    ?>