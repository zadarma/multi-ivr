<?php

namespace MultiIvr;

use DateTime;
use MultiIvr\ActionHandler\ActionHandlerFactory;
use MultiIvr\Config\Action;
use MultiIvr\Config\Config;
use MultiIvr\Config\Rule;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;
use MultiIvr\Parser\ConfigParser;
use MultiIvr\Parser\Txt\Parser;
use Zadarma_API\Api;
use Zadarma_API\Webhook\AbstractNotify;
use Zadarma_API\Webhook\NotifyIvr;
use Zadarma_API\Webhook\NotifyStart;

/**
 * Class MultiIvr
 * @package MultiIvr
 */
class MultiIvr
{
    /**
     * @var ConfigParser|null
     */
    private $parser;

    /**
     * @var ActionHandlerFactory
     */
    private $factory;

    /**
     * MultiIvr constructor.
     * @param ConfigParser $parser
     * @param ActionHandlerFactory $factory
     */
    public function __construct(ConfigParser $parser, ActionHandlerFactory $factory)
    {
        $this->parser = $parser;
        $this->factory = $factory;
    }

    /**
     * @return $this
     */
    public static function default(): self
    {
        return new self(new Parser(), new ActionHandlerFactory());
    }

    /**
     * @param string $key
     * @param string $secret
     * @param string $configRawText
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function handle(string $key, string $secret, string $configRawText): void
    {
        $api = new Api($key, $secret);
        $config = $this->parser->parse($configRawText)->validation();
        /** @var NotifyStart $notify */
        if ($notify = $api->getWebhookEvent([AbstractNotify::EVENT_START])) {
            $this->handleStartEvent($config, $notify);
            return;
        }
        /** @var NotifyIvr $notify */
        if ($notify = $api->getWebhookEvent([AbstractNotify::EVENT_IVR])) {
            $waitDtmf = $notify->wait_dtmf;
            if ($waitDtmf->name === null && $waitDtmf->digits === null) {
                return;
            }
            $this->handleIvrEvent($config, $notify);
        }
    }

    /**
     * @param string $configRawText
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function validation(string $configRawText): void
    {
        $this->parser->parse($configRawText)->validation();
    }

    /**
     * @param Config $config
     * @param NotifyStart $notify
     */
    private function handleStartEvent(Config $config, NotifyStart $notify): void
    {
        $start = $config->getStart();
        $this->handleAction(
            $start->getRules(),
            $start->getDefaultAction(),
            $config,
            (string)$notify->caller_id,
            (string)$notify->called_did,
            DateTime::createFromFormat('Y-m-d H:i:s', $notify->call_start),
            null
        );
    }

    /**
     * @param Config $config
     * @param NotifyIvr $notify
     */
    private function handleIvrEvent(Config $config, NotifyIvr $notify): void
    {
        $menuItem = $config->getMenuItemByName($notify->wait_dtmf->name);
        $this->handleAction(
            $menuItem->getRules(),
            $menuItem->getDefaultAction(),
            $config,
            (string)$notify->caller_id,
            (string)$notify->called_did,
            DateTime::createFromFormat('Y-m-d H:i:s', $notify->call_start),
            $notify->wait_dtmf->digits
        );
    }

    /**
     * @param Rule[] $rulesAr
     * @param Action $defaultAction
     * @param Config $config
     * @param int $callerId
     * @param int $calledDid
     * @param DateTime $callStart
     * @param string|null $button
     */
    private function handleAction(
        array $rulesAr,
        Action $defaultAction,
        Config $config,
        int $callerId,
        int $calledDid,
        DateTime $callStart,
        ?string $button
    ): void {
        $usedFilter = null;
        foreach ($rulesAr as $rule) {
            $isUse = $rule->isUse($callerId, $calledDid, $callStart, $button);
            if ($isUse) {
                $usedFilter = $rule;
                break;
            }
        }
        $action = $usedFilter !== null ? $usedFilter->getAction() : $defaultAction;
        $this->factory->create($config, $action)->handle();
    }
}
