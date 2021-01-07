<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: Eclipse Public License 1.0
 *
 * @category   Metis
 * @package    Metis_View_Helper
 * @copyright  2009 Darryl Patterson <widgetapps@gmail.com>
 * @license    http://metis.widgetapps.ca/license   Eclipse Public License 1.0
 * @version    $Id: GetLanguageLabel.php 24 2009-12-15 07:55:37Z smartssa $
 * @link       http://code.google.com/p/widgetapps-metis/
*/

class Metis_View_Helper_GetLanguageLabel extends Metis_View_Helper_Metis
{
    private $languageLabels;
    
    public function getLanguageLabel($key)
    {
	    if (isset($this->languageLabels[$key])){
	        return $this->languageLabels[$key];
	    }
	    
	    return '';
    }
    
    public function setView(Metis_View $view)
    {
        parent::setView($view);
        $this->languageLabels = $view->getLanguageLabels();
    }
    
}
