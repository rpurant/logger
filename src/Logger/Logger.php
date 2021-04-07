<?php


namespace Spier\Logger;


use InvalidArgumentException;

/**
 * Custom Logging Class to store log information
 * Author - rpurant
 * Date - 11.05.2017
 */
class Logger
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 600;
    public const EMERGENCY = 700;

    // declare log file and file pointer as private properties
    private string $logFile;
    private $fp;
    private string $errorStatus;
    private string $logRoot;

    /**
     * This is a static variable and not a constant to serve as an extension point for custom levels
     *
     * @var array<int, string> $levels Logging levels with the levels as key
     */
    protected static array $levels = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * @param $res
     * DateTime: 30/09/2018 11:17 PM
     * Created By: rpurant
     */
    public function logQueryStats($res): void
    {
        $res .= "\r\n";
        // set path and name of log file (optional)
        $this->errorStatus = static::getLevelName(self::INFO);
        $file_name = $this->getLogRoot() . 'dbStats_' . date('Ymd') . '.log';
        $this->logError($res, $file_name);
    }

    /**
     * @param $err
     * @param $file_name
     * DateTime: 30/09/2018 11:20 PM
     * Created By: rpurant
     */
    private function logError($err, $file_name): void
    {
        $this->lFile($file_name);
        // write message to the log file
        $this->lWrite($err);
        // close log file
        $this->lClose();
    }

    /**
     * set log file (path and name)
     * @param $path
     * DateTime - 11/05/2017 10:01 PM
     * Created By - rpurant
     */
    private function lFile($path): void
    {
        $this->logFile = $path;
    }

    /**
     * write message to the log file
     * @param $message
     * DateTime - 11/05/2017 10:07 PM
     * Created By - rpurant
     */
    private function lWrite($message): void
    {
        // if file pointer doesn't exist, then open log file
        if (!is_resource($this->fp)) {
            $this->lOpen();
        }
        // define script name
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        // define current time and suppress E_WARNING if using the system TZ settings
        // (don't forget to set the INI setting date.timezone)
        // Get time of request
        $time = @date('d.m.Y H:i:s');
        // Get IP address
        $addr = $_SERVER['REMOTE_ADDR'] ?? 'from cron';
        if (($remote_address = $addr) === '') {
            $remote_address = 'REMOTE_ADDR_UNKNOWN';
        }
        // Get requested script
//        if( ($request_uri = $_SERVER['REQUEST_URI']) === '') {
//            $request_uri = 'REQUEST_URI_UNKNOWN';
//        }
        // write current time, script name and message to the log file
        fwrite($this->fp, "[$time] [$remote_address] [" . $this->errorStatus . "] [$script_name] - $message" . PHP_EOL);
    }

    /**
     * open log file (private method)
     * DateTime - 11/05/2017 10:08 PM
     * Created By - rpurant
     */
    private function lOpen(): void
    {
        // in case of Windows set default log file
        $log_file_default = strncasecmp(PHP_OS, 'WIN', 3) === 0
            ? 'c:/php/logfile.txt' : '/tmp/logfile.txt';
        // define log file from l_file method or use previously set default
        $l_file = $this->logFile ?: $log_file_default;
        // open log file for writing only and place file pointer at the end of the file
        // (if the file does not exist, try to create it)
        $this->fp = fopen($l_file, 'ab') || exit("Can't open $l_file");
    }

    /**
     * close log file (it's always a good idea to close a file when you're done with it)
     * DateTime - 11/05/2017 10:07 PM
     * Created By - rpurant
     */
    private function lClose(): void
    {
        fclose($this->fp);
    }

    /**
     * @param $err
     * DateTime: 30/09/2018 11:25 PM
     * Created By: rpurant
     */
    public function logDBError($err): void
    {
        $err .= "\r\n";
        // set path and name of log file (optional)
        $this->errorStatus = static::getLevelName(self::ERROR);
        $file_name = $this->getLogRoot() . 'dbLogs_' . date('Ymd') . '.log';
        $this->logError($err, $file_name);
    }

    /**
     * To Handle normal error
     * @param $err
     * DateTime - 10/05/2017 09:08 PM
     * Created By - rpurant
     */
    public function logAppError($err): void
    {
        $err .= "\r\n";
        // set path and name of log file (optional)
        $this->errorStatus = static::getLevelName(self::ERROR);
        $file_name = $this->getLogRoot() . 'appLogs_' . date('Ymd') . '.log';
        $this->logError($err, $file_name);
    }

    /**
     * @return string
     */
    public function getLogRoot(): string
    {
        return $this->logRoot;
    }

    /**
     * @param string $logRoot
     */
    public function setLogRoot(string $logRoot): void
    {
        $this->logRoot = $logRoot;
    }

    /**
     * Description: getLevelName
     * @param int $level
     * @return string
     * Created by rpurant on 07/04/2021 6:14 PM
     */
    public static function getLevelName(int $level): string
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }
}