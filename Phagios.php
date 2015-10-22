<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *   Phagios - PHP 5 Nagios Plugin Helper
 *   Copyright (C) 2013 Patrick Kuti.
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright (c) 2013, Patrick Kuti
 * @license     http://www.gnu.org/licenses/gpl-3.0-standalone.html GNU General Public License *
 *
 * @link        http://github.com/patyx7/phagios
 *
 * @author      Patrick Kuti <source.code@introspect.in>, Hannes Van de Vel <h@nnes.be>
 */

// Phagios version number
define('VERSION', '0.1.0');

// Set timeouts as depicted in
// https://www.nagios-plugins.org/doc/guidelines.html#RUNTIME
ini_set('max_execution_time', '10');

// Do not abort on connection close from remote user
ignore_user_abort(true);

// Set up some error settings
ini_set('error_reporting', E_ALL);
ini_set('html_errors', false);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

// Make sure we're running from the command line
if (php_sapi_name() !== 'cli') {
    exit('This should be run from the command line.');
}

/**
 * Main Phagios Class.
 */
abstract class Phagios
{
    /**
     * Nagios plugin return code of Unknown
     * Should be used for invalid command line arguments or internal errors.
     *
     * @var int
     */
    const STATE_UNKNOWN = 3;

    /**
     * Nagios plugin return code of Critical
     * Should be use for a host or service being down, or critical threshold being exceeded.
     *
     * @var int
     */
    const STATE_CRITICAL = 2;

    /**
     * Nagios plugin return code of Warning
     * Should be used for a host or service being up and not working correctly, or warning threshld being exceeded.
     *
     * @var int
     */
    const STATE_WARNING = 1;

    /**
     * Nagios plugin return code of OK
     * Should be used for a host or service being up and working correctly or responding in acceptable time.
     *
     * @var int
     */
    const STATE_OK = 0;

    /**
     * Nagios verbosity level of summary (default)
     * Should be used for minimal output.
     *
     * @var int
     */
    const VERBOSE_SUMMARY = 0;

    /**
     * Nagios verbosity level of additional
     * Should be used for additional information (e.g listing processes that failed).
     *
     * @var int
     */
    const VERBOSE_ADDITIONAL = 1;

    /**
     * Nagios verbosity level of debug
     * Should be used for configuration debug output (e.g commands used).
     *
     * @var int
     */
    const VERBOSE_DEBUG = 2;

    /**
     * Nagios verbosity level of problem
     * Should be used for plugin problem diagnosis (e.g full stack traces).
     *
     * @var int
     */
    const VERBOSE_PROBLEM = 3;

    protected $pluginTimeZone = 'UTC';

    /**
     * Name of the plugin that will be run.
     *
     * @var string
     */
    protected $pluginName = 'Phagios';

    /**
     * Version of the plugin that will be run.
     *
     * @var string
     */
    protected $pluginVersion = '0.1.0';

    /**
     * Description of the plugin that will be run.
     *
     * @var string
     */
    protected $pluginDescription = 'Icinga2 / Nagios plugin';

    /**
     * Example usage of the plugin that will be run.
     *
     * @var string
     */
    protected $pluginUsage = './phagios.php -V -h -H <host_address> -p <port> -U <username> -P <password> -t <timeout> -w <warning_threshold> -c <critical_threshold>';

    /**
     * Example help of the plugin that will be run.
     *
     * @var string
     */
    protected $pluginHelp = '
    Options:
    -h, --help
    This help screen

    -v|vv|vvv, --verbose
    Verbose output

    -V, --version
    Version information
    ';

    /**
     * Verbosity level for verbose output.
     *
     * @var int
     */
    protected $verbosity = self::VERBOSE_SUMMARY;

    protected $pluginShortOpts = '';
    protected $pluginLongOpts = array();

    protected $pluginOpts = array();
    /**
     * Constructor.
     */
    public function __construct($pluginOpts = array())
    {
        # Define default shortopts
        $defaultShortOpts = 'V';
        $defaultShortOpts .= 'vv:';
        $defaultShortOpts .= 'h';
        $defaultShortOpts .= 't:';
        $defaultShortOpts .= 'w:';
        $defaultShortOpts .= 'c:';
        $defaultShortOpts .= 'z:';

        # Merge plugin & default shortopts
        $this->pluginShortOpts .= $defaultShortOpts;

        # Define default longopts
        $defaultLongOpts = array(
            'version',
            'verbose::',
            'help',
            'timeout:',
            'warning:',
            'critical:',
            'timezone:',
        );

        # Merge plugin & default longopts
        if (is_array($this->pluginLongOpts)) {
            $this->pluginLongOpts = array_merge($this->pluginLongOpts, $defaultLongOpts);
        } else {
            $this->pluginLongOpts = $defaultLongOpts;
        }

        # Get command line options
        $this->pluginOpts = getopt($this->pluginShortOpts, $this->pluginLongOpts);

        # Process them
        try {
            $this->getOpts();
        } catch (PhagiosUnknownException $e) {
            $this->cleanExit(self::STATE_UNKNOWN, $e->getMessage());
        } catch (Exception $e) {
            $this->cleanExit(self::STATE_UNKNOWN, $e->getMessage());
        }

        $this->debugOutput("\n".print_r($this->pluginOpts, true), self::VERBOSE_DEBUG);

        // As per http://php.net/manual/en/function.date-default-timezone-set.php
        date_default_timezone_set($this->pluginTimeZone);
        $this->debugOutput('Timezone: '.$this->pluginTimeZone, self::VERBOSE_ADDITIONAL);

        # Always cleanup when the script has finished
        register_shutdown_function(array($this, 'cleanUp'));
    }

