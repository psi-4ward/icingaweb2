<?php
// {{{ICINGA_LICENSE_HEADER}}}
/**
 * Icinga 2 Web - Head for multiple monitoring frontends
 * Copyright (C) 2013 Icinga Development Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @copyright 2013 Icinga Development Team <info@icinga.org>
 * @author Icinga Development Team <info@icinga.org>
 */
// {{{ICINGA_LICENSE_HEADER}}}

/**
 * Rendering helper for flags depending on objects
 */
class Zend_View_Helper_MonitoringFlags extends Zend_View_Helper_Abstract
{
    /**
     * Key of flags without prefix (e.g. host or service)
     * @var string[]
     */
    private static $keys = array(
        'passive_checks_enabled' => 'Passive checks',
        'active_checks_enabled'  => 'Active checks',
        'obsess_over_host'       => 'Obsessing',
        'notifications_enabled'  => 'Notifications',
        'event_handler_enabled'  => 'Event handler',
        'flap_detection_enabled' => 'Flap detection',
    );

    /**
     * Type prefix
     * @param array $vars
     * @return string
     */
    private function getObjectType(array $vars)
    {
        return array_shift(explode('_', array_shift(array_keys($vars)), 2));
    }

    /**
     * Build all existing flags to a readable array
     * @param stdClass $object
     * @return array
     */
    public function monitoringFlags(\stdClass $object)
    {
        $vars = (array)$object;
        $type = $this->getObjectType($vars);
        $out = array();

        foreach (self::$keys as $key => $name) {
            $value = false;
            if (array_key_exists(($realKey = $type. '_'. $key), $vars)) {
                $value = $vars[$realKey] === '1' ? true : false;
            }
            $out[$name] = $value;
        }

        return $out;
    }
}