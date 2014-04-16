<?php
// {{{ICINGA_LICENSE_HEADER}}}
/**
 * This file is part of Icinga Web 2.
 *
 * Icinga Web 2 - Head for multiple monitoring backends.
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
 * @copyright  2013 Icinga Development Team <info@icinga.org>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL, version 2
 * @author     Icinga Development Team <info@icinga.org>
 *
 */
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Form\Config\Authentication;

use \Exception;
use \Zend_Config;
use Icinga\Web\Form;
use Icinga\Data\ResourceFactory;
use Icinga\Authentication\UserBackend;

/**
 * Form for adding or modifying LDAP authentication backends
 */
class LdapBackendForm extends BaseBackendForm
{
    /**
     * Return content of the resources.ini or previously set resources
     *
     * @return  array
     */
    public function getResources()
    {
        if ($this->resources === null) {
            $res = ResourceFactory::getResourceConfigs('ldap')->toArray();

            foreach (array_keys($res) as $key) {
                $res[$key] = $key;
            }

            return $res;
        } else {
            return $this->resources;
        }
    }

    /**
     * Create this form and add all required elements
     *
     * @see Form::create()
     */
    public function create()
    {
        $this->setName('form_modify_backend');
        $name = $this->filterName($this->getBackendName());
        $backend = $this->getBackend();

        $this->addElement(
            'text',
            'backend_' . $name . '_name',
            array(
                'required'      => true,
                'allowEmpty'    => false,
                'label'         => t('Backend Name'),
                'helptext'      => t('The name of this authentication backend'),
                'value'         => $this->getBackendName()
            )
        );

        $this->addElement(
            'select',
            'backend_' . $name . '_resource',
            array(
                'required'      => true,
                'allowEmpty'    => false,
                'label'         => t('LDAP Resource'),
                'helptext'      => t('The resource to use for authenticating with this provider'),
                'value'         => $this->getBackend()->get('resource'),
                'multiOptions'  => $this->getResources()
            )
        );

        $this->addElement(
            'text',
            'backend_' . $name . '_user_class',
            array(
                'required'  => true,
                'label'     => t('LDAP User Object Class'),
                'helptext'  => t('The object class used for storing users on the ldap server'),
                'value'     => $backend->get('user_class', 'inetOrgPerson')
            )
        );

        $this->addElement(
            'text',
            'backend_' . $name . '_user_name_attribute',
            array(
                'required'  => true,
                'label'     => t('LDAP User Name Attribute'),
                'helptext'  => t('The attribute name used for storing the user name on the ldap server'),
                'value'     => $backend->get('user_name_attribute', 'uid')
            )
        );

        $this->addElement(
            'button',
            'btn_submit',
            array(
                'type'      => 'submit',
                'value'     => '1',
                'escape'    => false,
                'class'     => 'btn btn-cta btn-wide',
                'label'     => '<i class="icinga-icon-save"></i> Save Backend'
            )
        );
    }

    /**
     * Return the ldap authentication backend configuration for this form
     *
     * @return  array
     *
     * @see     BaseBackendForm::getConfig()
     */
    public function getConfig()
    {
        $prefix = 'backend_' . $this->filterName($this->getBackendName()) . '_';
        $section = $this->getValue($prefix . 'name');
        $cfg = array(
            'backend'               => 'ldap',
            'resource'              => $this->getValue($prefix . 'resource'),
            'user_class'            => $this->getValue($prefix . 'user_class'),
            'user_name_attribute'   => $this->getValue($prefix . 'user_name_attribute')
        );

        return array($section => $cfg);
    }

    /**
     * Validate the current configuration by creating a backend and requesting the user count
     *
     * @return  bool    Whether validation succeeded or not
     *
     * @see BaseBackendForm::isValidAuthenticationBacken
     */
    public function isValidAuthenticationBackend()
    {
        try {
            $cfg = $this->getConfig();
            $backendName = key($cfg);
            $testConn = UserBackend::create($backendName, new Zend_Config($cfg[$backendName]));

            if ($testConn->count() === 0) {
                $this->addErrorMessage(t('No users found on directory server'));
                return false;
            }
        } catch (Exception $exc) {
            $this->addErrorMessage(sprintf(t('Connection validation failed: %s'), $exc->getMessage()));
            return false;
        }

        return true;
    }
}
