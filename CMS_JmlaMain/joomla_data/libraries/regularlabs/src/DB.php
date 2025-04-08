<?php

/**
 * @package         Regular Labs Library
 * @version         25.3.16992
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */
namespace RegularLabs\Library;

defined('_JEXEC') or die;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\Database\DatabaseDriver as JDatabaseDriver;
use Joomla\Database\DatabaseQuery as JDatabaseQuery;
use Joomla\Database\QueryInterface as JQueryInterface;
class DB
{
    static $tables = [];
    public static function addArticleIsPublishedFilters(JQueryInterface &$query, string $prefix = 'a'): void
    {
        $filters = self::getArticleIsPublishedFilters($prefix);
        $query->where($filters);
    }
    public static function combine(array $conditions = [], string $glue = 'OR'): string
    {
        if (empty($conditions)) {
            return '';
        }
        if (!is_array($conditions)) {
            return (string) $conditions;
        }
        if (count($conditions) < 2) {
            return reset($conditions);
        }
        $glue = strtoupper($glue) == 'AND' ? 'AND' : 'OR';
        return '(' . implode(' ' . $glue . ' ', $conditions) . ')';
    }
    /**
     * Creat a query dump string
     */
    public static function dump(string|JQueryInterface $query, int $caller_offset = 0, string $class_prefix = '', int $caller_limit = 5): void
    {
        $string = "\n" . (string) $query;
        $string = str_replace('#__', JFactory::getDbo()->getPrefix(), $string);
        $bounded = $query->getBounded();
        foreach ($bounded as $key => $obj) {
            $string = str_replace($key, self::quote($obj->value, \false), $string);
        }
        \RegularLabs\Library\Protect::protectByRegex($string, ' IN \(.*?\)');
        \RegularLabs\Library\Protect::protectByRegex($string, ' FIELD\(.*?\)');
        $string = preg_replace('#(\n[A-Z][A-Z ]+) #', "\n\\1\n       ", $string);
        $string = str_replace(' LIMIT ', "\n\nLIMIT ", $string);
        $string = str_replace(' ON ', "\n    ON ", $string);
        $string = str_replace(' OR ', "\n    OR ", $string);
        $string = str_replace(' AND ', "\n   AND ", $string);
        $string = str_replace('`,', "`,\n       ", $string);
        \RegularLabs\Library\Protect::unprotect($string);
        echo "\n<pre>==============================================================================\n";
        echo self::getQueryComment($class_prefix, $caller_limit, $caller_offset) . "\n";
        echo "-----------------------------------------------------------------------------------\n";
        echo trim($string);
        echo "\n===================================================================================</pre>\n";
    }
    public static function escape(string $text, bool $extra = \false): string
    {
        return JFactory::getDbo()->escape($text, $extra);
    }
    public static function get(): JDatabaseDriver
    {
        return JFactory::getDbo();
    }
    public static function getArticleIsPublishedFilters(string $prefix = 'a'): string
    {
        $nowDate = self::getNowDate();
        $nullDate = self::getNullDate();
        $wheres = [];
        $wheres[] = self::is($prefix . '.state', 1);
        $wheres[] = self::combine([self::is($prefix . '.publish_up', 'NULL'), self::is($prefix . '.publish_up', '<=' . $nowDate)], 'OR');
        $wheres[] = self::combine([self::is($prefix . '.publish_down', 'NULL'), self::is($prefix . '.publish_down', $nullDate), self::is($prefix . '.publish_down', '>' . $nowDate)], 'OR');
        return self::combine($wheres, 'AND');
    }
    public static function getIncludesExcludes(array|string $values, bool $remove_exclude_operators = \true): array
    {
        $includes = [];
        $excludes = [];
        $values = \RegularLabs\Library\ArrayHelper::toArray($values);
        if (empty($values)) {
            return [$includes, $excludes];
        }
        foreach ($values as $value) {
            if ($value == '') {
                $value = '!*';
            }
            if ($value == '!') {
                $value = '+';
            }
            if (self::isExclude($value)) {
                $excludes[] = $remove_exclude_operators ? self::removeOperator($value) : $value;
                continue;
            }
            $includes[] = $value;
        }
        return [$includes, $excludes];
    }
    public static function getNowDate(): string
    {
        return JFactory::getDate()->toSql();
    }
    public static function getNullDate(): string
    {
        return JFactory::getDbo()->getNullDate();
    }
    public static function getOperator(array|string|null $value, string $default = '='): string
    {
        if ($value === null || $value === '' || is_array($value) && empty($value)) {
            return $default;
        }
        if (is_array($value)) {
            $value = array_values($value);
            return self::getOperator(reset($value), $default);
        }
        $regex = '^' . \RegularLabs\Library\RegEx::quote(self::getOperators(), 'operator');
        if (!\RegularLabs\Library\RegEx::match($regex, $value, $parts)) {
            return $default;
        }
        $operator = $parts['operator'];
        return match ($operator) {
            '!', '<>', '!NOT!' => '!=',
            '==' => '=',
            default => $operator,
        };
    }
    public static function getOperators(): array
    {
        return ['!NOT!', '!=', '!', '<>', '<=', '<', '>=', '>', '=', '=='];
    }
    public static function getQuery(): JDatabaseQuery
    {
        return JFactory::getDbo()->getQuery(\true);
    }
    public static function getQueryComment(string $class_prefix = '', int $caller_limit = 5, int $caller_offset = 0): string
    {
        $callers = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, $caller_offset + $caller_limit);
        for ($i = 1; $i <= $caller_offset + 2; $i++) {
            array_shift($callers);
        }
        $callers = array_reverse($callers);
        $lines = [JUri::getInstance()->toString()];
        foreach ($callers as $caller) {
            $lines[] = '[' . str_pad($caller['line'] ?? '', 3, ' ', \STR_PAD_LEFT) . '] ' . str_replace('\\', '.', trim(substr($caller['class'] ?? '', strlen($class_prefix)), '\\')) . '.' . $caller['function'];
        }
        return implode("\n", $lines);
    }
    public static function getTableColumns(string $table, bool $typeOnly = \true): array
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        return $cache->set(JFactory::getDbo()->getTableColumns($table, $typeOnly));
    }
    public static function in(array|string $keys, array|string $values, array|object $options = [], bool $quote_key = \true): string
    {
        $options = (object) \RegularLabs\Library\ArrayHelper::toArray($options);
        $glue = $options->glue ?? 'OR';
        if (is_array($keys)) {
            $wheres = [];
            foreach ($keys as $single_key) {
                $wheres[] = self::in($single_key, $values, $options, $quote_key);
            }
            return self::combine($wheres, $glue);
        }
        if ($values == '') {
            $values = [''];
        }
        $operator = self::getOperator($values);
        $db_key = $keys;
        if ($quote_key && !str_starts_with($db_key, '`')) {
            $db_key = self::quoteName($db_key);
        }
        $values = \RegularLabs\Library\ArrayHelper::toArray($values);
        if (empty($values)) {
            return $operator == '!=' ? 'TRUE' : 'FALSE';
        }
        if (count($values) == 1) {
            $value = reset($values);
            $value = self::removeOperator($value);
            $value = self::prepareValue($value, $options);
            if ($value === 'NULL') {
                $operator = $operator == '!=' ? 'IS NOT' : 'IS';
            }
            return $db_key . ' ' . $operator . ' ' . $value;
        }
        $operator = $operator == '!=' ? 'NOT IN' : 'IN';
        if ($glue == 'OR') {
            $values = self::removeOperator($values);
            $values = self::prepareValue($values, $options);
            return $db_key . ' ' . $operator . ' (' . implode(',', $values) . ')';
        }
        $wheres = [];
        foreach ($values as $value) {
            $wheres[] = self::in($keys, $value, $options, $quote_key);
        }
        return self::combine($wheres, $glue);
    }
    public static function is(array|string $keys, array|string $values, array|object $options = [], bool $quote_key = \true): string
    {
        $options = (object) \RegularLabs\Library\ArrayHelper::toArray($options);
        $glue = $options->glue ?? 'OR';
        $handle_wildcards = $options->handle_wildcards ?? \true;
        if (is_array($keys) && $glue == 'OR') {
            $wheres = [];
            foreach ($keys as $single_key) {
                $wheres[] = self::is($single_key, $values, $options, $quote_key);
            }
            return self::combine($wheres, $glue);
        }
        if (is_array($keys) && $glue == 'AND') {
            $options->glue = 'OR';
            $wheres = [];
            foreach ($values as $single_values) {
                $wheres[] = self::is($keys, $single_values, $options, $quote_key);
            }
            return self::combine($wheres, $glue);
        }
        if (!is_array($values) && $handle_wildcards && str_contains($values, '*')) {
            return self::like($keys, $values, $options, $quote_key);
        }
        if (!is_array($values)) {
            return self::in($keys, $values, $options, $quote_key);
        }
        $includes = [];
        $excludes = [];
        $wheres = [];
        foreach ($values as $value) {
            if ($handle_wildcards && str_contains($value, '*')) {
                $wheres[] = self::is($keys, $value, $options, $quote_key);
                continue;
            }
            if (self::isExclude($value)) {
                $excludes[] = $value;
                continue;
            }
            $includes[] = $value;
        }
        if (!empty($includes)) {
            $wheres[] = self::in($keys, $includes, $options, $quote_key);
        }
        if (!empty($excludes)) {
            $wheres[] = self::in($keys, $excludes, $options, $quote_key);
        }
        if (empty($wheres)) {
            return 'FALSE';
        }
        if (count($wheres) == 1) {
            return reset($wheres);
        }
        return self::combine($wheres, $glue);
    }
    public static function isExclude(?string $string): bool
    {
        if (empty($string)) {
            return \false;
        }
        return in_array(self::getOperator($string), ['!=', '<>'], \true);
    }
    public static function isNot(array|string $key, array|string $value, array|object $options = []): string
    {
        if (is_array($key)) {
            $wheres = [];
            foreach ($key as $single_key) {
                $wheres[] = self::isNot($single_key, $value, $options);
            }
            return self::combine($wheres, 'AND');
        }
        $values = $value;
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $i => $value) {
            $operator = self::isExclude($value) ? '=' : '!=';
            $values[$i] = $operator . self::removeOperator($value);
        }
        return self::is($key, $values, $options);
    }
    public static function isNotNull(array|string $key): string
    {
        if (is_array($key)) {
            $wheres = [];
            foreach ($key as $single_key) {
                $wheres[] = self::isNotNull($single_key);
            }
            return self::combine($wheres, 'AND');
        }
        return self::isNot($key, 'NULL');
    }
    public static function isNull(array|string $key): string
    {
        if (is_array($key)) {
            $wheres = [];
            foreach ($key as $single_key) {
                $wheres[] = self::isNull($single_key);
            }
            return self::combine($wheres, 'AND');
        }
        return self::is($key, 'NULL');
    }
    public static function like(string $key, array|string $value, array|object $options = [], $quote_key = \true): string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$key, $value, $options, $quote_key], '', '', 1);
        if (!is_null($array)) {
            return $array;
        }
        $operator = self::getOperator($value);
        $db_key = $key;
        if ($quote_key && !str_starts_with($db_key, '`')) {
            $db_key = self::quoteName($db_key);
        }
        if ($value == '*') {
            return $db_key . ' ' . ($operator == '!=' ? 'IS NULL' : 'IS NOT NULL');
        }
        $db_key = 'LOWER(' . $db_key . ')';
        $operator = $operator == '!=' ? 'NOT LIKE' : 'LIKE';
        $options = (object) \RegularLabs\Library\ArrayHelper::toArray($options);
        $value = self::removeOperator($value);
        $value = self::prepareValue($value, $options);
        $value = str_replace(['*', '_'], ['%', '\_'], $value);
        if (!str_contains($value, '%')) {
            $value = 'LOWER(' . $value . ')';
        }
        return $db_key . ' ' . $operator . ' ' . $value;
    }
    /**
     * Create an NOT IN statement
     * Reverts to a simple equals statement if array just has 1 value
     */
    public static function notIn(string|array $keys, string|array $values, array|object $options = [], bool $quote_key = \true): string
    {
        if (is_array($values) && empty($values)) {
            return 'TRUE';
        }
        if (is_array($values) && count($values) > 0) {
            $values[0] = '!' . $values[0];
        }
        return self::in($keys, $values, $options, $quote_key);
    }
    public static function prepareValue(string|array|object $value, array|object $options = []): string|array
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$value, $options]);
        if (!is_null($array)) {
            return $array;
        }
        if (!is_array($value) && $value === 'NULL') {
            return $value;
        }
        $options = (object) \RegularLabs\Library\ArrayHelper::toArray($options);
        $handle_now = $options->handle_now ?? \true;
        $dates = ['now', 'now()', 'date()', 'jfactory::getdate()'];
        if ($handle_now && !is_array($value) && in_array(strtolower($value), $dates, \true)) {
            return 'NOW()';
        }
        if ((empty($options->quote) || !$options->quote) && (is_int($value) || ctype_digit($value))) {
            return $value;
        }
        $value = self::quote($value);
        return $value;
    }
    public static function quote(array|string $text, bool $escape = \true): array|string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$text, $escape]);
        if (!is_null($array)) {
            return $array;
        }
        if (is_null($text)) {
            return 'NULL';
        }
        return JFactory::getDbo()->quote($text, $escape);
    }
    public static function quoteName(array|string $name, array|string|null $as = null): array|string
    {
        return JFactory::getDbo()->quoteName($name, $as);
    }
    public static function removeOperator(string|array|null $string)
    {
        if ($string === null || $string === '' || is_array($string) && empty($string)) {
            return $string;
        }
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        $regex = '^' . \RegularLabs\Library\RegEx::quote(self::getOperators(), 'operator');
        return \RegularLabs\Library\RegEx::replace($regex, '', $string);
    }
    public static function tableExists(string $table): bool
    {
        if (isset(self::$tables[$table])) {
            return self::$tables[$table];
        }
        $db = JFactory::getDbo();
        if (str_starts_with($table, '#__')) {
            $table = $db->getPrefix() . substr($table, 3);
        }
        if (!str_starts_with($table, $db->getPrefix())) {
            $table = $db->getPrefix() . $table;
        }
        $query = 'SHOW TABLES LIKE ' . $db->quote($table);
        $db->setQuery($query);
        $result = $db->loadResult();
        self::$tables[$table] = !empty($result);
        return self::$tables[$table];
    }
}
