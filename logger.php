<?php

    interface Loggable {
        public function getLoggableMessage();
    }

    class Logger {

        protected $output_file;
        private $filehandle;
        private bool $exceptionsEnabled;
        private bool $noticesEnabled;

        public function __construct($output_file) {
            $this->output_file = $output_file;
            $this->filehandle = fopen($output_file, 'a', true);
            stream_set_blocking($this->filehandle, false);
        }

        public function log(SandyPHPException|SandyPHPNotice|SandyPHPError|Loggable $event) {
            if(($this->exceptionsEnabled AND $event instanceof SandyPHPException) OR ($this->noticesEnabled AND $event instanceof SandyPHPNotice)) fwrite($this->filehandle, $event->getLoggableMessage()."\n");
        }

        public function setExceptionsEnabled(bool $enabled) {
            $this->exceptionsEnabled = $enabled;
        }
        
        public function setNoticesEnabled(bool $enabled) {
            $this->noticesEnabled = $enabled;
        }

    }

?>