<?php
/*
 * This file is part of the NTPClient package.
 *
 * (c) Krzysztof Mazur <krz@ychu.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KrzysztofMazur\NtpClient\Impl;

/**
 * @author Krzysztof Mazur <krz@ychu.pl>
 */
final class DateTimeConverter
{
    private function __construct()
    {
    }

    /**
     * @param int $timestamp
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public static function createFromUnixTimestamp(int $timestamp, \DateTimeZone $timezone = null): \DateTime
    {
        $dateTime = \DateTime::createFromFormat('U', $timestamp);
        /**
         * Timezone is ignored when time parameter contains UNIX timestamp
         * @link http://php.net/manual/en/datetime.createfromformat.php
         */
        if (!is_null($timezone)) {
            $dateTime->setTimezone($timezone);
        }

        return $dateTime;
    }
}
