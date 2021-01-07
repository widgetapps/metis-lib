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
 * @version    $Id: GetUsername.php 24 2009-12-15 07:55:37Z smartssa $
 * @link       http://code.google.com/p/widgetapps-metis/
*/

class Metis_View_Helper_GetUsername extends Metis_View_Helper_Metis
{
	public function getUsername($userId)
	{
        $table_user = new models_User();
        $user       = $table_user->find($userId)->current();
        
        return $user->username;
	}
}