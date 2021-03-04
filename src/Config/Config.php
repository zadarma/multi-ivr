<?php

namespace MultiIvr\Config;

use MultiIvr\Exceptions\MultiIvrException;

/**
 * Class Config
 * @package MultiIvr\Config
 */
class Config
{
    /**
     * @var MenuItem[]
     */
    private $menuItems = [];
    /**
     * @var Start
     */
    private $start;

    /**
     * Config constructor.
     * @param Start $start
     */
    public function __construct(Start $start)
    {
        $this->start = $start;
    }

    /**
     * @param MenuItem $item
     * @return $this
     * @throws MultiIvrException
     */
    public function addMenuItem(MenuItem $item): self
    {
        $name = $item->getName();
        if (isset($this->menuItems[$item->getName()])) {
            throw new MultiIvrException("Menu item with '{$name}' name already exists");
        }
        $this->menuItems[$item->getName()] = $item;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMenuItem(string $name): bool
    {
        return isset($this->menuItems[$name]);
    }

    /**
     * @return Start
     */
    public function getStart(): Start
    {
        return $this->start;
    }

    /**
     * @return MenuItem[]
     */
    public function getMenuItems(): array
    {
        return array_values($this->menuItems);
    }

    /**
     * @param string $name
     * @return MenuItem
     */
    public function getMenuItemByName(string $name): MenuItem
    {
        return $this->menuItems[$name];
    }

    /**
     * @return $this
     * @throws MultiIvrException
     */
    public function validation(): self
    {
        $this->validationAction($this->start->getDefaultAction());
        foreach ($this->start->getRules() as $rule) {
            $this->validationAction($rule->getAction());
        }
        foreach ($this->getMenuItems() as $menuItem) {
            $this->validationAction($menuItem->getDefaultAction());
            foreach ($menuItem->getRules() as $rule) {
                $this->validationAction($rule->getAction());
            }
        }
        return $this;
    }

    /**
     * @param Action $action
     * @throws MultiIvrException
     */
    private function validationAction(Action $action): void
    {
        if ($action->getType() === Action::ACTION_MENU && !$this->hasMenuItem($action->getTarget())) {
            throw new MultiIvrException("Menu item with name '{$action->getTarget()}' not found.");
        }
    }
}
