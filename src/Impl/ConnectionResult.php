<?php

namespace KrzysztofMazur\NTPClient\Impl;

class ConnectionResult
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var string
     */
    private $errorCode;

    /**
     * @var string
     */
    private $errorString;

    /**
     * @param resource $socket
     * @param string $errorCode
     * @param string $errorString
     */
    public function __construct(resource $socket, string $errorCode, string $errorString)
    {
        $this->socket = $socket;
        $this->errorCode = $errorCode;
        $this->errorString = $errorString;
    }

    /**
     * @return resource
     */
    public function getSocket(): resource
    {
        return $this->socket;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorString(): string
    {
        return $this->errorString;
    }
}
