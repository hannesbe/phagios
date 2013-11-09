#!/usr/bin/env php
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *   Phagios - PHP 5 Nagios Plugin Helper
 *   Copyright (C) 2013 Patrick Kuti
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
 * @package     patyx7 / phagios 
 * @copyright   Copyright (c) 2013, Patrick Kuti
 * @license     http://www.gnu.org/licenses/gpl-3.0-standalone.html GNU General Public License
 * @link        http://github.com/patyx7/phagios
 * @author      Patrick Kuti <code@introspect.in>
 */

// As per http://php.net/manual/en/function.date-default-timezone-set.php
date_default_timezone_set('UTC');

// Phagios version number
define("VERSION", '0.0.1a');

// Set timesouts as depicted in 
// https://www.nagios-plugins.org/doc/guidelines.html#RUNTIME
ini_set('max_execution_time', '55');

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
 * Main Phagios Class
 */
abstract class Phagios
{

    /**
     * Nagios plugin return code of Unknown
     * Should be used for invalid command line arguments or internal errors
     * 
     * @var integer
     */
    const STATE_UNKNOWN = 3;

    /**
     * Nagios plugin return code of Critical
     * Should be use for a host or service being down, or critical threshold being exceeded
     * 
     * @var integer
     */
    const STATE_CRITICAL = 2;

    /**
     * Nagios plugin return code of Warning
     * Should be used for a host or service being up and not working correctly, or warning threshld being exceeded
     * 
     * @var integer
     */
    const STATE_WARNING = 1;

    /**
     * Nagios plugin return code of OK
     * Should be used for a host or service being up and working correctly or responding in acceptable time
     * 
     * @var integer
     */
    const STATE_OK = 0;

    /**
     * Nagios verbosity level of summary (default)
     * Should be used for minimal output
     * 
     * @var integer
     */
    const VERBOSE_SUMMARY = 0;

    /**
     * Nagios verbosity level of additional
     * Should be used for additional information (e.g listing processes that failed)
     * 
     * @var integer
     */
    const VERBOSE_ADDITIONAL = 1;

    /**
     * Nagios verbosity level of debug
     * Should be used for configuration debug output (e.g commands used)
     * 
     * @var integer
     */
    const VERBOSE_DEBUG = 2;

    /**
     * Nagios verbosity level of problem
     * Should be used for plugin problem diagnosis (e.g full stack traces)
     * 
     * @var integer
     */
    const VERBOSE_PROBLEM = 3;

    /**
     * Name of the plugin that will be run
     *
     * @var string
     */
    protected $pluginName = 'Phagios';

    /**
     * Version of the plugin that will be run
     *
     * @var string
     */
    protected $pluginVersion = '0.0.1a';

    /**
     * Description of the plugin that will be run
     *
     * @var string
     */
    protected $pluginDescription = 'Nagios Plugin Helper for PHP';

    /**
     * Example usage of the plugin that will be run
     *
     * @var string
     */
    protected $pluginUsage = "./phagios.php -V -h -H <host_address> -p <port> -U <username> -P <password> -t <timeout> -w <warning_threshold> -c <critical_threshold>";

    /**
     * Verbosity level for verbose output
     * 
     * @var integer
     */
    protected $verbosity = 0;

    /**
     * Constructor
     * 
     * @return null
     */
    public function __construct()
    {
        # Always cleanup
        register_shutdown_function(array(&$this, 'cleanUp'));
    }

    /**
     * Sets the verbosity level for the plugin that is about to run
     * 
     * @param integer $level 
     * 
     * @return null
     */
    public function setVerbosity($level)
    {
        if (is_int($level) && ($level > 3 || $level < 0)) {
            throw new InvalidArgumentException('Verbosity can only be set to an integer between and including 0 to 3.');
        }
        $this->verbosity = $level;
    }

    /**
     * Main method to execute plugin's run method
     * 
     * @return null
     */
    public function main()
    {
        try {
            $pluginResult = $this->run();
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
     * Run the plugin
     *
     * @return  string  informational status data
     */
    abstract protected function run();

    /**
     * Exit gracefully with correct return codes
     *
     * @param   integer     $state      Exit status plugin should return
     * @param   string      $message    Message plugin should return back to Nagios
     *
     * @return null  
     */
    private function cleanExit($state, $message)
    {
        print($message);
        $this->cleanUp();
        exit($state);
    }

    /**
     * Clean up any outstanding connections
     * 
     * @return boolean
     */
    private function cleanUp()
    {
        //TODO
        return true;
    }
}

class PhagiosUnknownException extends Exception
{
}
class PhagiosCritcalException extends Exception
{
}
class PhagiosWarningException extends Exception
{
}
