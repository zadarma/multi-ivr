<?php

namespace MultiIvr\ActionHandler;

use MultiIvr\Config\MenuItem;
use Zadarma_API\Webhook\Request;

/**
 * Class GoToMenu
 * @package MultiIvr\ActionHandler
 */
class GoToMenu implements Handler
{

    /**
     * @var MenuItem
     */
    private $menuItem;

    /**
     * GoToMenu constructor.
     * @param MenuItem $menuItem
     */
    public function __construct(MenuItem $menuItem)
    {
        $this->menuItem = $menuItem;
    }

    /**
     *
     */
    public function handle(): void
    {
        (new Request())
            ->setIvrPlay($this->menuItem->getPlayFileId())
            ->setWaitDtmf(
                $this->menuItem->getTimeOut(),
                $this->menuItem->getAttempts(),
                $this->menuItem->getMaxSymbols(),
                $this->menuItem->getName(),
                $this->menuItem->getDefaultAction()->getTarget()
            )
            ->send();
    }
}
