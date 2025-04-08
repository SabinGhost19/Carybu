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
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
class Article
{
    static $articles = [];
    /**
     * Method to get article data.
     */
    public static function get(int|string|null $id = null, bool $get_unpublished = \false, array $selects = []): object|null
    {
        $id = $id ?? null ?: (int) self::getId();
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $db = \RegularLabs\Library\DB::get();
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $custom_selects = !empty($selects);
        $query = \RegularLabs\Library\DB::getQuery()->select($custom_selects ? $selects : [
            'a.id',
            'a.asset_id',
            'a.title',
            'a.alias',
            'a.introtext',
            'a.fulltext',
            'a.state',
            'a.catid',
            'a.created',
            'a.created_by',
            'a.created_by_alias',
            // Use created if modified is 0
            'CASE WHEN a.modified = ' . \RegularLabs\Library\DB::quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified',
            'a.modified_by',
            'a.checked_out',
            'a.checked_out_time',
            'a.publish_up',
            'a.publish_down',
            'a.images',
            'a.urls',
            'a.attribs',
            'a.version',
            'a.ordering',
            'a.metakey',
            'a.metadesc',
            'a.access',
            'a.hits',
            'a.metadata',
            'a.featured',
            'a.language',
        ])->from(\RegularLabs\Library\DB::quoteName('#__content', 'a'));
        if (!is_numeric($id)) {
            $query->where('(' . \RegularLabs\Library\DB::is('a.title', $id) . ' OR ' . \RegularLabs\Library\DB::is('a.alias', $id) . ')');
        } else {
            $query->where(\RegularLabs\Library\DB::is('a.id', (int) $id));
        }
        // Join on content_frontpage table
        if (!$custom_selects) {
            $query->select([\RegularLabs\Library\DB::quoteName('fp.featured_up'), \RegularLabs\Library\DB::quoteName('fp.featured_down')]);
        }
        $query->join('LEFT', \RegularLabs\Library\DB::quoteName('#__content_frontpage', 'fp') . ' ON ' . \RegularLabs\Library\DB::quoteName('fp.content_id') . ' = ' . \RegularLabs\Library\DB::quoteName('a.id'));
        // Join on category table.
        if (!$custom_selects) {
            $query->select([\RegularLabs\Library\DB::quoteName('c.title', 'category_title'), \RegularLabs\Library\DB::quoteName('c.alias', 'category_alias'), \RegularLabs\Library\DB::quoteName('c.access', 'category_access'), \RegularLabs\Library\DB::quoteName('c.language', 'category_language'), \RegularLabs\Library\DB::quoteName('c.lft', 'category_lft'), \RegularLabs\Library\DB::quoteName('c.lft', 'category_ordering')]);
        }
        $query->innerJoin(\RegularLabs\Library\DB::quoteName('#__categories', 'c') . ' ON ' . \RegularLabs\Library\DB::quoteName('c.id') . ' = ' . \RegularLabs\Library\DB::quoteName('a.catid'))->where(\RegularLabs\Library\DB::is('c.published', '>0'));
        // Join on user table.
        if (!$custom_selects) {
            $query->select(\RegularLabs\Library\DB::quoteName('u.name', 'author'));
        }
        $query->join('LEFT', \RegularLabs\Library\DB::quoteName('#__users', 'u') . ' ON ' . \RegularLabs\Library\DB::quoteName('u.id') . ' = ' . \RegularLabs\Library\DB::quoteName('a.created_by'));
        // Join over the categories to get parent category titles
        if (!$custom_selects) {
            $query->select([\RegularLabs\Library\DB::quoteName('parent.title', 'parent_title'), \RegularLabs\Library\DB::quoteName('parent.id', 'parent_id'), \RegularLabs\Library\DB::quoteName('parent.path', 'parent_route'), \RegularLabs\Library\DB::quoteName('parent.alias', 'parent_alias'), \RegularLabs\Library\DB::quoteName('parent.language', 'parent_language')]);
        }
        $query->join('LEFT', \RegularLabs\Library\DB::quoteName('#__categories', 'parent') . ' ON ' . \RegularLabs\Library\DB::quoteName('parent.id') . ' = ' . \RegularLabs\Library\DB::quoteName('c.parent_id'));
        // Join on voting table
        if (!$custom_selects) {
            $query->select(['ROUND(v.rating_sum / v.rating_count, 0) AS rating', \RegularLabs\Library\DB::quoteName('v.rating_count', 'rating_count')]);
        }
        $query->join('LEFT', \RegularLabs\Library\DB::quoteName('#__content_rating', 'v') . ' ON ' . \RegularLabs\Library\DB::quoteName('v.content_id') . ' = ' . \RegularLabs\Library\DB::quoteName('a.id'));
        if (!$get_unpublished && !$user->authorise('core.edit.state', 'com_content') && !$user->authorise('core.edit', 'com_content')) {
            \RegularLabs\Library\DB::addArticleIsPublishedFilters($query);
        }
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            return null;
        }
        if (isset($data->attribs)) {
            // Convert parameter field to object.
            $registry = new JRegistry($data->attribs);
            $data->params = JComponentHelper::getParams('com_content');
            $data->params->merge($registry);
        }
        if (isset($data->metadata)) {
            // Convert metadata field to object.
            $data->metadata = new JRegistry($data->metadata);
        }
        return $cache->set($data);
    }
    /**
     * Gets the current article id based on url data
     */
    public static function getId(): int|false
    {
        $id = \RegularLabs\Library\Input::getInt('id');
        if (!$id || !(\RegularLabs\Library\Input::get('option', '') == 'com_content' && \RegularLabs\Library\Input::get('view', '') == 'article' || \RegularLabs\Library\Input::get('option', '') == 'com_flexicontent' && \RegularLabs\Library\Input::get('view', '') == 'item')) {
            return \false;
        }
        return $id;
    }
    public static function getPageNumber(array|string &$all_pages, string $search_string): int
    {
        if (is_string($all_pages)) {
            $all_pages = self::getPages($all_pages);
        }
        if (count($all_pages) < 2) {
            return 0;
        }
        foreach ($all_pages as $i => $page_text) {
            if ($i % 2) {
                continue;
            }
            if (!str_contains($page_text, $search_string)) {
                continue;
            }
            $all_pages[$i] = \RegularLabs\Library\StringHelper::replaceOnce($search_string, '---', $page_text);
            return $i / 2;
        }
        return 0;
    }
    public static function getPages(string $string): array
    {
        if ($string == '') {
            return [''];
        }
        return preg_split('#(<hr class="system-pagebreak" .*?>)#s', $string, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);
    }
    /**
     * Passes the different article parts through the given plugin method
     */
    public static function process(?object &$article, string $context, object &$class, string $method, array $params = [], array $ignore_types = []): void
    {
        self::processText('title', $article, $class, $method, $params, $ignore_types);
        self::processText('created_by_alias', $article, $class, $method, $params, $ignore_types);
        $has_text = isset($article->text);
        $has_description = isset($article->description);
        $has_article_texts = isset($article->introtext) && isset($article->fulltext);
        $text_same_as_description = $has_text && $has_description && $article->text == $article->description;
        $text_same_as_article_text = \false;
        self::processText('description', $article, $class, $method, $params, $ignore_types);
        // This is a category page with a category description. So skip the text processing
        if ($text_same_as_description) {
            $article->text = $article->description;
            return;
        }
        // Don't replace in text fields in the category list view, as they won't get used anyway
        if (\RegularLabs\Library\Document::isCategoryList($context)) {
            return;
        }
        // prevent fulltext from being messed with, when it is a json encoded string (Yootheme Pro templates do this for some weird f-ing reason)
        if (!empty($article->fulltext) && str_starts_with($article->fulltext, '<!-- {')) {
            self::processText('text', $article, $class, $method, $params, $ignore_types);
            return;
        }
        if ($has_text && $has_article_texts) {
            $check_text = \RegularLabs\Library\RegEx::replace('\s', '', $article->text);
            $check_introtext_fulltext = \RegularLabs\Library\RegEx::replace('\s', '', $article->introtext . ' ' . $article->fulltext);
            $text_same_as_article_text = $check_text == $check_introtext_fulltext;
        }
        if ($has_article_texts && !$has_text) {
            self::processText('introtext', $article, $class, $method, $params, $ignore_types);
            self::processText('fulltext', $article, $class, $method, $params, $ignore_types);
            return;
        }
        if ($has_article_texts && $text_same_as_article_text) {
            $splitter = '͞';
            if (str_contains($article->introtext, $splitter) || str_contains($article->fulltext, $splitter)) {
                $splitter = 'Ͽ';
            }
            $article->text = $article->introtext . $splitter . $article->fulltext;
            self::processText('text', $article, $class, $method, $params, $ignore_types);
            [$article->introtext, $article->fulltext] = explode($splitter, $article->text, 2);
            $article->text = str_replace($splitter, ' ', $article->text);
            return;
        }
        self::processText('text', $article, $class, $method, $params, $ignore_types);
        self::processText('introtext', $article, $class, $method, $params, $ignore_types);
        // Don't handle fulltext on category blog views
        if ($context == 'com_content.category' && \RegularLabs\Library\Input::get('view', '') == 'category') {
            return;
        }
        self::processText('fulltext', $article, $class, $method, $params, $ignore_types);
    }
    public static function processText(string $type, ?object &$article, object &$class, string $method, array $params = [], array $ignore_types = []): void
    {
        if (empty($article->{$type})) {
            return;
        }
        if (in_array($type, $ignore_types, \true)) {
            return;
        }
        call_user_func_array([$class, $method], [&$article->{$type}, ...$params]);
    }
}
