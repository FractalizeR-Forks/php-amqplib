<?php

namespace PhpAmqpLib\Helper\RecoveringOperations;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Wire\IO\StreamIO;

/**
 * This is a plain AMQPConnection (AMQPStreamConnection, actually) with a capability to retry operations on failure
 *
 * @package PhpAmqpLib\Helper\RecoveringOperations
 */
class BasicRecoveringAMQPStreamConnection extends AbstractConnection
{
    /**
     * @param OperationRecoveryStrategyInterface $repeatStrategy Strategy of operation recovery. {@see
     *                                                           OperationRecoveryStrategyInterface} for details
     * @param string                             $host
     * @param string                             $port
     * @param string                             $user
     * @param string                             $password
     * @param string                             $vhost
     * @param bool                               $insist
     * @param string                             $login_method
     * @param null                               $login_response
     * @param string                             $locale
     * @param float                              $connection_timeout
     * @param float                              $read_write_timeout
     * @param null                               $context
     * @param bool                               $keepalive
     * @param int                                $heartbeat
     */
    public function __construct(
        $repeatStrategy,
        $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $login_method = 'AMQPLAIN',
        $login_response = null,
        $locale = 'en_US',
        $connection_timeout = 3.0,
        $read_write_timeout = 3.0,
        $context = null,
        $keepalive = false,
        $heartbeat = 0
    ) {
        $io = new StreamIO(
            $host,
            $port,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat
        );

        $ioAdapter = new BasicRecoveringIODecorator($io, $repeatStrategy);

        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $ioAdapter,
            $heartbeat
        );

        // save the params for the use of __clone, this will overwrite the parent
        $this->construct_params = func_get_args();
    }
}
