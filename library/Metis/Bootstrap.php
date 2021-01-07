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
 * @version    $Id: Bootstrap.php 24 2009-12-15 07:55:37Z smartssa $
 * @link       http://code.google.com/p/widgetapps-metis/
*/

class Metis_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function initDb()
	{
		
	}
	
	protected function _initView()
	{
        $view = new Metis_View();
        $helperPath = METIS_PATH . DIRECTORY_SEPARATOR
                      . 'Metis' . DIRECTORY_SEPARATOR
                      . 'View' . DIRECTORY_SEPARATOR . 'Helper';
        $view->addHelperPath($helperPath, 'Metis_View_Helper');
        
        $resources = $this->getOption('resources');
        
        if (isset($resources['view']['encoding'])){
        	$view->setEncoding($resources['view']['encoding']);
        } else {
        	$view->setEncoding('UTF-8');
        }
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view);
		
	}
}
