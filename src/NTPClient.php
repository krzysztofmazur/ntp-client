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

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
interface NTPClient
{
    /**
     * @return int
     */
    public function getUnixTime(): int;

    /**
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function getTime(\DateTimeZone $timezone = null): \DateTime;
}
