<?php

namespace MultiIvr\Config;

/**
 * Class Start
 * @package MultiIvr\Config\Start
 */
class Start
{
    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * @var Action
     */
    private $defaultAction;

    /**
     * Start constructor.
     * @param Action $defaultAction
     */
    public function __construct(Action $defaultAction)
    {
        $this->defaultAction = $defaultAction;
    }

    /**
     * @return Action
     */
    public function getDefaultAction(): Action
    {
        return $this->defaultAction;
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function addRule(Rule $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
