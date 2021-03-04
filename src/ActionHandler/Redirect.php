<?php

namespace MultiIvr\ActionHandler;

use MultiIvr\Config\Action;
use Zadarma_API\Webhook\Request;

/**
 * Class Redirect
 * @package MultiIvr\ActionHandler
 */
class Redirect implements Handler
{
    /**
     * @var Action
     */
    private $action;

    /**
     * Redirect constructor.
     * @param Action $action
     */
    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    /**
     *
     */
    public function handle(): void
    {
        (new Request())->setRedirect($this->action->getTarget(), $this->action->getReturnTimeOut())->send();
    }
}
