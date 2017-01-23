<?php
/*
 * This file is part of the NTPClient package.
 *
 * (c) Krzysztof Mazur <krz@ychu.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KrzysztofMazur\NTPClient\Impl;

use KrzysztofMazur\NTPClient\Exception\ConnectionRefusedException;
use KrzysztofMazur\NTPClient\Exception\ConnectionTimeoutException;
use KrzysztofMazur\NTPClient\Exception\InvalidResponseException;
use KrzysztofMazur\NTPClient\Exception\UnableToConnectException;
use KrzysztofMazur\NTPClient\NtpClient;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
abstract class AbstractNtpClient implements NtpClient
{
    /**
     * Value from RFC868
     */
    const SINCE_1900_TO_UNIX = 2208988800;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @param string $server
     * @param int $port
     */
    public function __construct(string $server, int $port)
    {
        if (empty($server)) {
            throw new \InvalidArgumentException(sprintf("Empty server address given: %s", $server));
        }
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf("Invalid port: %d", $port));
        }

        $this->server = $server;
        $this->port = $port;
        $this->timeout = 5;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        if ($timeout < 0) {
            throw new \InvalidArgumentException(sprintf("Negative timeout given: %d", $timeout));
        }
        $this->timeout = $timeout;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function getTime(\DateTimeZone $timezone = null): \DateTime
    {
        return DateTimeConverter::createFromUnixTimestamp($this->getUnixTime(), $timezone);
    }

    /**
     * @return string
     */
    protected abstract function getConnectionString(): string;

    /**
     * @return resource
     * @throws UnableToConnectException
     */
    protected function connect()
    {
        $socket = @stream_socket_client($this->getConnectionString(), $errorCode, $errorString, $this->timeout);
        if (!$socket) {
            throw new UnableToConnectException($errorString, $errorCode);
        }
        $this->checkMetadata($socket);

        return $socket;
    }

    /**
     * @param resource $socket
     * @throws ConnectionRefusedException
     * @throws ConnectionTimeoutException
     */
    protected function checkMetadata($socket)
    {
        $metadata = stream_get_meta_data($socket);
        if ($metadata['timed_out']) {
            throw new ConnectionTimeoutException("Connection timeout");
        }
    }

    /**
     * @param resource $socket
     * @param int $length
     * @return string
     * @throws InvalidResponseException
     */
    protected function readResponse($socket, int $length): string
    {
        socket_set_timeout($socket, $this->timeout);
        $response = @fread($socket, $length);
        if ($response === false || empty($response)) {
            throw new InvalidResponseException("Unable to read response, or response is empty");
        }

        return $response;
    }

    /**
     * @param resource $socket
     */
    protected function close($socket)
    {
        @fclose($socket);
    }
}
