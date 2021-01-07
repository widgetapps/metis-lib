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
 * @version    $Id: Exception.php 24 2009-12-15 07:55:37Z smartssa $
 * @link       http://code.google.com/p/widgetapps-metis/
*/

class Metis_Auth_Exception extends Metis_Exception
{
    function __construct($message)
    {
        parent::__construct($message);
    }
    
    public function failed()
    {
    	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    	$redirector->gotoUrl('/index/index/failedauth');
    }
}
