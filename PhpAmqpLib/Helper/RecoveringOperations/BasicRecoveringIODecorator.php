<?php

namespace PhpAmqpLib\Helper\RecoveringOperations;

use Exception;
use PhpAmqpLib\Wire\IO\AbstractIO;

/**
 * This is a decorator around AbstractIO class, that retries operations on failure according to a
 * {@see OperationRecoveryStrategyInterface} you pass to it
 *
 * @package PhpAmqpLib\Helper\RepeatableOperations
 */
class BasicRecoveringIODecorator extends AbstractIO
{
    /**
     * @var AbstractIO
     */
    private $io;

    /**
     * @var OperationRecoveryStrategyInterface
     */
    private $repeatStrategy;

    /**
     * RepeatableIODecorator constructor.
     *
     * @param AbstractIO                         $io
     * @param OperationRecoveryStrategyInterface $repeatStrategy
     */
    public function __construct(AbstractIO $io, OperationRecoveryStrategyInterface $repeatStrategy)
    {
        $this->io = $io;
        $this->repeatStrategy = $repeatStrategy;
    }

    /**
     * @inheritdoc
     */
    public function read($n)
    {
        return $this->reconnectionImpl(__METHOD__, function () use ($n) {
            return $this->io->read($n);
        });
    }

    /**
     * @inheritdoc
     */
    public function write($data)
    {
        return $this->reconnectionImpl(__METHOD__, function () use ($data) {
            return $this->io->write($data);
        });
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        return $this->io->close();
    }

    /**
     * @inheritdoc
     */
    public function select($sec, $usec)
    {
        return $this->reconnectionImpl(__METHOD__, function () use ($sec, $usec) {
            return $this->io->select($sec, $usec);
        });
    }

    /**
     * @inheritdoc
     */
    public function connect()
    {
        return $this->reconnectionImpl(__METHOD__, function () {
            return $this->io->connect();
        });
    }

    /**
     * @inheritdoc
     */
    public function reconnect()
    {
        return $this->reconnectionImpl(__METHOD__, function () {
            return $this->io->reconnect();
        });
    }

    /**
     * @inheritdoc
     */
    public function getSocket()
    {
        return $this->io->getSocket();
    }

    /**
     * @param string   $operation
     * @param callable $callable
     *
     * @return mixed
     * @throws Exception
     */
    protected function reconnectionImpl($operation, $callable)
    {
        do {
            try {
                $data = $callable();
                $this->repeatStrategy->operationSuccessful($operation);
                return $data;
            } catch (Exception $e) {
                if ($this->repeatStrategy->shouldTryToReconnect($operation, $e)) {
                    $this->repeatStrategy->waitForNextReconnectionAttempt($operation);
                    continue;
                }
                throw $e;
            }
        } while (true);
    }
}
