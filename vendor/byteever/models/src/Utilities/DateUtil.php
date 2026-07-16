<?php

namespace WooCommerceSerialNumbers\B8\Models\Utilities;

defined('ABSPATH') || exit;
/**
 * Date utility class.
 *
 * Provides helper methods for date and time operations.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class DateUtil
{
    /**
     * Check if a value represents a valid date in a given format.
     *
     * @since 1.0.0
     * @param mixed  $date   The date value to check.
     * @param string $format The format to verify the date against.
     * @return bool Whether the value represents a valid date for the format.
     */
    public static function is_valid($date, string $format = 'Y-m-d H:i:s'): bool
    {
        // Already a date object, so treat it as valid.
        if ($date instanceof \DateTime) {
            return true;
        }
        // Numeric values are treated as timestamps.
        if (is_numeric($date)) {
            return true;
        }
        // Non-string values cannot be parsed.
        if (!is_string($date)) {
            return false;
        }
        $parsed = DateTime::createFromFormat($format, $date);
        return $parsed && $parsed->format($format) === $date;
    }
    /**
     * Parse the given value into a Unix timestamp.
     *
     * @since 1.0.0
     * @param mixed $value The value to cast.
     * @return int|null The timestamp, or null when the value cannot be parsed.
     */
    public static function strtotime($value): ?int
    {
        if (empty($value)) {
            return null;
        }
        // A date object exposes its own timestamp.
        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }
        // Numeric values are already timestamps.
        if (is_numeric($value)) {
            return (int) $value;
        }
        $timestamp = strtotime((string) $value);
        return false === $timestamp ? null : $timestamp;
    }
}