<?php

namespace WooCommerceSerialNumbers\B8\Models\Utilities;

defined('ABSPATH') || exit;
/**
 * DateTime utility class.
 *
 * Extends PHP DateTime with WordPress-specific functionality.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class DateTime extends \DateTime
{
    /**
     * UTC offset in seconds, used only when a timezone is not set.
     *
     * @since 1.0.0
     * @var int
     */
    protected int $utc_offset = 0;
    /**
     * WordPress timezone.
     *
     * @since 1.0.0
     * @var \DateTimeZone|null
     */
    private static ?\DateTimeZone $wp_timezone = null;
    /**
     * UTC timezone.
     *
     * @since 1.0.0
     * @var \DateTimeZone|null
     */
    private static ?\DateTimeZone $utc_timezone = null;
    /**
     * Create a DateTime instance from a format.
     *
     * @since 1.0.0
     * @param string             $format   The format string.
     * @param string             $datetime The date/time string.
     * @param \DateTimeZone|null $timezone A DateTimeZone object.
     * @return self|false
     */
    #[\ReturnTypeWillChange]
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        if (null === $timezone) {
            $timezone = self::get_utc_timezone();
        }
        $dt = parent::createFromFormat($format, $datetime, $timezone);
        if (false === $dt) {
            return false;
        }
        return new self($dt->format('Y-m-d H:i:s'), $timezone);
    }
    /**
     * Create a DateTime instance from a timestamp.
     *
     * @since 1.0.0
     * @param int|float          $timestamp Unix timestamp.
     * @param \DateTimeZone|null $timezone  A DateTimeZone object.
     * @return self
     */
    public static function createFromTimestamp($timestamp, $timezone = null): self
    {
        if (null === $timezone) {
            $timezone = self::get_utc_timezone();
        }
        $dt = new self('@' . $timestamp);
        $dt->setTimezone($timezone);
        return $dt;
    }
    /**
     * Get the WordPress timezone.
     *
     * @since 1.0.0
     * @return \DateTimeZone
     */
    public static function get_wp_timezone(): \DateTimeZone
    {
        if (null === self::$wp_timezone) {
            $timezone_string = wp_timezone_string();
            self::$wp_timezone = new \DateTimeZone($timezone_string);
        }
        return self::$wp_timezone;
    }
    /**
     * Get the UTC timezone.
     *
     * @since 1.0.0
     * @return \DateTimeZone
     */
    public static function get_utc_timezone(): \DateTimeZone
    {
        if (null === self::$utc_timezone) {
            self::$utc_timezone = new \DateTimeZone('UTC');
        }
        return self::$utc_timezone;
    }
    /**
     * Output an ISO 8601 date string in the local (WordPress) timezone.
     *
     * @since 1.0.0
     * @return string
     */
    public function __toString(): string
    {
        return $this->format(DATE_ATOM);
    }
    /**
     * Set a fixed UTC offset instead of a timezone.
     *
     * @since 1.0.0
     * @param int $offset The offset in seconds.
     * @return void
     */
    public function set_utc_offset(int $offset): void
    {
        $this->utc_offset = $offset;
    }
    /**
     * Get the UTC offset if set, or the DateTime object's offset.
     *
     * @since 1.0.0
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function getOffset()
    {
        return $this->utc_offset ? $this->utc_offset : parent::getOffset();
    }
    /**
     * Set the timezone and clear any fixed UTC offset.
     *
     * @since 1.0.0
     * @param \DateTimeZone $timezone The timezone instance.
     * @return \DateTime
     */
    #[\ReturnTypeWillChange]
    public function setTimezone($timezone)
    {
        $this->utc_offset = 0;
        return parent::setTimezone($timezone);
    }
    /**
     * Get the timestamp with the WordPress timezone offset applied.
     *
     * @since 1.0.0
     * @return int
     */
    public function getOffsetTimestamp(): int
    {
        return $this->getTimestamp() + $this->getOffset();
    }
    /**
     * Format a date based on the offset timestamp.
     *
     * @since 1.0.0
     * @param string $format The date format.
     * @return string
     */
    public function date(string $format): string
    {
        return gmdate($format, $this->getOffsetTimestamp());
    }
    /**
     * Return a localised date in the WordPress timezone.
     *
     * @since 1.0.0
     * @param string $format The date format.
     * @return string
     */
    public function date_i18n(string $format = 'Y-m-d'): string
    {
        $date = wp_date($format, $this->getTimestamp());
        return false === $date ? '' : $date;
    }
}