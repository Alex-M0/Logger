# WebPlace/Logger

Library provides the ability to easily log the system

## Installation

Require `webplace/logger` using composer.

## Usage

```php
<?php

use WebPlace\Logger;

Logger::setLogDirectory('/to_log_directory/');

Logger::writeLog('message to write log');

Logger::writeWarningLog('message to write log');

Logger::writeErrorLog('message to write log');

Logger::writeExceptionLog('message to write log', new Throwable('error'));

```
