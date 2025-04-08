<?php
/**
 * @package         Sourcerer
 * @version         12.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\ObjectHelper as RL_Object;
use RegularLabs\Library\Php as RL_Php;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;

class Replace
{
    static $article      = null;
    static $current_area = null;

    public static function replace(
        string      &$string,
        string      $area = 'article',
        object|null $article = null,
        bool        $remove = false
    ): void
    {
        if ( ! is_string($string) || $string == '')
        {
            return;
        }

        Protect::_($string);

        $regex = Params::getRegex();

        $array       = self::stringToSplitArray($string, $regex);
        $array_count = count($array);

        if ($array_count <= 1)
        {
            return;
        }

        self::$article = $article;

        for ($i = 1; $i < $array_count - 1; $i++)
        {
            if ( ! fmod($i, 2) || ! RL_RegEx::match($regex, $array[$i], $match))
            {
                continue;
            }

            $content = self::handleMatch($match, $area, $remove);

            $array[$i] = $match['start_pre'] . $match['start_post'] . $content . $match['end_pre'] . $match['end_post'];
        }

        $string = implode('', $array);
    }

    public static function replaceInTheRest(string &$string): void
    {
        if ( ! is_string($string) || $string == '')
        {
            return;
        }

        [$start_tags, $end_tags] = Params::getTags();

        [$pre_string, $string, $post_string] = RL_Html::getContentContainingSearches(
            $string,
            $start_tags,
            $end_tags
        );

        if ($string == '')
        {
            $string = $pre_string . $string . $post_string;

            return;
        }

        // COMPONENT
        if (RL_Document::isFeed())
        {
            $string = RL_RegEx::replace('(<item[^>]*>)', '\1<!-- START: SRC_COMPONENT -->', $string);
            $string = str_replace('</item>', '<!-- END: SRC_COMPONENT --></item>', $string);
        }

        if ( ! str_contains($string, '<!-- START: SRC_COMPONENT -->'))
        {
            Area::tag($string, 'component');
        }

        $components = Area::get($string, 'component');

        foreach ($components as $component)
        {
            self::replace($component[1], 'components');
            $string = str_replace($component[0], $component[1], $string);
        }

        // EVERYWHERE
        self::replace($string, 'other');

        $string = $pre_string . $string . $post_string;
    }

    private static function addInlineVariables(object $data, string &$content): void
    {
    }

    private static function cleanTags(string &$string): void
    {
        $tag_regex  = '<(\/?[a-z\!][^>]*?(?:\s.*?)?)>';
        $new_string = RL_RegEx::replace($tag_regex, '<\1\2>', $string);

        if ( ! is_null($new_string))
        {
            $string = $new_string;
        }
    }

    private static function convertWysiwygToPlainText(string $content): string
    {
        $content = RL_Html::convertWysiwygToPlainText($content);

        // Remove trailing spaces from EOT lines
        $content = RL_RegEx::replace('(=\s*<<<([^\s]+)) ?(\n.*?\2;) ?', '\1\3', $content);

        return $content;
    }

    private static function getPhpFileCodeByType(?string $file, string $type): string
    {
    }

    private static function getPhpFilesCode(object $data): string
    {
    }

    private static function handleMatch(
        array  &$match,
        string $area = 'article',
        bool   $remove = false
    ): string
    {
        if ($remove)
        {
            return '';
        }

        $params = Params::get();

        $data = RL_PluginTag::getAttributesFromString($match['data']);

        $content = trim($match['content']);

        $data->raw ??= false;

        // Remove html tags if code is placed via the WYSIWYG editor
        if ( ! $data->raw)
        {
            $content = self::convertWysiwygToPlainText($content);
        }

        self::replacePhpShortCodes($content);


        self::replaceTags($content, $area);

        if ($data->raw)
        {
            return $content;
        }

        $trim = $data->trim ?? $params->trim;

        if ($trim)
        {
            $tags = RL_Html::cleanSurroundingTags([
                'start_pre'  => $match['start_pre'],
                'start_post' => $match['start_post'],
            ], ['div', 'p', 'span']);

            $match = [...$match, ...$tags];

            $tags = RL_Html::cleanSurroundingTags([
                'end_pre'  => $match['end_pre'],
                'end_post' => $match['end_post'],
            ], ['div', 'p', 'span']);

            $match = [...$match, ...$tags];

            $tags = RL_Html::cleanSurroundingTags([
                'start_pre' => $match['start_pre'],
                'end_post'  => $match['end_post'],
            ], ['div', 'p', 'span']);

            $match = [...$match, ...$tags];
        }

        return $content;
    }

    private static function loadFiles(object $data, string &$content): void
    {
    }

    private static function loadMediaFile(?string $file, string $type, array $options = []): void
    {
    }

    private static function loadScripts(object $data): void
    {
    }

    private static function loadStylesheets(object $data): void
    {
    }

    private static function replacePhpShortCodes(string &$string): void
    {
        // Replace <? with <?php
        $string = RL_RegEx::replace('<\?(\s.*?)\?>', '<?php\1?>', $string);
        // Replace <?= with <?php echo
        $string = RL_RegEx::replace('<\?=\s*(.*?)\?>', '<?php echo \1?>', $string);
    }

    private static function replaceTags(string &$string, string $area = 'article'): void
    {
        if (empty($string))
        {
            return;
        }

        // allow in component?
        if (RL_Protect::isRestrictedComponent(Params::get('components', []), $area))
        {
            Protect::protectTags($string);

            return;
        }

        self::replaceTagsByType($string, $area, 'php');
        self::replaceTagsByType($string, $area, 'all');
        self::replaceTagsByType($string, $area, 'js');
        self::replaceTagsByType($string, $area, 'css');
    }

    /**
     * Replace any html style tags by a comment tag if not permitted
     * Match: <...>
     */
    private static function replaceTagsAll(
        string &$string,
        bool   $enabled = true,
        bool   $security_pass = true
    ): void
    {
        if (empty($string))
        {
            return;
        }

        if ( ! $enabled)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::_('SRC_CODE_REMOVED_NOT_ENABLED'));

            return;
        }

        if ( ! $security_pass)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', ''));

            return;
        }

        self::cleanTags($string);

        $area = Params::getArea('default');
        $forbidden_tags_array = explode(',', $area->forbidden_tags);
        RL_Array::clean($forbidden_tags_array);
        // remove the comment tag syntax from the array - they cannot be disabled
        $forbidden_tags_array = array_diff($forbidden_tags_array, ['!--']);
        // reindex the array
        $forbidden_tags_array = [...$forbidden_tags_array];

        $has_forbidden_tags = false;

        foreach ($forbidden_tags_array as $forbidden_tag)
        {
            if ( ! ( ! str_contains($string, '<' . $forbidden_tag)))
            {
                $has_forbidden_tags = true;
                break;
            }
        }

        if ( ! $has_forbidden_tags)
        {
            return;
        }

        // double tags
        $tag_regex = '<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>.*?</\1>';
        RL_RegEx::matchAll($tag_regex, $string, $matches);

        if ( ! empty($matches))
        {
            foreach ($matches as $match)
            {
                if ( ! in_array($match[1], $forbidden_tags_array))
                {
                    continue;
                }

                $tag    = Protect::getMessageCommentTag(JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match[1]));
                $string = str_replace($match[0], $tag, $string);
            }
        }

        // single tags
        $tag_regex = '<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>';
        RL_RegEx::matchAll($tag_regex, $string, $matches);

        if ( ! empty($matches))
        {
            foreach ($matches as $match)
            {
                if ( ! in_array($match[1], $forbidden_tags_array))
                {
                    continue;
                }

                $tag    = Protect::getMessageCommentTag(JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match[1]));
                $string = str_replace($match[0], $tag, $string);
            }
        }
    }

    private static function replaceTagsByType(
        ?string &$string,
        string  $area = 'article',
        string  $type = 'all'
    ): void
    {
        if (empty($string))
        {
            return;
        }

        $type_ext = '_' . $type;

        if ($type == 'all')
        {
            $type_ext = '';
        }

        $area_params   = Params::getArea('default');
        $security_pass = true;
        $enable = (bool) ($area_params->{'enable' . $type_ext} ?? true);

        switch ($type)
        {
            case 'php':
                self::replaceTagsPHP($string, $enable, $security_pass);
                break;
            case 'js':
                self::replaceTagsJS($string, $enable, $security_pass);
                break;
            case 'css':
                self::replaceTagsCSS($string, $enable, $security_pass);
                break;
            default:
                self::replaceTagsAll($string, $enable, $security_pass);
                break;
        }
    }

    /**
     * Replace the CSS tags by a comment tag if not permitted
     */
    private static function replaceTagsCSS(
        string &$string,
        bool   $enabled = true,
        bool   $security_pass = true
    ): void
    {
        if (empty($string))
        {
            return;
        }

        // quick check to see if i is necessary to do anything
        if (( ! str_contains($string, 'style')) && ( ! str_contains($string, 'link')))
        {
            return;
        }

        // Match:
        // <script ...>...</script>
        $tag_regex =
            '(<\s*style\s[^>]*?[^/]\s*>'
            . '(.*?)'
            . '<\s*\/\s*style\s*>)';
        $arr       = self::stringToSplitArray($string, $tag_regex);
        $arr_count = count($arr);

        // Match:
        // <script ...>
        // single script tags are not xhtml compliant and should not occur, but just in case they do...
        if ($arr_count == 1)
        {
            $tag_regex = '(<\s*link\s[^>]*?(rel="stylesheet"|type="text/css").*?>)';
            $arr       = self::stringToSplitArray($string, $tag_regex);
            $arr_count = count($arr);
        }

        if ($arr_count <= 1)
        {
            return;
        }

        if ( ! $enabled)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_CSS')));

            return;
        }

        if ( ! $security_pass)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_CSS')));

            return;
        }
    }

    /**
     * Replace the JavaScript tags by a comment tag if not permitted
     */
    private static function replaceTagsJS(
        string &$string,
        bool   $enabled = true,
        bool   $security_pass = true
    ): void
    {
        if (empty($string))
        {
            return;
        }

        // quick check to see if i is necessary to do anything
        if (( ! str_contains($string, 'script')))
        {
            return;
        }

        // Match:
        // <script ...>...</script>
        $tag_regex =
            '(<\s*script\s[^>]*?[^/]\s*>'
            . '(.*?)'
            . '<\s*\/\s*script\s*>)';
        $arr       = self::stringToSplitArray($string, $tag_regex);
        $arr_count = count($arr);

        // Match:
        // <script ...>
        // single script tags are not xhtml compliant and should not occur, but just incase they do...
        if ($arr_count == 1)
        {
            $tag_regex = '(<\s*script\s.*?>)';
            $arr       = self::stringToSplitArray($string, $tag_regex);
            $arr_count = count($arr);
        }

        if ($arr_count <= 1)
        {
            return;
        }

        if ( ! $enabled)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_JAVASCRIPT')));

            return;
        }

        if ( ! $security_pass)
        {
            // replace source block content with HTML comment
            $string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_JAVASCRIPT')));

            return;
        }
    }

    /**
     * Replace the PHP tags with the evaluated PHP scripts
     * Or replace by a comment tag the PHP tags if not permitted
     */
    private static function replaceTagsPHP(
        string &$string,
        bool   $enabled = true,
        bool   $security_pass = true
    ): void
    {
        if (empty($string))
        {
            return;
        }

        if (( ! str_contains($string, '<?')))
        {
            return;
        }

        // Match ( read {} as <> ):
        // {?php ... ?}
        // {? ... ?}

        $string_array       = self::stringToSplitArray($string, '<\?(?:php)?[\s<](.*?)\?>');
        $string_array_count = count($string_array);

        if ($string_array_count < 1)
        {
            $string = implode('', $string_array);

            return;
        }

        if ( ! $enabled)
        {
            // replace source block content with HTML comment
            $string_array    = [];
            $string_array[0] = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_PHP')));

            $string = implode('', $string_array);

            return;
        }

        if ( ! $security_pass)
        {
            // replace source block content with HTML comment
            $string_array    = [];
            $string_array[0] = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_PHP')));

            $string = implode('', $string_array);

            return;
        }

        // if source block content has more than 1 php block, combine them
        if ($string_array_count > 3)
        {
            for ($i = 2; $i < $string_array_count - 1; $i++)
            {
                if (fmod($i, 2) == 0)
                {
                    $string_array[1] .= "<!-- SRC_SEMICOLON --> ?>" . $string_array[$i] . "<?php ";
                    unset($string_array[$i]);
                    continue;
                }

                $string_array[1] .= $string_array[$i];
                unset($string_array[$i]);
            }
        }

        $semicolon = '<!-- SRC_SEMICOLON -->';
        $script    = trim($string_array[1]) . $semicolon;
        $script    = RL_RegEx::replace('(;\s*)?' . RL_RegEx::quote($semicolon), ';', $script);

        $area = Params::getArea('default');

        $forbidden_php_array = explode(',', $area->forbidden_php);
        RL_Array::clean($forbidden_php_array);

        $forbidden_php_regex = '[^a-z_](' . implode('|', $forbidden_php_array) . ')(\s*\(|\s+[\'"])';

        RL_RegEx::matchAll($forbidden_php_regex, ' ' . $script, $functions);

        if ( ! empty($functions))
        {
            $functionsArray = [];

            foreach ($functions as $function)
            {
                $functionsArray[] = $function[1] . ')';
            }

            $comment = JText::_('SRC_PHP_CODE_REMOVED_FORBIDDEN') . ': ( ' . implode(', ', $functionsArray) . ' )';

            $string_array[1] = RL_Document::isHtml()
                ? Protect::getMessageCommentTag($comment)
                : '';

            $string = implode('', $string_array);

            return;
        }

        $output = RL_Php::execute('<?php ' . $script . ' ?>', self::$article);

        $string_array[1] = $output;

        $string = implode('', $string_array);
    }

    private static function stringToSplitArray(
        string $string,
        string $search
    ): array
    {
        $params = Params::get();

        $string = RL_RegEx::replace($search, $params->splitter . '\1' . $params->splitter, $string);

        return explode($params->splitter, $string);
    }
}
