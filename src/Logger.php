<?php

declare(strict_types=1);

namespace WebPlace;

use Throwable;

/**
 * Class Logger
 *
 * @package
 * @date 2021-09-24
 * @author Alex <dev@webplace.net.ua>
 */
class Logger
{
    /**
     * Обычный лог
     */
    public const NORMAL_LOG = 0;

    /**
     * Лог предуприждений
     */
    public const WARNING_LOG = 1;

    /**
     * Лог ошибок
     */
    public const ERROR_LOG = 2;

    /**
     * Лог исключений
     */
    public const EXCEPTION_LOG = 3;

    /**
     * @var string
     */
    protected static $logDirectory;

    /**
     * Конструктор класса
     */
    private function __construct()
    {
    }

    /**
     * Задаёт путь к каталогу хранения логов с конечным слешем
     *
     * @param string $logDirectory
     */
    public static function setLogDirectory(string $logDirectory)
    {
        static::$logDirectory = $logDirectory;
    }

    /**
     * Возвращает стек вызовов
     *
     * @param int $limit
     * @param int $removeFirstLines
     *
     * @return string
     */
    public static function getDebugBacktrace(int $limit = 10, int $removeFirstLines = 0): string
    {
        $stack = '';
        $trace = debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS,
            $limit + $removeFirstLines + 1
        );
        $trace = array_slice($trace, $removeFirstLines + 1);

        foreach ($trace as $key => $node) {
            $file = $node['file'] ?? '???';
            $line = $node['line'] ?? '???';

            $key++;
            $stack .= "#$key $file ($line): ";

            if (isset($node['class'])) {
                $stack .= $node['class'] . $node['type'];
            }

            $stack .= $node['function'] . '()' . PHP_EOL;
        }

        return $stack;
    }

    /**
     * Возвращает название лог файла
     *
     * @param int|string $logName
     *
     * @return string
     */
    public static function getLogFileName($logName): string
    {
        if (is_int($logName)) {
            switch ($logName) {
                case self::NORMAL_LOG:
                    $logName = 'common';
                    break;

                case self::WARNING_LOG:
                    $logName = 'warning';
                    break;

                case self::ERROR_LOG:
                    $logName = 'error';
                    break;

                case self::EXCEPTION_LOG:
                    $logName = 'exception';
                    break;

                default:
                    $logName = 'unknown';
            }
        }

        $logName .= date('-Y-m-d') . '.log';

        return $logName;
    }

    /**
     * Записывает лог на диск
     *
     * @param string            $message
     * @param array|string|null $additionalData
     * @param int|string        $logName
     * @param bool|int          $withStack Позволяет настроить количество записей,
     *                                     если передано true, то устанавливаеься 10 записей по умолчанию
     *
     * @return bool
     */
    public static function writeLog(
        string $message,
        $additionalData = null,
        $logName = self::NORMAL_LOG,
        $withStack = -1
    ): bool {
        $message = date('[Y-m-d H:i:s] ') . $message;

        if (is_array($additionalData)) {
            $message .= ': ' . print_r($additionalData, true);
        } elseif (is_string($additionalData) && $additionalData != '') {
            $message .= ': ' . $additionalData;
        }

        if (is_bool($withStack)) {
            $withStack = $withStack ? 10 : -1;
        }

        if ($withStack > -1) {
            $message .= static::getDebugBacktrace($withStack);
        }

        return error_log(
            trim($message) . PHP_EOL . PHP_EOL,
            3,
            static::$logDirectory . static::getLogFileName($logName)
        );
    }

    /**
     * Записывает лог предупреждения на диск
     *
     * @param string            $message
     * @param array|string|null $additionalData
     * @param bool|int          $withStack Позволяет настроить количество записей,
     *                                     если передано true то устанавливаеься 10 записей по умолчанию
     *
     * @return bool
     */
    public static function writeWarningLog(string $message, $additionalData = null, $withStack = -1): bool
    {
        return static::writeLog($message, $additionalData, self::WARNING_LOG, $withStack);
    }

    /**
     * Записывает лог ошибки на диск
     *
     * @param string            $message
     * @param array|string|null $additionalData
     * @param bool|int          $withStack Позволяет настроить количество записей,
     *                                     если передано true то устанавливаеься 10 записей по умолчанию
     *
     * @return bool
     */
    public static function writeErrorLog(string $message, $additionalData, $withStack = true): bool
    {
        return static::writeLog($message, $additionalData, self::ERROR_LOG, $withStack);
    }

    /**
     * Записывает лог исключений на диск
     *
     * @param string            $message
     * @param Throwable         $exception
     * @param array|string|null $additionalData
     *
     * @return bool
     */
    public static function writeExceptionLog(string $message, Throwable $exception, $additionalData = null): bool
    {
        $message .= ': ' . get_class($exception) .
            " #{$exception->getCode()}: {$exception->getMessage()}" . PHP_EOL . $exception->getTraceAsString();

        return static::writeLog($message, $additionalData, self::EXCEPTION_LOG);
    }
}
