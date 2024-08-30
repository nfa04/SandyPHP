<?php

    class Logger {

        protected $output_file;
        private $filehandle;

        public function __construct($output_file) {
            $this->output_file = $output_file;
            $this->filehandle = fopen($output_file, 'a', true);
            stream_set_blocking($this->filehandle, false);
        }

        public function logException(SandyPHPException $exception) {
            fwrite($this->filehandle, $exception->getLoggableMessage()."\n");
        }

        public function logError(SandyPHPError $error) {
            fwrite($this->filehandle, $error->getLoggableMessage());
        }

        public function logNotice(SandyPHPNotice $notice) {
            fwrite($this->filehandle, $notice->getLoggableMessage());
        }
        
    }

?>