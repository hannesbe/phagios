#!/usr/bin/php
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

date_default_timezone_set('UTC');
define("VERSION", '1.0.0');

ini_set('max_execution_time', '55');
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

/**
 * Main Phagios Class
 *
 */
abstract class Phagios
{

    /**
     * Nagios plugin return code of Unknown
     * 
     * @var integer
     */
    const STATE_UNKNOWN = 3;

    /**
     * Nagios plugin return code of Critical
     * 
     * @var integer
     */
    const STATE_CRITICAL = 2;

    /**
     * Nagios plugin return code of Warning
     * 
     * @var integer
     */
    const STATE_WARNING = 1;

    /**
     * Nagios plugin return code of OK
     * 
     * @var integer
     */
    const STATE_OK = 0;

    /**
     * Nagios verbosity level of summary (default)
     * 
     * @var integer
     */
    const VERBOSE_SUMMARY = 0;

    /**
     * Nagios verbosity level of additional information
     * 
     * @var integer
     */
    const VERBOSE_ADDITIONAL = 1;

    /**
     * Nagios verbosity level of debug outout
     * 
     * @var integer
     */
    const VERBOSE_DEBUG = 2;

    /**
     * Nagios verbosity level of problem diagnosis
     * 
     * @var integer
     */
    const VERBOSE_DIAGNOSIS = 3;

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
     * Sets the verbosity level for the plugin that is about to run
     * 
     * @param type $level 
     * 
     * @return type
     */
    public function setVerbosity($level)
    {
        if ($level > 3 || $level < 0) {
            throw new Exception('Verbosity can only be set to an integer between and including 0 to 3.');
        }
        $this->verbosity = $level;
    }

    /**
     * Main method to execute plugin's run method
     * 
     * @method main
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
     */
    abstract protected function run();

    /**
     * Exit gracefully with correct return codes
     *
     * @method cleanExit
     *
     * @param  integer    $state   [description]
     * @param  string    $message [description]
     *
     * @return null  
     */
    private function cleanExit($state, $message)
    {
        print($message);
        exit($state);
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

return new Phagios();
