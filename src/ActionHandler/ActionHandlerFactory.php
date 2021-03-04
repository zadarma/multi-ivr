<?php

namespace MultiIvr\ActionHandler;

use MultiIvr\Config\Action;
use MultiIvr\Config\Config;

/**
 * Class ActionHandlerFactory
 * @package MultiIvr
 */
class ActionHandlerFactory
{
    /**
     * @param Config $config
     * @param Action $action
     * @return Handler
     */
    public function create(Config $config, Action $action): Handler
    {
        if ($action->getType() === Action::ACTION_MENU) {
            return new GoToMenu($config->getMenuItemByName($action->getTarget()));
        }
        return new Redirect($action);
    }
}
