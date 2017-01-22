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

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
final class TcpNtpClient extends AbstractNtpClient
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $server, int $port = 37)
    {
        parent::__construct($server, $port);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnixTime(): int
    {
        $socket = $this->connect();
        $response = $this->readResponse($socket, 4);
        $this->close($socket);

        return $this->bin2dec($response) - self::SINCE_1900_TO_UNIX;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectionString(): string
    {
        return sprintf("tcp://%s:%d", $this->server, $this->port);
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
