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
use Exception;
use Joomla\CMS\Cache\CacheControllerFactoryInterface as JCacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController as JOutputController;
use Joomla\CMS\Factory as JFactory;
class Cache
{
    static array $cache = [];
    /**
     * @var [JOutputController]
     */
    private array $file_cache_controllers = [];
    private bool $force_caching = \true;
    private string $group;
    private string $id;
    private int $time_to_life_in_seconds = 0;
    private bool $use_files = \false;
    public function __construct(mixed $id = null, string $group = 'regularlabs', int $class_offset = 1)
    {
        if (is_null($id)) {
            $caller = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 + $class_offset)[$class_offset];
            $id = [$caller['class'], $caller['function'], $caller['args']];
        }
        if (!is_string($id)) {
            $id = json_encode($id);
        }
        $this->id = md5($id);
        $this->group = $group;
    }
    public function exists(): bool
    {
        if (!$this->use_files) {
            return $this->existsMemory();
        }
        return $this->existsMemory() || $this->existsFile();
    }
    public function get(): mixed
    {
        return $this->use_files ? $this->getFile() : $this->getMemory();
    }
    public function reset(): void
    {
        unset(static::$cache[$this->id]);
        if ($this->use_files) {
            $this->getFileCache()->remove($this->id);
        }
    }
    public function resetAll(): void
    {
        static::$cache = [];
        if ($this->use_files) {
            $this->getFileCache()->clean($this->group);
        }
    }
    public function set(mixed $data): mixed
    {
        return $this->use_files ? $this->setFile($data) : $this->setMemory($data);
    }
    public function setTimeToLife(string|int $time_to_life_in_minutes = 0, bool $force_caching = \true): self
    {
        if (is_string($time_to_life_in_minutes)) {
            $time_to_life_in_minutes = round(strtotime($time_to_life_in_minutes) - time()) / 60;
        }
        $this->use_files = \true;
        // convert ttl to minutes
        $this->time_to_life_in_seconds = $time_to_life_in_minutes * 60;
        $this->force_caching = $force_caching;
        return $this;
    }
    public function useFiles(int $time_to_life_in_minutes = 0, bool $force_caching = \true): self
    {
        $this->setTimeToLife($time_to_life_in_minutes, $force_caching);
        return $this;
    }
    private function existsFile(): bool
    {
        if (\RegularLabs\Library\Document::isDebug()) {
            return \false;
        }
        return $this->getFileCache()->contains($this->id);
    }
    private function existsMemory(): bool
    {
        return array_key_exists($this->id, static::$cache);
    }
    /**
     * @throws Exception
     */
    private function getFile(): mixed
    {
        if ($this->existsMemory()) {
            return $this->getMemory();
        }
        $data = $this->getFileCache()->get($this->id);
        $this->setMemory($data);
        return $data;
    }
    private function getFileCache(): JOutputController
    {
        $options = ['defaultgroup' => $this->group];
        if ($this->time_to_life_in_seconds) {
            $options['lifetime'] = $this->time_to_life_in_seconds;
        }
        if ($this->force_caching) {
            $options['caching'] = \true;
        }
        $id = json_encode($options);
        if (isset($this->file_cache_controllers[$id])) {
            return $this->file_cache_controllers[$id];
        }
        $this->file_cache_controllers[$id] = JFactory::getContainer()->get(JCacheControllerFactoryInterface::class)->createCacheController('output', $options);
        return $this->file_cache_controllers[$id];
    }
    private function getMemory(): mixed
    {
        if (!$this->existsMemory()) {
            return null;
        }
        $data = static::$cache[$this->id];
        return is_object($data) ? clone $data : $data;
    }
    /**
     * @throws Exception
     */
    private function setFile(mixed $data): mixed
    {
        $this->setMemory($data);
        if (\RegularLabs\Library\Document::isDebug()) {
            return $data;
        }
        $this->getFileCache()->store($data, $this->id);
        return $data;
    }
    private function setMemory(mixed $data): mixed
    {
        static::$cache[$this->id] = $data;
        return $data;
    }
}
