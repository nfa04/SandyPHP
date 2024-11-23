<?php
namespace SandyPHP\Notices;
class SandyPHPNotice implements \SandyPHP\Logger\Loggable {
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
?>