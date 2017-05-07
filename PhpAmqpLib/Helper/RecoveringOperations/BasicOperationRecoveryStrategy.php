<?php

namespace PhpAmqpLib\Helper\RecoveringOperations;

use Exception;

/**
 * Basic operation recovery strategy. We just do X attempts and wait Y seconds, if an attempt was unsuccessful
 *
 * @package PhpAmqpLib\Helper\RepeatableOperations
 */
class BasicOperationRecoveryStrategy implements OperationRecoveryStrategyInterface
{
    /**
     * @var int Number of reconnection retries to do in total
     */
    protected $retries = 0;

    /**
     * @var int Number of milliseconds to wait between unsuccessful recovery attempts
     */
    protected $sleepMsecs;

    /**
     * BasicOperationRecoveryStrategy constructor.
     *
     * @param int $retries
     * @param int $sleepMSecs
     */
    public function __construct($retries = 3, $sleepMSecs = 3000)
    {
        $this->retries = $retries;
    }

    /**
     * @inheritdoc
     */
    public function operationSuccessful($operation)
    {
        $this->retries = 0;
    }

    /**
     * @inheritdoc
     */
    public function shouldTryToReconnect($operation, Exception $e)
    {
        $this->retries++;
        return $this->retries <= 3;
    }

    /**
     * @inheritdoc
     */
    public function waitForNextReconnectionAttempt($operation)
    {
        usleep($this->sleepMsecs * 1000);
    }
}
