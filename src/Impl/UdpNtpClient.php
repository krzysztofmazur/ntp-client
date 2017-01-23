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

use KrzysztofMazur\NTPClient\Exception\InvalidResponseException;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
final class UdpNtpClient extends AbstractNtpClient
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $server, int $port = 123)
    {
        parent::__construct($server, $port);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnixTime(): int
    {
        $socket = $this->connect();
        $this->sendInitPackage($socket);
        $response = $this->readResponse($socket, 48);
        $this->close($socket);

        return $this->extractTime($response) - self::SINCE_1900_TO_UNIX;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectionString(): string
    {
        return sprintf("udp://%s:%d", $this->server, $this->port);
    }

    /**
     * @param resource $socket
     */
    private function sendInitPackage($socket)
    {
        @fwrite($socket, chr(0x1B) . str_repeat(chr(0x00), 47));
    }

    /**
     * @param string $response
     * @return int
     * @throws InvalidResponseException
     */
    protected function extractTime(string $response): int
    {
        $unpacked = @unpack('N12', $response);
        if (!($unpacked && isset($unpacked[9]))) {
            throw new InvalidResponseException("Unable to unpack response");
        }

        return $unpacked[9];
    }
}
