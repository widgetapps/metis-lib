<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: Eclipse Public License 1.0
 *
 * @category   Metis
 * @package    Metis_Controller
 * @copyright  2009 Darryl Patterson <widgetapps@gmail.com>
 * @license    http://metis.widgetapps.ca/license   Eclipse Public License 1.0
 * @version    $Id: Action.php 28 2010-07-16 14:44:43Z widgetapps $
 * @link       http://code.google.com/p/widgetapps-metis/
 */

abstract class Metis_Controller_Action extends Zend_Controller_Action
{
    private $languageLabels;
    protected $auth;
    protected $acl;

    function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->languageLabels = array();
        $this->loadLabels();
        $this->view->setLanguageLabels($this->languageLabels);
    }

    public function init()
    {
        $this->_helper->layout->setLayout('default');
        $this->moduleName  = $this->getRequest()->getModuleName();
        $this->auth        = Zend_Auth::getInstance();
        $this->acl         = new Zend_Acl();

        if ($this->auth->hasIdentity()){
            if (isset($this->auth->getIdentity()->role)) {
                $this->view->role        = $this->auth->getIdentity()->role;
            }
            $this->view->hasIdentity = true;
            if (isset($this->auth->getIdentity()->userId)) {
                $this->view->userId      = $this->auth->getIdentity()->userId;
            }
        } else {
            $this->view->role        = 'guest';
            $this->view->hasIdentity = false;
        }
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->setTitle();
        $this->setPageId();
        $this->setupAcl();
    }

    protected function getLabel($key)
    {
        if (isset($this->languageLabels[$key])){
            return $this->languageLabels[$key];
        }

        return '';
    }

    protected function failedLogin()
    {
        $failedLogin = new Zend_Session_Namespace('failedLogin');
        $failedLogin->setExpirationHops(3);
        $failedLogin->redirect = $_SERVER['REQUEST_URI'];
        $this->_redirect($this->config->failed_login_redirect);
    }

    // TODO: Add in exception handling.
    function setupAcl()
    {
        if (! $this->auth->hasIdentity()){
            return;
        }

        $this->acl = new Zend_Acl();

        $aclPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'acl.xml';

        if (!file_exists($aclPath)){
            throw new Metis_Exception('Cannot find ACL file: [' . $aclPath . ']');
        }

        $acl_dom = new DOMDocument();
        $acl_dom->load($aclPath);

        if ($this->view->getEncoding() != $acl_dom->encoding){
            throw new Metis_Exception('Error loading ACL file [' . $aclPath . ']: XML encoding did not match. This application requires ' . $this->view->getEncoding());
        }

        $acl_xml = simplexml_import_dom($acl_dom);

        // Load the roles
        foreach ($acl_xml->roles->role as $role){
            $this->acl->addRole(new Zend_Acl_Role((string)$role['name']));
        }

        if ($this->auth->hasIdentity()) {
            // special unique username role
            if (isset($this->auth->getIdentity()->role)) {
                try {
                    $this->acl->addRole(new Zend_Acl_Role($this->auth->getIdentity()->username), $this->auth->getIdentity()->role);
                } catch (Exception $e) {
                }
            } else {
                try {
                    $this->acl->addRole(new Zend_Acl_Role($this->auth->getIdentity()->username));
                } catch (Exception $e) {
                }
            }
        }

        // Load the resources
        foreach ($acl_xml->resources->resource as $resource){
            try {
                $this->acl->add(new Zend_Acl_Resource((string)$resource['name']));
            } catch (Exception $e) {
            }
        }

        // Load the rights
        // TODO: Need to account for null resource, if no resources are defined.
        foreach ($acl_xml->rights->right as $right){
            $rightsArray = array();
            foreach ($right->children() as $child){
                $rightsArray[] = $child->getName();
            }

            switch ((string)$right['type']){
                case 'allow':
                    $this->acl->allow((string)$right['role'], (string)$right['resource'], $rightsArray);
                    break;
                case 'deny':
                    $this->acl->deny((string)$right['role'], (string)$right['resource'], $rightsArray);
                    break;
            }
        }
    }

    protected function authenticateAction($privilege)
    {
        try {
            // username inherits any other roles
            if ($this->auth->hasIdentity()) {
            	return $this->acl->isAllowed($this->auth->getIdentity()->username, $this->moduleName, $privilege);
            } else {
            	throw new Metis_Auth_Exception($e);
            }
        } catch (Exception $e) {
            throw new Metis_Auth_Exception($e);
        }
    }

    protected function getLanguage()
    {
        $session_env = new Zend_Session_Namespace('env');
        if (isset($session_env->language)){
            return $session_env->language;
        }

        $resources = $this->getInvokeArg('bootstrap')->getOption('resources');
        if (isset($resources['view']['language'])){
            return $resources['view']['language'];
        }

        return 'en';
    }

    private function loadLabels()
    {
        $globalLabelsPath = APPLICATION_PATH .
        DIRECTORY_SEPARATOR . 'localization' . DIRECTORY_SEPARATOR .
        $this->getLanguage() . '.xml';
        $this->loadLabelFile($globalLabelsPath);

        $moduleLabelPath  = APPLICATION_PATH .
        DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .
        $this->getRequest()->getModuleName() . DIRECTORY_SEPARATOR .
	                        'localization' . DIRECTORY_SEPARATOR . $this->getLanguage() .
	                        '.xml';
        $this->loadLabelFile($moduleLabelPath);
    }

    private function loadLabelFile($path)
    {
        if (!file_exists($path)){
            throw new Metis_Exception('Cannot find language file: [' . $path . ']');
        }

        $language_dom = new DOMDocument();
        $language_dom->load($path);
        if ($this->view->getEncoding() != $language_dom->encoding){
            throw new Metis_Exception('Error loading language file [' . $path . ']: XML encoding did not match.');
        }

        $language_xml = simplexml_import_dom($language_dom);
        foreach ($language_xml->label as $label){
            $key   = (string)$label['key'];
            $value = (string)$label;
            $this->languageLabels[trim($key)] = trim($value);
        }
    }

    protected function setPageId($override = null) {
        // set the page body id to the module_controller_action for css magic
        if ($override) {
            $this->view->pageId = $override;
        } else {
            $this->view->pageId = strtolower(
            $this->_request->getModuleName() . '_' .
            $this->_request->getControllerName() . '_' .
            $this->_request->getActionName());
        }
    }

    protected function setTitle($title = null) {
        // set the page title to the default, overridable on an action basis.
        if ($title) {
            $this->view->headTitle()->setSeparator(' - ')->append($title);
        } else {
            // pull the default from the config/application.xml file
            $pagetitle = $this->getInvokeArg('bootstrap')->getOption('pagetitle');
            $this->view->headTitle()->setSeparator(' - ')->append($pagetitle);
        }
    }

    protected function disabledAction() {
        // generic 'disabled' activities.
        $this->disableRender();
        // gtfo.
        $this->_redirect('/');
    }

    protected function disableRender() {
        // Disable view rendering so you don't need empty .phtml files for every
        // action that has no content.
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    protected function fileNotFound() {
        throw new Zend_Controller_Action_Exception('HTTP/1.0 404 Not Found', 404);
    }
}