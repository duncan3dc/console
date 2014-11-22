console
=======

Create command line php applications using symfony/console.  
This is basically a fork of symfony/console which does things a little differently and adds some extra features.  

[![Build Status](https://travis-ci.org/duncan3dc/console.svg?branch=master)](https://travis-ci.org/duncan3dc/console)


Loading Commands
----------------
Commands can be automatically created from classes using the following criteria:
* Files/classes must be named using CamelCase and must end in "Command" (with files having the .php extension)
* Each uppercase character will be converted to lowercase and preceded by a hyphen
* Directories will represent namespaces and each separater will be replaced with a colon
Using the example below, the file src/commands/Category/Topic/RunCommand.php will create a command called category:topic:run
```php
$application->loadCommands("src/commands");
```
_Of course, they can still be added the [symfony way](http://symfony.com/doc/current/components/console/introduction.html)_
