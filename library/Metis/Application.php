<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: Eclipse Public License 1.0
 *
 * @category   Metis
 * @package    Metis
 * @copyright  2009 Darryl Patterson <widgetapps@gmail.com>
 * @license    http://metis.widgetapps.ca/license   Eclipse Public License 1.0
 * @version    $Id: Application.php 24 2009-12-15 07:55:37Z smartssa $
 * @link       http://code.google.com/p/widgetapps-metis/
 */

class Metis_Application extends Zend_Application
{
    function __construct()
    {
        if (!defined('APPLICATION_ENV') || !defined('APPLICATION_PATH')){
            throw new Exception('Apache config path missing.');
        }

        parent::__construct(APPLICATION_ENV, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.xml');

        Zend_Session::start();
    }
}