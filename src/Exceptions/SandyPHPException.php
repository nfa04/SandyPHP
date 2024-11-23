<?php
namespace SandyPHP\Exceptions;
class SandyPHPException extends \Exception implements \Throwable, \SandyPHP\Logger\Loggable {
    protected $timestamp;

    public function __construct() {
        $this->timestamp = time();
    }

    public function getLoggableMessage() {
        return date("[d.m.Y - H:i]", $this->timestamp).'[Exception]['.get_class($this).']: '.$this->message;
    }

}
?>