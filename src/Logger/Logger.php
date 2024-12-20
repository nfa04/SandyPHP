<?php
namespace SandyPHP\Logger;

interface Loggable {
    public function getLoggableMessage();
}

class Logger {

    protected $output_file;
    private $filehandle;
    private string $id;
    private bool $exceptionsEnabled;
    private bool $noticesEnabled;

    public function __construct(string $output_file, string $id) {
        $this->id = $id;
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

    public function getID() {
        return $this->id;
    }

    public function getLogFileName() {
        return $this->output_file;
    }

}
?>