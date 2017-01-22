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
use KrzysztofMazur\NTPClient\NTPClient;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
class NTPClientImpl implements NTPClient
{
    /**
     * Protocols
     */
    const USE_UDP = 0;
    const USE_TCP = 1;

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
     * @var int
     */
    private $protocol;

    /**
     * @param string $server
     * @param int $port
     * @param int $protocol
     */
    public function __construct(string $server, int $port, int $protocol = self::USE_UDP)
    {
        if (empty($server)) {
            throw new \InvalidArgumentException(sprintf("Empty server address given: %s", $server));
        }
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf("Invalid port: %d", $port));
        }
        if (!in_array($protocol, [self::USE_UDP, self::USE_TCP])) {
            throw new \InvalidArgumentException("Invalid protocol parameter");
        }

        $this->server = $server;
        $this->port = $port;
        $this->protocol = $protocol;
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
        return \DateTime::createFromFormat('U', $this->getUnixTime(), $timezone);
    }

    /**
     * @return int
     */
    public function getUnixTime(): int
    {
        $value = $this->protocol === self::USE_UDP ? $this->getUnixTimeUDP() : $this->getUnixTimeTCP();

        return $value - self::SINCE_1900_TO_UNIX;
    }

    /**
     * @return int
     */
    private function getUnixTimeTCP(): int
    {
        $socket = $this->connect();
        $response = $this->readResponse($socket, 4);
        $this->close($socket);

        return $this->bin2dec($response);
    }

    /**
     * @return int
     */
    private function getUnixTimeUDP(): int
    {
        $socket = $this->connect();
        $this->sendInitPackage($socket);
        $response = $this->readResponse($socket, 48);
        $this->close($socket);

        return $this->extractTime($response);
    }

    /**
     * @return string
     */
    private function getConnectionString(): string
    {
        return sprintf("%s://%s:%s", $this->protocol === self::USE_UDP ? 'udp' : 'tcp', $this->server, $this->port);
    }

    /**
     * @return resource
     * @throws UnableToConnectException
     */
    private function connect()
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
    private function checkMetadata($socket)
    {
        $metadata = stream_get_meta_data($socket);
        if ($metadata['timed_out']) {
            throw new ConnectionTimeoutException("Connection timeout");
        }
    }

    /**
     * @param resource $socket
     */
    private function sendInitPackage($socket)
    {
        fwrite($socket, chr(0x1B) . str_repeat(chr(0x00), 47));
    }

    /**
     * @param resource $socket
     * @param int $length
     * @return string
     * @throws InvalidResponseException
     */
    private function readResponse($socket, int $length)
    {
        $response = fread($socket, $length);
        if ($response === false || $response === '') {
            throw new InvalidResponseException("Unable to read response, or response is empty");
        }

        return $response;
    }

    /**
     * @param resource $socket
     */
    private function close($socket)
    {
        @fclose($socket);
    }

    /**
     * @param string $response
     * @return int
     * @throws InvalidResponseException
     */
    private function extractTime(string $response): int
    {
        $unpacked = @unpack('N12', $response);
        if (!($unpacked && isset($unpacked[9]))) {
            throw new InvalidResponseException("Unable to unpack response");
        }

        return $unpacked[9];
    }

    /**
     * @param string $value
     * @return int
     */
    private function bin2dec(string $value): int
    {
        return hexdec(bin2hex($value));
    }
}
