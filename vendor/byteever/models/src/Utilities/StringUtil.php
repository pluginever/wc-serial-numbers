<?php

namespace WooCommerceSerialNumbers\B8\Models\Utilities;

defined('ABSPATH') || exit;
/**
 * String utility class.
 *
 * Provides string manipulation and formatting methods.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class StringUtil
{
    /**
     * Convert a string to snake_case.
     *
     * @since 1.0.0
     * @param string $text The string to convert.
     * @return string
     */
    public static function snake(string $text): string
    {
        $text = (string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $text);
        return strtolower(str_replace(array('-', ' '), '_', $text));
    }
    /**
     * Join multiple strings into a normalized string with a custom separator.
     *
     * @since 1.0.0
     * @param string|string[] $parts     The strings to normalize and join.
     * @param string          $separator The separator to use between parts.
     * @return string The normalized and joined string.
     */
    public static function join($parts, string $separator = '_'): string
    {
        $cleaned = array();
        foreach (wp_parse_list($parts) as $part) {
            $part = (string) $part;
            $part = (string) preg_replace('/[^A-Za-z0-9]/', $separator, $part);
            $part = (string) preg_replace('/[' . preg_quote($separator, '/') . ']+/', $separator, $part);
            $part = trim($part, $separator);
            $cleaned[] = strtolower($part);
        }
        return implode($separator, array_filter($cleaned));
    }
    /**
     * Singularize a string.
     *
     * @since 1.0.0
     * @param string $subject The string to singularize.
     * @return string
     */
    public static function singularize(string $subject): string
    {
        return (string) preg_replace(array('/ies$/', '/ves$/', '/(?!s)es$/', '/s$/'), array('y', 'f', '', ''), $subject);
    }
    /**
     * Pluralize a string.
     *
     * @since 1.0.0
     * @param string $subject The string to pluralize.
     * @return string
     */
    public static function pluralize(string $subject): string
    {
        return (string) preg_replace(array('/y$/', '/f$/', '/fe$/', '/o$/', '/$/'), array('ies', 'ves', 'ves', 'oes', 's'), self::singularize($subject));
    }
    /**
     * Limit a string to a maximum length with an optional ending.
     *
     * @since 1.0.0
     * @param string             $value      The string to limit.
     * @param array<int, string> $parameters The [max_length, ending] parameters.
     * @return string
     */
    public static function limit_string_value(string $value, array $parameters): string
    {
        $limit = isset($parameters[0]) ? (int) $parameters[0] : 100;
        $ending = isset($parameters[1]) ? $parameters[1] : '';
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit) . $ending;
    }
    /**
     * Pad a string to a specified length.
     *
     * @since 1.0.0
     * @param string             $value      The string to pad.
     * @param array<int, string> $parameters The [length, pad_string, pad_type] parameters.
     * @return string
     */
    public static function pad_string_value(string $value, array $parameters): string
    {
        $length = isset($parameters[0]) ? (int) $parameters[0] : 0;
        $pad_string = isset($parameters[1]) ? $parameters[1] : ' ';
        $pad_type = isset($parameters[2]) ? $parameters[2] : 'right';
        switch ($pad_type) {
            case 'left':
                $type = STR_PAD_LEFT;
                break;
            case 'both':
                $type = STR_PAD_BOTH;
                break;
            default:
                $type = STR_PAD_RIGHT;
                break;
        }
        return str_pad($value, $length, $pad_string, $type);
    }
}