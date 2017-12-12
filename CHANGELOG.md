Changelog
=========

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
