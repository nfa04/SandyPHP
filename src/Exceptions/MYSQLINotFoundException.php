<?php
namespace SandyPHP\Exceptions;
class MYSQLINotFoundException extends SandyPHPException {

public function __construct() {
    $this->message = 'SandyPHP couldn\'t find the MySQLI extension.';
}

}
?>