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
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
use Exception;
use RegularLabs\Scoped\Intervention\Image\Exception\NotReadableException as NotReadableException;
use RegularLabs\Scoped\Intervention\Image\ImageManagerStatic as ImageManager;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\Filesystem\File as JFile;
class Image
{
    static $data_files = [];
    private $attributes;
    private $description;
    private $file;
    private $input;
    private $is_resized;
    private $output;
    private $settings;
    /**
     * @param object $attributes @deprecated use SET methods instead
     */
    public function __construct(string $file = null, object $attributes = null)
    {
        $this->settings = (object) ['resize' => (object) ['enabled' => \true, 'quality' => 70, 'method' => 'crop', 'folder' => 'resized', 'max_age' => 0, 'force_overwrite' => \false, 'use_retina' => \true, 'retina_pixel_density' => 1.5], 'title' => (object) ['enabled' => \true, 'format' => 'uppercase_first', 'lowercase_words' => 'a,the,to,at,in,with,and,but,or'], 'lazy_loading' => \false];
        if ($file) {
            $this->setFile($file);
        }
        if ($attributes) {
            $this->setFromOldAttributes($attributes);
        }
        $this->resetOutput();
    }
    public static function cleanPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path_site = str_replace('\\', '/', JPATH_SITE) . '/';
        if (str_starts_with($path, $path_site)) {
            $path = substr($path, strlen($path_site));
        }
        $path = ltrim(str_replace(JUri::root(), '', $path), '/');
        $path = strtok($path, '?');
        $path = strtok($path, '#');
        return $path;
    }
    public function createResizeFolder(): self
    {
        $path = $this->getResizeFolderPath();
        if (is_dir($path)) {
            return $this;
        }
        if (!@mkdir($path, 0755, \true)) {
            $this->settings->resize->folder = '';
        }
        return $this;
    }
    public function exists(?string $file = null): bool
    {
        $file = urldecode($file ?: $this->getFilePath());
        return $file && file_exists($file) && is_file($file);
    }
    public function forceOverwriteResized(): self
    {
        return $this->setResizeAttribute('force_overwrite', 1.0E-5);
    }
    public function getAlt(): string
    {
        if (isset($this->attributes->alt)) {
            return $this->attributes->alt;
        }
        $alt = $this->getDataFileDataByType('alt');
        if (!is_null($alt)) {
            return htmlentities($alt);
        }
        return $this->getTitle(\true);
    }
    public function getDataFileData(): mixed
    {
        $image_data = $this->getFolderFileData();
        return $image_data[$this->getFileStem()] ?? null;
    }
    public function getDataFileDataByType(string $type = 'title'): mixed
    {
        $image_data = $this->getDataFileData();
        if (isset($image_data[$type])) {
            return $image_data[$type];
        }
        $default_data = $this->getDefaultDataFileData();
        return $default_data[$type] ?? null;
    }
    public function getDefaultDataFileData(): mixed
    {
        $image_data = $this->getFolderFileData();
        return $image_data['*'] ?? null;
    }
    public function getDescription(): string
    {
        if (!is_null($this->description)) {
            return $this->description;
        }
        return $this->getDataFileDataByType('description') ?? '';
    }
    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }
    public function getFile(): string
    {
        $this->prepareInput();
        return $this->input->file;
    }
    public function setFile($file): self
    {
        $this->file = $file;
        $this->resetInput();
        return $this;
    }
    public function getFileExtension(): string
    {
        $this->prepareInput();
        return $this->input->file_extension;
    }
    public function getFileInfo(string $file, string $file_path): object
    {
        $file_path = urldecode($file_path);
        $path_info = @pathinfo($file_path);
        if (\RegularLabs\Library\File::isInternal($file_path)) {
            $size_info = @getimagesize($file_path);
        }
        $file_name = $path_info['basename'] ?? basename($file_path);
        $file_stem = $path_info['filename'] ?? JFile::stripExt($file_name);
        $file_extension = $path_info['extension'] ?? JFile::getExt($file_name);
        return (object) ['folder' => \RegularLabs\Library\File::getDirName($file), 'folder_path' => $path_info['dirname'] ?? null, 'file' => $file, 'file_path' => $file_path, 'file_name' => $file_name, 'file_stem' => $file_stem, 'file_extension' => $file_extension, 'mime' => $size_info['mime'] ?? null, 'width' => $size_info[0] ?? null, 'height' => $size_info[1] ?? null];
    }
    public function getFileName(): string
    {
        $this->prepareInput();
        return $this->input->file_name;
    }
    public function getFilePath(): string
    {
        $this->prepareInput();
        return $this->input->file_path;
    }
    public function getFileStem(): string
    {
        $this->prepareInput();
        return $this->input->file_stem;
    }
    public function getFolder(): string
    {
        $this->prepareInput();
        return $this->input->folder;
    }
    public function getFolderPath(): string
    {
        $this->prepareInput();
        return $this->input->folder_path;
    }
    public function getHeight(): int
    {
        $this->prepareOutput();
        return $this->output->height;
    }
    public function getOriginalHeight(): int
    {
        $this->prepareInput();
        return $this->input->height;
    }
    public function getOriginalWidth(): int
    {
        $this->prepareInput();
        return $this->input->width;
    }
    public function getOutputFile(): string
    {
        $this->prepareInput();
        if ($this->isExternal() || !$this->exists()) {
            return $this->input->file;
        }
        $this->prepareOutput();
        return $this->output->file;
    }
    public function getOutputFilePath(): string
    {
        $this->prepareOutput();
        return $this->output->file_path;
    }
    /**
     * @depecated Use getOutputFile() instead
     */
    public function getPath(): string
    {
        return $this->getOutputFile();
    }
    public function getSrcSet(?string $pixel_density = null): ?string
    {
        if ($this->isExternal()) {
            return null;
        }
        if (!$this->settings->resize->use_retina) {
            return null;
        }
        if ($this->getFilePath() == $this->getOutputFilePath()) {
            return null;
        }
        $pixel_density = $pixel_density ?: $this->settings->resize->retina_pixel_density;
        $single = $this->getOutputFile();
        $double = $this->getOutputFile2x();
        if ($double == $single) {
            return null;
        }
        return $double . ' ' . (float) $pixel_density . 'x, ' . $single . ' 1x';
    }
    public function getTagAttributes(): object
    {
        $ordered_keys = ['src', 'srcset', 'alt', 'title', 'width', 'height', 'class', 'loading'];
        krsort($ordered_keys);
        $tag_width = $this->output->width;
        $tag_height = $this->output->height;
        $this->attributes = $this->attributes ?: (object) [];
        $this->attributes->src = $this->getOutputFile();
        $this->attributes->srcset = $this->getSrcset();
        $this->attributes->alt = $this->getAlt();
        $this->attributes->title = $this->getTitle(\true);
        $this->attributes->width = $this->output->width ?: $this->attributes->width ?? 0;
        $this->attributes->height = $this->output->height ?: $this->attributes->height ?? 0;
        if ($this->attributes->width < $tag_width) {
            $this->attributes->width = $tag_width;
            $this->attributes->height = round($this->attributes->height * ($tag_width / $this->attributes->width));
        }
        if ($this->attributes->height < $tag_height) {
            $this->attributes->height = $tag_height;
            $this->attributes->width = round($this->attributes->width * ($tag_height / $this->attributes->height));
        }
        $this->attributes->width = $this->attributes->width ?: null;
        $this->attributes->height = $this->attributes->height ?: null;
        $attributes = (array) $this->attributes;
        foreach ($ordered_keys as $key) {
            if (!key_exists($key, $attributes)) {
                continue;
            }
            $value = $attributes[$key];
            if (is_null($value)) {
                continue;
            }
            unset($attributes[$key]);
            $attributes = [$key => $value, ...$attributes];
        }
        return (object) $attributes;
    }
    public function getTitle(bool $force = \false): string
    {
        if (isset($this->attributes->title)) {
            return $this->attributes->title;
        }
        $title = $this->getDataFileDataByType('title');
        if (!is_null($title)) {
            // Remove HTML tags
            $title = strip_tags($title);
            return htmlentities($title);
        }
        return $this->getTitleFromName($force);
    }
    public function getWidth(): int
    {
        $this->prepareOutput();
        return $this->output->width;
    }
    public function isResized(): bool
    {
        if (!is_null($this->is_resized)) {
            return $this->is_resized;
        }
        $this->is_resized = \false;
        $this->prepareInput();
        $parent_folder_name = \RegularLabs\Library\File::getBaseName($this->input->folder_path);
        $resize_folder_name = $this->settings->resize->folder;
        // Image is not inside the resize folder
        if ($parent_folder_name != $resize_folder_name) {
            return \false;
        }
        $parent_folder = \RegularLabs\Library\File::getDirName($this->input->folder_path);
        $file_name = $this->input->file_name;
        // Check if image with same name exists in parent folder
        if ($this->exists($parent_folder . '/' . \RegularLabs\Library\StringHelper::utf8_decode($file_name))) {
            $this->is_resized = \true;
            return \true;
        }
        // Remove any dimensions from the file
        // image_300x200.jpg => image.jpg
        $file_name = \RegularLabs\Library\RegEx::replace('_[0-9]+x[0-9]*(\.[^.]+)$', '\1', $file_name);
        // Check again if image with same name (but without dimensions) exists in parent folder
        if ($this->exists($parent_folder . '/' . \RegularLabs\Library\StringHelper::utf8_decode($file_name))) {
            $this->is_resized = \true;
            return \true;
        }
        return \false;
    }
    public function outputExists(): bool
    {
        $file = $this->getOutputFilePath();
        return $file && file_exists($file) && is_file($file);
    }
    public function render(string $outer_class = ''): string
    {
        $attributes = (array) $this->getTagAttributes();
        $image_tag = '<img ' . \RegularLabs\Library\HtmlTag::flattenAttributes($attributes) . ' />';
        if (!$outer_class) {
            return $image_tag;
        }
        return '<div class="' . htmlspecialchars($outer_class) . '">' . $image_tag . '</div>';
    }
    /**
     * @depecated Use render() instead
     */
    public function renderTag(): string
    {
        return $this->render($this->attributes->{'outer-class'} ?? '');
    }
    public function setAlt($alt): self
    {
        return $this->setTagAttribute('alt', $alt);
    }
    public function setAutoTitles(bool $enabled, string $title_format = 'titlecase', string $lowercase_words = 'a,the,to,at,in,with,and,but,or'): self
    {
        $this->settings->title->enabled = $enabled;
        $this->settings->title->format = $title_format;
        $this->settings->title->lowercase_words = $lowercase_words;
        return $this;
    }
    public function setDimensions(int|float|string $width, int|float|string $height): self
    {
        $this->setResizeMethod(empty($width) || empty($height) ? 'scale' : 'crop');
        $this->setOutputSetting('width', (int) $width);
        $this->setOutputSetting('height', (int) $height);
        return $this;
    }
    public function setEnableResize(bool $enabled): self
    {
        $this->settings->resize->enabled = $enabled;
        return $this;
    }
    public function setHeight(int|float|string $height): self
    {
        return $this->setOutputSetting('height', (int) $height);
    }
    public function setItemProp(?string $itemprop): self
    {
        $itemprop = $itemprop ? 'image' : null;
        $this->setTagAttribute('itemprop', $itemprop);
        return $this;
    }
    public function setLazyLoading(?bool $enabled): self
    {
        return $this->setTagAttribute('loading', $enabled ? 'lazy' : null);
    }
    public function setLowerCaseWords(array $words): self
    {
        $this->settings->title->lowercase_words = $words;
        return $this;
    }
    public function setOutputFileData(): self
    {
        if (!empty($this->output->file)) {
            return $this;
        }
        $this->prepareInput();
        $output = clone $this->input;
        if (!empty($this->output->width) || !empty($this->output->height)) {
            $output->width = $this->output->width;
            $output->height = $this->output->height;
        }
        $this->output = $output;
        return $this;
    }
    public function setOutputSetting(string $key, mixed $value): self
    {
        if (is_null($this->output)) {
            $this->output = (object) ['width' => 0, 'height' => 0];
        }
        if ($this->output->{$key} == $value) {
            return $this;
        }
        $this->output->{$key} = $value;
        $this->resetOutput();
        return $this;
    }
    public function setResizeAttribute(string $key, mixed $value): self
    {
        if ($this->settings->resize->{$key} == $value) {
            return $this;
        }
        $this->settings->resize->{$key} = $value;
        $this->resetOutput();
        return $this;
    }
    public function setResizeFolder(string $folder = 'resized'): self
    {
        return $this->setResizeAttribute('folder', $folder);
    }
    public function setResizeMaxAge(int $age_in_days): self
    {
        return $this->setResizeAttribute('max_age', $age_in_days);
    }
    public function setResizeMethod(string $method): self
    {
        $this->settings->resize->method = $method;
        return $this;
    }
    public function setResizeQuality(string|int $quality): self
    {
        return $this->setResizeAttribute('quality', $this->parseQuality($quality));
    }
    public function setRetinaPixelDensity(string $pixel_density): self
    {
        return $this->setResizeAttribute('retina_pixel_density', $pixel_density);
    }
    public function setTagAttribute(string $key, mixed $value): self
    {
        if (is_null($this->attributes)) {
            $this->attributes = (object) [];
        }
        $this->attributes->{$key} = $value;
        return $this;
    }
    public function setTagAttributes(object $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setTagAttribute($key, $value);
        }
        return $this;
    }
    public function setTitle(string $title): self
    {
        return $this->setTagAttribute('title', $title);
    }
    public function setUseRetina(bool $use_retina): self
    {
        return $this->setResizeAttribute('use_retina', $use_retina);
    }
    public function setWidth(int|float|string $width): self
    {
        return $this->setOutputSetting('width', (int) $width);
    }
    private function getFolderFileData(): array
    {
        $folder = $this->getFolderPath();
        if (isset(self::$data_files[$folder])) {
            return self::$data_files[$folder];
        }
        self::$data_files[$folder] = [];
        if (!$this->exists($folder . '/data.txt')) {
            return self::$data_files[$folder];
        }
        $data = file_get_contents($folder . '/data.txt');
        $data = str_replace("\r", '', $data);
        $data = explode("\n", $data);
        foreach ($data as $data_line) {
            if (empty($data_line) || $data_line[0] == '#' || !str_contains($data_line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $data_line, 2);
            if (!\RegularLabs\Library\RegEx::match('^(?<file>.*?)\[(?<type>.*)\]$', $key, $match)) {
                continue;
            }
            $file = $match['file'];
            $type = $match['type'];
            if (!isset(self::$data_files[$folder][$file])) {
                self::$data_files[$folder][$file] = [];
            }
            self::$data_files[$folder][$file][$type] = $val;
        }
        return self::$data_files[$folder];
    }
    private function getLowercaseWords(): array
    {
        $words = $this->settings->title->lowercase_words;
        $words = \RegularLabs\Library\ArrayHelper::implode($words, ',');
        $words = \RegularLabs\Library\StringHelper::strtolower($words);
        return explode(',', ' ' . str_replace(',', ' , ', $words . ' '));
    }
    private function getOutputFile2x(): string
    {
        if ($this->isExternal()) {
            return $this->getOutputFile();
        }
        $double_width = $this->output->width * 2;
        $double_height = $this->output->height * 2;
        if ($double_width == $this->input->width && $double_height == $this->input->height) {
            return $this->getOutputFile();
        }
        $double_size = \RegularLabs\Library\ObjectHelper::clone($this);
        $double_size->setDimensions($double_width, $double_height);
        return $double_size->getOutputFile();
    }
    private function getResizeBoundry(): string
    {
        if ($this->input->width / $this->output->width > $this->input->height / $this->output->height) {
            return 'width';
        }
        return 'height';
    }
    private function getResizeDimensions(): array
    {
        if (!$this->output->width) {
            return [null, $this->output->height];
        }
        if (!$this->output->height) {
            return [$this->output->width, null];
        }
        if ($this->input->width / $this->output->width > $this->input->height / $this->output->height) {
            return [null, $this->output->height];
        }
        return [$this->output->width, null];
    }
    private function getResizeFolder(): string
    {
        if (!$this->settings->resize->folder) {
            $this->setResizeFolder();
        }
        return $this->getFolder() . '/' . $this->settings->resize->folder;
    }
    private function getResizeFolderPath(): string
    {
        if (!$this->settings->resize->folder) {
            $this->setResizeFolder();
        }
        return $this->getFolderPath() . '/' . $this->settings->resize->folder;
    }
    private function getResizedFileName(): string
    {
        $this->prepareInput();
        return $this->input->file_stem . '_' . $this->output->width . 'x' . $this->output->height . '.' . $this->input->file_extension;
    }
    private function getTitleFromName(bool $force = \false): string
    {
        if (!$force && !$this->settings->title->enabled) {
            return '';
        }
        $title = \RegularLabs\Library\StringHelper::toSpaceSeparated($this->input->file_stem);
        switch ($this->settings->title->format) {
            case 'lowercase':
                return \RegularLabs\Library\StringHelper::strtolower($title);
            case 'uppercase':
                return \RegularLabs\Library\StringHelper::strtoupper($title);
            case 'uppercase_first':
                return \RegularLabs\Library\StringHelper::strtoupper(\RegularLabs\Library\StringHelper::substr($title, 0, 1)) . \RegularLabs\Library\StringHelper::strtolower(\RegularLabs\Library\StringHelper::substr($title, 1));
            case 'titlecase':
                return function_exists('mb_convert_case') ? mb_convert_case(\RegularLabs\Library\StringHelper::strtolower($title), \MB_CASE_TITLE) : ucwords(strtolower($title));
            case 'titlecase_smart':
                $title = function_exists('mb_convert_case') ? mb_convert_case(\RegularLabs\Library\StringHelper::strtolower($title), \MB_CASE_TITLE) : ucwords(strtolower($title));
                $lowercase_words = $this->getLowercaseWords();
                return str_ireplace($lowercase_words, $lowercase_words, $title);
            default:
                return $title;
        }
    }
    private function handleDimensions(): self
    {
        if (!$this->input->width || !$this->input->height) {
            return $this;
        }
        // Width and height are both not set, so revert to original dimensions
        if (!$this->output->height && !$this->output->width) {
            $this->output->width = $this->input->width;
            $this->output->height = $this->input->height;
            return $this;
        }
        if (!$this->outputExists()) {
            return $this;
        }
        if ($this->settings->resize->method == 'crop') {
            $this->output->width = $this->output->width ?: $this->output->height;
            $this->output->height = $this->output->height ?: $this->output->width;
            return $this->resize();
        }
        // Width and height are both set
        if ($this->output->width && $this->output->height) {
            $boundry = $this->getResizeBoundry();
            $this->output->width = $boundry == 'width' ? $this->output->width : round($this->output->height / $this->input->height * $this->input->width);
            $this->output->height = $boundry == 'height' ? $this->output->height : round($this->output->width / $this->input->width * $this->input->height);
            return $this->resize();
        }
        $this->output->width = $this->output->width ?: round($this->output->height / $this->input->height * $this->input->width);
        $this->output->height = $this->output->height ?: round($this->output->width / $this->input->width * $this->input->height);
        return $this->resize();
    }
    private function isExternal(): bool
    {
        $this->prepareInput();
        return \RegularLabs\Library\File::isExternal($this->input->file);
    }
    private function limitDimensions(): self
    {
        if ($this->output->width <= $this->input->width && $this->output->height <= $this->input->height) {
            return $this;
        }
        if ($this->output->width > $this->input->width) {
            $this->output->height = $this->output->height / $this->output->width * $this->input->width;
            $this->output->width = $this->input->width;
        }
        if ($this->output->height > $this->input->height) {
            $this->output->width = $this->output->width / $this->output->height * $this->input->height;
            $this->output->height = $this->input->height;
        }
        $this->output->width = round($this->output->width);
        $this->output->height = round($this->output->height);
        return $this;
    }
    private function parseQuality(string|int $quality = 90): int
    {
        if (is_int($quality)) {
            return $quality;
        }
        return match ($quality) {
            'low' => 30,
            'medium' => 60,
            default => 90,
        };
    }
    private function prepareInput(): self
    {
        if (!is_null($this->input)) {
            return $this;
        }
        if (!$this->file) {
            throw new Exception('No file set');
        }
        if (\RegularLabs\Library\File::isExternal($this->file)) {
            $this->input = $this->getFileInfo($this->file, $this->file);
            return $this;
        }
        $file = self::cleanPath($this->file);
        $this->input = $this->getFileInfo($file, JPATH_ROOT . '/' . ltrim($file, '/'));
        return $this;
    }
    private function prepareOutput(): self
    {
        if (!empty($this->output->file)) {
            return $this;
        }
        $this->prepareInput();
        if (empty($this->output->width) && empty($this->output->height)) {
            $this->output = clone $this->input;
            return $this;
        }
        $this->setOutputFileData();
        $this->handleDimensions();
        return $this;
    }
    private function resetInput(): self
    {
        $this->input = null;
        $this->resetOutput();
        return $this;
    }
    private function resetOutput(): self
    {
        if (is_null($this->output)) {
            $this->output = (object) ['width' => 0, 'height' => 0];
        }
        unset($this->output->file);
        return $this;
    }
    /**
     * Method to create a resized version of the current image and save them to disk
     */
    private function resize(?string $width = null, ?string $height = null, ?int $quality = null): self
    {
        if (!$this->settings->resize->enabled) {
            return $this;
        }
        if ($this->isResized()) {
            return $this;
        }
        if ($this->isExternal()) {
            return $this;
        }
        if (!is_null($width) || !is_null($height)) {
            $this->setDimensions($width, $height);
        }
        if (!is_null($quality)) {
            $this->setResizeQuality($quality);
        }
        if ($this->output->width == $this->input->width && $this->output->height == $this->input->height) {
            $this->output = clone $this->input;
            return $this;
        }
        $this->limitDimensions();
        $file = $this->getResizedFileName();
        $this->output->file = $this->getResizeFolder() . '/' . $file;
        $this->output->file_path = $this->getResizeFolderPath() . '/' . $file;
        $file_exists = $this->outputExists();
        $file_is_outdated = $this->settings->resize->force_overwrite;
        if (!$file_is_outdated && $file_exists && $this->settings->resize->max_age > 0) {
            $max_age_in_seconds = ceil($this->settings->resize->max_age * 60 * 60 * 24);
            $min_time = time() - $max_age_in_seconds;
            $file_is_outdated = filemtime($this->output->file_path) < $min_time;
        }
        if ($file_exists && !$file_is_outdated) {
            //$this->output = $this->getFileInfo($this->output->file, $this->output->file_path);
            return $this;
        }
        [$resize_width, $resize_height] = $this->getResizeDimensions();
        try {
            $resized = ImageManager::make($this->getFilePath())->orientate()->resize($resize_width, $resize_height, function ($constraint) {
                $constraint->aspectRatio();
            });
            if ($this->settings->resize->method == 'crop' || $this->output->width && $this->output->height) {
                $resized->crop($this->output->width, $this->output->height);
            }
            $this->createResizeFolder();
            $resized->save($this->output->file_path, $this->settings->resize->quality);
        } catch (NotReadableException $exception) {
            $resized = null;
        }
        if (!$resized) {
            $this->output = clone $this->input;
            $this->is_resized = \false;
        }
        $this->output = $this->getFileInfo($this->output->file, $this->output->file_path);
        return $this;
    }
    private function setFromOldAttributes(object $attributes): void
    {
        if (isset($attributes->alt)) {
            $this->setAlt($attributes->alt);
        }
        if (isset($attributes->title)) {
            $this->setTitle($attributes->title);
        }
        if (isset($attributes->class)) {
            $this->setTagAttribute('class', $attributes->class);
        }
        if (isset($attributes->{'outer-class'})) {
            $this->setTagAttribute('outer-class', $attributes->{'outer-class'});
        }
        if (isset($attributes->{'resize-folder'})) {
            $folder = \RegularLabs\Library\File::getBaseName($attributes->{'resize-folder'});
            $this->setResizeFolder($folder);
        }
        if (isset($attributes->quality)) {
            $this->setResizeQuality($attributes->quality);
        }
        $this->setDimensions($attributes->width ?? 0, $attributes->height ?? 0);
    }
}
