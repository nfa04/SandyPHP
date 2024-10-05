# SandyPHP
SandyPHP uses [PHPSandbox](https://github.com/Corveda/PHPSandbox) to run any given script within a sandbox. However, in contrast to PHPSandbox, it does have another approach to controlling what a script can and can not do. While PHPSandbox does whitelisting, re-defining etc. of certain functions, classes, traits, etc., SandyPHP is an abstraction of that, allowing to set/unset/restrict permissions for specific use-cases that might be dangerous to the machine the script is running on. For example:

  - Access to storage
  - Access to the network
  - Access to any information on the host system
  - Sending E-Mails (if a mail server is configured for the PHP instance)
  - Interprocess communication
  - Access to databases

This is achieved by intercepting calls to functions, methods, etc. connected to any of these functions. Whenever SandyPHP has to refuse to execute this function call it will try to return a value that won't break most scripts.

SandyPHP also supports a system to log notices and exceptions caused when the script is running, this includes for example permission requests and violations.

## Disclaimer
This software is meant for experimental purposes. I do NOT recommend running it on any public server and do NOT guarantee for its security in any way. Although I tried to make it as compatible as possible with normal PHP, there might be edge-cases, where scripts will break.
