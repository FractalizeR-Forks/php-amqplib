<?php

namespace PhpAmqpLib\Helper\RecoveringOperations;

use Exception;

/**
 * An interface for defining a recovery strategy for reconnecting to AMQP servers.
 *
 * @package PhpAmqpLib\Helper\RepeatableOperations
 */
interface OperationRecoveryStrategyInterface
{
    /**
     * This method is called after some operation was executed successfully. You may, for instance, reset retry counter
     * here
     *
     * @param string $operation Operation name. Actually, method name on the AbstractIO class, that was just
     *                          successfully executed
     *
     * @return void
     */
    public function operationSuccessful($operation);

    /**
     * This method is called, when some IO operation failed to execute correctly.
     *
     * @param string    $operation Operation name. Actually, method name on the AbstractIO class, that was just
     *                             successfully executed
     * @param Exception $e         Exception, that was raised during an operation execution
     *
     * @return bool True, if operation should be retried. False if $e should be rethrown.
     */
    public function shouldTryToReconnect($operation, Exception $e);

    /**
     * This method is called to make a delay between operation retries. You might want to do something useful while
     * waiting or just call sleep()
     *
     * @param string $operation
     *
     * @return void
     */
    public function waitForNextReconnectionAttempt($operation);
}
