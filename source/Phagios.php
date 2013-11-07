<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *   Phagios - PHP 5.3+ Nagios Plugin Helper
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
 * @author    Patrick Kuti <code@introspect.in>
 * @copyright 2013 Patrick Kuti
 * @license   http://www.gnu.org/licenses/gpl-3.0-standalone.html GNU General Public License
 * @version   Release: 0.0.1a
 * @link      http://github.com/patyx7/phagios
 */

namespace Phagios;

/**
 * Main Phagios Class
 *
 */
abstract class Phagios
{

    /**
     * Constant for Nagios unknown
     */
    const UNKNOWN = 3;

    /**
     * Constant for Nagios critical
     */
    const CRITICAL = 2;

    /**
     * Constant for Nagios warning
     */
    const WARNING = 1;

    /**
     * Constant for Nagios ok
     */
    const OK = 0;

    /**
     * Name of the plugin
     */
    protected $pluginName = 'Phagios';

    /**
     * Version of the plugin
     */
    protected $pluginVersion = '0.0.1a';

    /**
     * Description of the plugin
     */
    protected $pluginDescription = 'Nagios Plugin Helper for PHP';

    /**
     * Example usage of the plugin
     */
    protected $pluginUsage = './phagios.php -H <host_address> -p <port> -U <username> -P <password> -t <timeout> -w <warning> -c <critical>';

    /**
     * Verbosity level
     */
    protected $verbosity = 0;

    /**
     * Set the verbosity of the plugin
     */
    public function setVerbosity($level)
    {
        $this->verbosity = $level;
    }

    /**
     * Main method to execute plugin's run method
     */
    public function main()
    {
        try {
            $pluginResult = $this->run();
        } catch (\Phagios\Exception\Unknown $e) {
            // Bail out with information and prefData
        } catch (\Phagios\Exception\Critcal $e) {
            // Bail out with information and prefData
        } catch (\Phagios\Exception\Warning $e) {
            // Bail out with information and prefData
        } catch (\Exception $e) {
            // Generic warning can go here
        }

        print $pluginResult;
    }

    /**
     * Run the plugin
     */
    abstract protected function run();
}


class UnknownException extends \Exception
{
}
class CrticalException extends \Exception
{
}
class WarningException extends \Exception
{
}
