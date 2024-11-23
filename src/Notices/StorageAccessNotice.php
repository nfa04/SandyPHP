<?php
namespace SandyPHP\Notices;
class StorageAccessNotice extends SandyPHPNotice {
    public function __construct(string $resource, bool $print_message) {
        $this->message = 'Script accessed the following storage resource: '.$resource;
        parent::__construct($print_message);
    }
}
?>