    protected function getOpts()
    {
        # Exit with name, version & usage if no arguments
        if (sizeof($this->pluginOpts) == 0) {
            throw new PhagiosUnknownException(
            "\n".$this->pluginName.' '.$this->pluginVersion."\n".
            $this->pluginUsage);
        } else {
            foreach (array_keys($this->pluginOpts) as $opt) {
                switch ($opt) {
                    # Set verbosity
                    case 'v':
                    $verbosity = sizeof($this->pluginOpts['v']);
                    switch ($verbosity) {
                        case 1:
                        $this->setVerbosity(self::VERBOSE_ADDITIONAL);
                        break;
                        case 2:
                        $this->setVerbosity(self::VERBOSE_DEBUG);
                        break;
                        case 3:
                        $this->setVerbosity(self::VERBOSE_PROBLEM);
                        break;
                        default:
                        $this->setVerbosity(self::VERBOSE_SUMMARY);
                        break;
                    }
                    break;
                    $this->debugOutput('Verbosity: '.$verbosity, self::VERBOSE_ADDITIONAL);

                    # Exit unknown with version information.
                    case 'V':
                    throw new PhagiosUnknownException(
                    "\n".$this->pluginName.' '.$this->pluginVersion."\n".
                    $this->pluginDescription);

                    # Exit unknown with help
                    case 'h':
                    throw new PhagiosUnknownException(
                    "\n".$this->pluginName.' '.$this->pluginVersion."\n".
                    $this->pluginUsage."\n".$this->pluginHelp);

                    # Set Timeout
                    case 't':
                    ini_set('max_execution_time', $this->pluginOpts['t']);
                    $this->debugOutput('Timeout argument overwrite: '.$this->pluginOpts['t'], self::VERBOSE_ADDITIONAL);
                    break;

                    # Set Timezone
                    case 'z':
                    $this->pluginTimeZone = $this->pluginOpts['z'];
                    date_default_timezone_set($this->pluginTimeZone);
                    $this->debugOutput('Timezone argument overwrite: '.$this->pluginOpts['z'], self::VERBOSE_ADDITIONAL);
                    break;

                    default:
                    break;
                }
            }
        }
    }
    /**
     * Sets the verbosity level for the plugin that is about to run.
     *
     * @param int $level
     */
    public function setVerbosity($level)
    {
        if (is_int($level) && ($level > 3 || $level < 0)) {
            throw new InvalidArgumentException('Verbosity can only be set to a value between 0 and 3. Defaults to 0.');
        }
        $this->verbosity = $level;
        $this->debugOutput('Verbosity set', self::VERBOSE_ADDITIONAL);
    }

    /**
     * Main method to execute plugin's runChecks method.
     */
    public function run()
    {
        try {
            $pluginResult = $this->runChecks();
        } catch (PhagiosUnknownException $e) {
            $this->cleanExit(self::STATE_UNKNOWN, $e->getMessage());
        } catch (PhagiosCritcalException $e) {
            $this->cleanExit(self::STATE_CRITICAL, $e->getMessage());
        } catch (PhagiosWarningException $e) {
            $this->cleanExit(self::STATE_WARNING, $e->getMessage());
        } catch (Exception $e) {
            $this->cleanExit(self::STATE_UNKNOWN, $e->getMessage());
        }

        $this->cleanExit(self::STATE_OK, $pluginResult);
    }

    /**
     * Run the plugin.
     *
     * @return string informational status data
     */
    abstract protected function runChecks();

    /**
     * Clean up any outstanding connections.
     *
     * @return bool
     */
    private function cleanUp()
    {
        // TODO
        return true;
    }

    /**
     * Exit gracefully with correct return codes.
     *
     * @param int    $state   Exit status plugin should return
     * @param string $message Message plugin should return back to Nagios
     */
    private function cleanExit($state, $message)
    {
        print($message);
        $this->cleanUp();
        exit($state);
    }

    // Debug
    protected function debugOutput($message, $outputVerbosity)
    {
        if ($this->verbosity >= $outputVerbosity) {
            printf("%s\n", '['.str_repeat('v', $this->verbosity)."] $message");
        }
    }
}

class PhagiosUnknownException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = 'UNKNOWN - '.$message;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
class PhagiosCriticalException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = 'CRITICAL - '.$message;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
class PhagiosWarningException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = 'WARNING - '.$message;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
