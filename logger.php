<?php

    interface Loggable {
        public function getLoggableMessage();
    }

    class Logger {

        protected $output_file;
        private $filehandle;

        public function __construct($output_file) {
            $this->output_file = $output_file;
            $this->filehandle = fopen($output_file, 'a', true);
            stream_set_blocking($this->filehandle, false);
        }

        public function log(SandyPHPException|SandyPHPNotice|SandyPHPError|Loggable $event) {
            fwrite($this->filehandle, $event->getLoggableMessage()."\n");
        }

        
    }

?>