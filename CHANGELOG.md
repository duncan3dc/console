Changelog
=========

## 2.3.1 - 2025-03-06

### Changed

* [Application] Explicitly declare nullable arguments.

--------

## 2.3.0 - 2024-07-29

### Changed

* [Support] Support for Symfony 7 has been added.
* [Application] Types have been added to properties.
* [Command] Types have been added to properties and method returns.

--------

## 2.2.0 - 2024-02-23

### Changed

* [Support] Support for PHP 8.1, 8.1, and 8.3 has been added.
* [Support] Support for Symfony 4 has been dropped.
* [Support] Support for Symfony 6 has been added.

--------

## 2.1.0 - 2021-08-15

### Changed

* [Support] Support for PHP 8.0 has been added.
* [Support] Support for PHP 7.3 has been dropped.

--------

## 2.0.2 - 2021-08-14

### Fixed

* [Application] Tidied up the internals of runCommand() to not duplicate the name of the command.

--------

## 2.0.1 - 2020-12-10

### Added

* [Support] Support for the latest version of Collision.

--------

## 2.0.0 - 2020-12-10

### Added

* [Support] Support for Symfony 5 has been added.

### Changed

* [Command] execute() must return an integer and declare a return type.
* [Command] getApplication() must return an instance of this library's application class.
* [Command] getName() will throw an exception if no name has been set.

### Removed

* [Support] Support for Symfony 3, 4.2, and 4.3 have been dropped, 4.4 is now required.
* [Support] Support for PHP 7.2 has been dropped.

--------

## 1.4.0 - 2019-08-23

### Added

* [Commands] Allow a custom suffix to be used (instead of the default `Command`).

### Changed

* [Commands] Use symfony/lock to provide command locking.

### Fixed

* [Application] Don't try to instantiate abstract classes or interfaces.

--------

## 1.3.0 - 2019-03-02

### Changed

* [Support] Added support for PHP 7.3
* [Support] Drop support for PHP 7.1
* [Support] Dropped support for Symfony 2.7, 3.1, 3.2, 3.3, 4.0, and 4.1

--------

## 1.2.1 - 2018-10-01

### Fixed

* [Application] Prevent installation of a upstream version with an exit code bug.

--------

## 1.2.0 - 2018-09-04

### Added

* [Commands] Added a `ListCommand` command that enhances the standard list capabilities.

--------

## 1.1.0 - 2018-08-21

### Added

* [General] Added a `command()` method that type hints against duncan3dc/symfony-climate.

--------

## 1.0.0 - 2018-05-04

### Added

* [General] Use [collision](https://github.com/nunomaduro/collision) for exception handling.

### Changed

* [Support] Drop support for PHP 7.0

### Removed

* [Application] `getTerminalWidth()/getTerminalHeight()` have been removed following their removal upstream. (The [Terminal class](http://symfony.com/blog/new-in-symfony-3-2-console-improvements-part-2) should be used instead).

--------

## 0.7.0 - 2018-04-05

### Changed

* [General] Most methods now use type hints where possible
* [Support] Drop support for PHP 5.6

--------

## 0.6.0 - 2017-12-12

### Added

* [Commands] Commands now output how long they ran for, and how much memory they used

### Changed

* [Support] Add support for PHP 7.2
* [Support] Drop support for HHVM
* [Support] Add support for Symfony 4.0

--------

## 0.5.0 - 2016-11-25

### Changed

* [Dependencies] Use duncan3dc/symfony-climate to handle output
* [Support] Add support for PHP 7.1

--------

## 0.4.0 - 2016-01-30

### Changed

* [Support] Drop support for PHP 5.5

--------

## 0.3.0 - 2015-09-13

### Fixed

* [Commands] Correct the permissions used on the lock file

--------

## 0.2.0 - 2015-06-22

### Added

* [Commands] Allow an individual namespace to be listed using a trailing colon
* [Application] Make it possible to get the terminal width/height

### Changed

* [Support] Add support for Symfony 2.6
* [Support] Add support for PHP 7.0

### Fixed

* [Commands] Corrected the check for the --no-time-limit option

--------

## 0.1.0 - 2014-11-23

### Added

* [Commands] Automatically create commands from classes
* [Output] Use CLImate for console output
* [Commands] Allow commands to time limit themselves
* [Application] Made it easier to call an existing command
* [Commands] Lock all commands by default
* [Application] Added a tab completion handler

--------
