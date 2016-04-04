<?php

namespace Cms\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    const TREE_SPACER = '&nbsp;&nbsp;&nbsp;&nbsp;';

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        if (!$this->_isFrontEndAction()) {
            //Set the backend layout
            $this->viewBuilder()->layout('QoboAdminPanel.basic');
        }
    }

    /**
     * This funtion defines the frontend actions of the plugin which are
     * less than the backend actions.
     *
     * @param  string  $action given action|fallback to Request's action
     * @return boolean         Boolean flag
     */
    protected function _isFrontEndAction($action = null)
    {
        $frontendActions = ['display'];
        if (is_null($action)) {
            $action = $this->request->params['action'];
        }

        return in_array($action, $frontendActions);
    }

}
