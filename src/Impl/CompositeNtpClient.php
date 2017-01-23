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

use KrzysztofMazur\NTPClient\Exception\ConnectionException;
use KrzysztofMazur\NTPClient\Exception\UnableToConnectException;
use KrzysztofMazur\NTPClient\NtpClient;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
class CompositeNtpClient implements NtpClient
{
    /**
     * @var NtpClient[]
     */
    private $clients;

    /**
     * @param NtpClient[] $clients
     */
    public function __construct(array $clients)
    {
        if (count($clients) === 0) {
            throw new \InvalidArgumentException("At least one client should be provided");
        }
        foreach ($clients as $client) {
            if (!($client instanceof NtpClient)) {
                throw new \InvalidArgumentException("All clients should implements NtpClient interface.");
            }
        }
        $this->clients = $clients;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnixTime(): int
    {
        foreach ($this->clients as $client) {
            try {
                return $client->getUnixTime();
            } catch (ConnectionException $e) {
                //do nothing
            }
        }

        throw new UnableToConnectException("Unable connect to any server");
    }

    /**
     * {@inheritdoc}
     */
    public function getTime(\DateTimeZone $timezone = null): \DateTime
    {
        return DateTimeConverter::createFromUnixTimestamp($this->getUnixTime(), $timezone);
    }
}
