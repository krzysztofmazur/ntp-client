<?php
/*
 * This file is part of the NTPClient package.
 *
 * (c) Krzysztof Mazur <krz@ychu.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KrzysztofMazur\NTPClient;

use KrzysztofMazur\NTPClient\Exception\ConnectionRefusedException;
use KrzysztofMazur\NTPClient\Exception\ConnectionTimeoutException;
use KrzysztofMazur\NTPClient\Exception\UnableToConnectException;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
interface NtpClient
{
    /**
     * @return int
     * @throws ConnectionRefusedException
     * @throws ConnectionTimeoutException
     * @throws UnableToConnectException
     */
    public function getUnixTime(): int;

    /**
     * @param \DateTimeZone $timezone
     * @return \DateTime
     * @throws ConnectionRefusedException
     * @throws ConnectionTimeoutException
     * @throws UnableToConnectException
     */
    public function getTime(\DateTimeZone $timezone = null): \DateTime;
}
