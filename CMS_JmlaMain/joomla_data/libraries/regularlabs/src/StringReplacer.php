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
/**
 * Class StringReplacer
 * Handles string replacement operations with the ability to exclude certain parts of the string
 */
class StringReplacer
{
    private bool $enable_clean = \true;
    private array $parts = [];
    public function __construct(string $string = '')
    {
        $this->set($string ?? '');
    }
    public function clean(): self
    {
        $this->enable_clean = \true;
        $this->cleanParts();
        return $this;
    }
    public function contains(string $string): bool
    {
        return str_contains($this->toString(), $string);
    }
    public function disableCleaning(): self
    {
        $this->enable_clean = \false;
        return $this;
    }
    public function excludeExceptHtmlTags(array $tags = ['*']): self
    {
        $regex = $this->getHtmlTagsRegex();
        $this->excludeExceptRegex($regex);
        if (in_array('*', $tags)) {
            return $this;
        }
        return $this->excludeHtmlTags($tags);
    }
    public function excludeExceptRegex(string $regex): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => &$string) {
            if ($this->shouldSkip($key, $string)) {
                $all_parts[] = $string;
                continue;
            }
            $parts = \RegularLabs\Library\RegEx::split($regex, $string);
            $parts = ['', ...$parts, ''];
            array_push($all_parts, ...$parts);
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function excludeExceptStrings(array $strings = []): self
    {
        $regex = \RegularLabs\Library\RegEx::quote($strings);
        return $this->excludeExceptRegex($regex);
    }
    public function excludeForm(array $form_classes = []): self
    {
        // Exclude the complete adminForm (to prevent replacements messing stuff up when editing articles and such)
        $regexes = $this->getFormRegexes($form_classes);
        return $this->excludeRegexBetween($regexes->start, $regexes->end, \true);
    }
    public function excludeHtmlTags(array $except_tags = []): self
    {
        $regex = $this->getHtmlTagsRegex();
        if (in_array('*', $except_tags)) {
            return $this;
        }
        $this->disableCleaning();
        $this->excludeRegex($regex);
        if (empty($except_tags)) {
            $this->clean();
            return $this;
        }
        $this->includeHtmlTags($except_tags);
        $this->clean();
        return $this;
    }
    public function excludeOutsideStrings(string $start, string $end, $exclude_strings = \false): self
    {
        if ($start == '' && $end == '') {
            return $this;
        }
        $start = $start ?: '^';
        $end = $end ?: '$';
        $regex = $exclude_strings ? '()(' . \RegularLabs\Library\RegEx::quote($start) . ')(.*?)(' . \RegularLabs\Library\RegEx::quote($end) . ')()' : '(' . \RegularLabs\Library\RegEx::quote($start) . '.*?' . \RegularLabs\Library\RegEx::quote($end) . ')';
        return $this->excludeExceptRegex($regex);
    }
    public function excludeRegex(string $regex): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => &$string) {
            if ($this->shouldSkip($key, $string)) {
                $all_parts[] = $string;
                continue;
            }
            $parts = \RegularLabs\Library\RegEx::split($regex, $string);
            if (empty($parts)) {
                $all_parts[] = $string;
                continue;
            }
            array_push($all_parts, ...$parts);
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function excludeRegexBetween(string $start, string $end, $exclude_matches = \false): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => &$string) {
            if ($this->shouldSkip($key, $string)) {
                $all_parts[] = $string;
                continue;
            }
            $start_parts = \RegularLabs\Library\RegEx::split($start, $string);
            if (count($start_parts) < 2) {
                $all_parts[] = $string;
                continue;
            }
            $first_part = array_shift($start_parts);
            if (!$exclude_matches) {
                $first_part .= array_shift($start_parts);
            }
            $search_part = implode($start_parts);
            $end_parts = (new \RegularLabs\Library\StringReplacer($search_part))->excludeRegex($end)->getParts();
            if (count($end_parts) < 2) {
                $all_parts[] = $string;
                continue;
            }
            $protected_part = array_shift($end_parts);
            if ($exclude_matches) {
                $protected_part .= array_shift($end_parts);
            }
            $last_part = implode($end_parts);
            array_push($all_parts, $first_part, $protected_part, $last_part);
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function excludeRegexNested(string $regex_outer, string $regex_inner): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => $string) {
            if (trim($string) == '' || $this->rowIsExcluded($key)) {
                $all_parts[] = $string;
                continue;
            }
            if (!\RegularLabs\Library\RegEx::match($regex_outer, $string) || !\RegularLabs\Library\RegEx::match($regex_inner, $string)) {
                $all_parts[] = $string;
                continue;
            }
            $nested = (new \RegularLabs\Library\StringReplacer($string))->excludeRegex($regex_inner);
            array_push($all_parts, ...$nested->getParts());
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function excludeStrings(array $strings = []): self
    {
        $regex = \RegularLabs\Library\RegEx::quote($strings);
        return $this->excludeRegex($regex);
    }
    public function getHtmlTagsRegex(): string
    {
        return '(</?[a-zA-Z][^>]*>)';
    }
    public function getParts(): array
    {
        return $this->parts;
    }
    public function includeRegex(string $regex): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => $string) {
            if (trim($string) == '' || !$this->rowIsExcluded($key)) {
                $all_parts[] = $string;
                continue;
            }
            if (!\RegularLabs\Library\RegEx::match($regex, $string)) {
                $all_parts[] = $string;
                continue;
            }
            $parts = \RegularLabs\Library\RegEx::split($regex, $string);
            array_push($all_parts, ...$parts);
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function includeRegexNested(string $regex_outer, string $regex_inner): self
    {
        $all_parts = [];
        foreach ($this->parts as $key => $string) {
            if (trim($string) == '' || !$this->rowIsExcluded($key)) {
                $all_parts[] = $string;
                continue;
            }
            if (!\RegularLabs\Library\RegEx::match($regex_outer, $string) || !\RegularLabs\Library\RegEx::match($regex_inner, $string)) {
                $all_parts[] = $string;
                continue;
            }
            // using exclude on this excluded row to get the reverse result
            $nested = (new \RegularLabs\Library\StringReplacer($string))->excludeRegex($regex_inner);
            array_push($all_parts, ...$nested->getParts());
        }
        $this->setParts($all_parts);
        return $this;
    }
    public function replace($search, $replace): self
    {
        foreach ($this->parts as $key => &$string) {
            if ($this->shouldSkip($key, $string)) {
                continue;
            }
            $string = str_replace($search, $replace, $string);
        }
        return $this;
    }
    public function replaceRegex(string $search, string $replace): self
    {
        foreach ($this->parts as $key => &$string) {
            if ($this->shouldSkip($key, $string)) {
                continue;
            }
            $string = \RegularLabs\Library\RegEx::replace($search, $replace, $string);
        }
        return $this;
    }
    public function run($callback, $on_excluded = \false): self
    {
        foreach ($this->parts as $key => &$string) {
            if (trim($string) == '' || $this->rowIsExcluded($key) && !$on_excluded) {
                continue;
            }
            $callback($string);
        }
        $this->flattenParts();
        return $this;
    }
    public function set(string $string): void
    {
        $this->parts = [$string];
    }
    public function stillContains(string $string): bool
    {
        foreach ($this->parts as $key => $value) {
            if (trim($value) == '' || $this->rowIsExcluded($key)) {
                continue;
            }
            if (str_contains($this->toString(), $string)) {
                return \true;
            }
        }
        return \false;
    }
    public function toString(): string
    {
        return implode('', $this->parts);
    }
    private static function includeHtmlTagsOnString(string &$string, array $tags): void
    {
        $replacer = new \RegularLabs\Library\StringReplacer($string);
        foreach ($tags as $tag_name => $tag_params) {
            self::includeSingleHtmlTag($replacer, $tag_name, $tag_params);
        }
        $string = $replacer->getParts();
    }
    private static function includeSingleHtmlTag(\RegularLabs\Library\StringReplacer &$replacer, $tag_name, $tag_params): void
    {
        if ($tag_name == '*') {
            $tag_name = '[a-zA-Z][^> ]*';
        }
        $regex_tag = '(</?' . $tag_name . '(?: [^>]*)?>)';
        if (!count($tag_params)) {
            // include the whole tag (exclude on an excluded row)
            $replacer->excludeRegex($regex_tag);
            return;
        }
        // only include the parameter values
        $regex_params = '()(' . \RegularLabs\Library\RegEx::quote($tag_params) . '=")([^"]*)';
        $replacer->excludeRegexNested($regex_tag, $regex_params);
    }
    private function cleanParts(): void
    {
        if (!$this->enable_clean) {
            return;
        }
        $delimiter = '<!-- ___RL_DELIMITER___ -->';
        $temp_string = implode($delimiter, $this->parts);
        $temp_string = str_replace($delimiter . $delimiter, '', $temp_string);
        $this->parts = explode($delimiter, $temp_string);
    }
    private function flattenParts(): void
    {
        // move any nested parts to the parent
        $all_parts = [];
        foreach ($this->parts as $string) {
            if (!is_array($string)) {
                $all_parts[] = $string;
                continue;
            }
            array_push($all_parts, ...$string);
        }
        $this->setParts($all_parts);
    }
    private function getFormRegexes(array $form_classes = []): object
    {
        $form_classes = \RegularLabs\Library\ArrayHelper::toArray($form_classes);
        $start = '(<form\s[^>]*(?:' . '(?:id|name)="(?:adminForm|postform|submissionForm|default_action_user|seblod_form|spEntryForm)"' . '|action="[^"]*option=com_myjspace&(?:amp;)?view=see"' . (!empty($form_classes) ? '|class="(?:[^"]* )?(?:' . implode('|', $form_classes) . ')(?: [^"]*)?"' : '') . '))';
        $end = '(</form>)';
        return (object) compact('start', 'end');
    }
    private function getHtmlTagArray(array $tags = []): array
    {
        $search_tags = [];
        foreach ($tags as $tag) {
            if (!strlen($tag)) {
                continue;
            }
            $tag = trim($tag, ']');
            $tag_parts = explode('[', $tag);
            $tag_name = trim($tag_parts[0]);
            if ($tag_name == '*') {
                return [];
            }
            if (count($tag_parts) < 2) {
                $search_tags[$tag_name] = [];
                continue;
            }
            $tag_params = $tag_parts[1];
            // Trim and remove empty values
            $tag_params = array_diff(array_map('trim', explode(',', $tag_params)), ['']);
            if (in_array('*', $tag_params)) {
                // Make array empty if asterisk is found
                // (the whole tag should be allowed)
                $search_tags[$tag_name] = [];
                continue;
            }
            $search_tags[$tag_name] = $tag_params;
        }
        return $search_tags;
    }
    private function includeHtmlTags(array $tags = []): void
    {
        $tags = $this->getHtmlTagArray($tags);
        if (!count($tags)) {
            return;
        }
        $this->run(function (&$string) use ($tags) {
            self::includeHtmlTagsOnString($string, $tags);
        }, \true);
    }
    private function rowIsExcluded(int $key): bool
    {
        // uneven count = excluded
        return fmod($key, 2);
    }
    private function setParts(array $parts): void
    {
        $this->parts = $parts;
        $this->cleanParts();
    }
    private function shouldSkip(int $key, string $string): bool
    {
        return trim($string) == '' || $this->rowIsExcluded($key);
    }
}
