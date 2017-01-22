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

use KrzysztofMazur\NTPClient\Exception\UnableToConnectException;
use KrzysztofMazur\NTPClient\NTPClient;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
class NTPClientImpl implements NTPClient
{
    /**
     * Value from RFC868
     */
    const SINCE_1900_TO_UNIX = 2208988800;

    /**
     * @var string
     */
    private $server;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @param string $server
     * @param int $port
     * @param int $timeout
     */
    public function __construct(string $server, int $port, int $timeout = 5)
    {
        if (empty($server)) {
            throw new \InvalidArgumentException(sprintf("Empty server address given: %s", $server));
        }
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf("Invalid port: %d", $port));
        }
        if ($timeout < 0) {
            throw new \InvalidArgumentException(sprintf("Negative timeout given: %d", $timeout));
        }

        $this->server = sprintf("udp://%s", $server);
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getUnixTime(): int
    {
        $socket = $this->connect();
        $this->sendInitPackage($socket);
        $response = $this->readResponse($socket);
        $this->close($socket);

        return $this->extractTime($response);
    }

    /**
     * @return resource
     * @throws UnableToConnectException
     */
    private function connect()
    {
        $sock = @fsockopen($this->server, $this->port, $errorCode, $errorString, $this->timeout);
        if (!$sock) {
            throw new UnableToConnectException($errorString, $errorCode);
        }

        return $sock;
    }

    /**
     * @param resource $sock
     */
    private function sendInitPackage($sock)
    {
        $result = fwrite($sock, chr(0x1B) . str_repeat(chr(0x00), 47));
        if ($result === false) {
            //TODO: throw exception ??
        }
    }

    /**
     * @param resource $socket
     * @return string
     */
    private function readResponse($socket)
    {
        $response = fread($socket, 48);
        if ($response === false) {
            //TODO: throw exception
        }

        return $response;
    }

    /**
     * @param resource $socket
     */
    private function close($socket)
    {
        $result = fclose($socket);
        if ($result === false) {
            //TODO: throw exception
        }
    }

    /**
     * @param string $response
     * @return int
     */
    private function extractTime(string $response): int
    {
        $unpacked = unpack('N12', $response);
        if (!isset($unpacked[9])) {
            //TODO: throw exception
        }

        return $unpacked[9] - self::SINCE_1900_TO_UNIX;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function getTime(\DateTimeZone $timezone = null): \DateTime
    {
        return \DateTime::createFromFormat('U', $this->getUnixTime(), $timezone);
    }
}
