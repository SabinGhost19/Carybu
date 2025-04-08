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
use Joomla\CMS\Application\CMSApplication as JCMSApplication;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\CMSPlugin as JCMSPlugin;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\Component\Finder\Administrator\Indexer\Helper as JIndexerHelper;
use Joomla\Component\Finder\Administrator\Indexer\Result as JIndexerResult;
use Joomla\Database\DatabaseDriver as JDatabaseDriver;
use Joomla\Event\DispatcherInterface as JDispatcherInterface;
use Joomla\Registry\Registry as JRegistry;
use stdClass;
class SystemPlugin extends JCMSPlugin
{
    public $_alias = '';
    public $_lang_prefix = '';
    public $_title = '';
    protected $_can_disable_by_url = \true;
    protected $_doc_ready = \false;
    protected $_enable_in_admin = \false;
    protected $_enable_in_frontend = \true;
    protected $_enable_in_indexer = \true;
    protected $_id = 0;
    protected $_jversion = 4;
    protected $_page_types = ['html', 'feed', 'pdf', 'xml', 'ajax', 'json', 'raw'];
    protected $_pass;
    /**
     * @var    JCMSApplication
     */
    protected $app;
    protected $autoloadLanguage = \true;
    /**
     * @var    JDatabaseDriver
     */
    protected $db;
    public function __construct(JDispatcherInterface &$subject, array $config = [])
    {
        if (isset($config['id'])) {
            $this->_id = $config['id'];
        }
        parent::__construct($subject, $config);
        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDbo();
        if (empty($this->_alias)) {
            $this->_alias = $this->_name;
        }
        if (empty($this->_title)) {
            $this->_title = strtoupper($this->_alias);
        }
    }
    /**
     * @param JIndexerResult $item  The search result
     * @param array          $query The search query of this result
     *
     * @return  void
     */
    public function handleOnFinderResult(JIndexerResult $item, $query)
    {
        $description = $item->description ?? '';
        $summary = $item->getElement('summary') ?? '';
        if (empty($description) && empty($summary)) {
            return;
        }
        $article = (object) ['id' => $item->getElement('id')];
        if (!empty($description)) {
            $article->fulltext = $description;
            \RegularLabs\Library\Article::processText('fulltext', $article, $this, 'processArticle', ['article', 'com_finder.index', $article]);
            $item->description = JIndexerHelper::parse($article->fulltext);
        }
        if ($description == $summary) {
            $item->setElement('summary', $item->description);
            return;
        }
        if (!empty($summary)) {
            $article->fulltext = $summary;
            \RegularLabs\Library\Article::processText('fulltext', $article, $this, 'processArticle', ['article', 'com_finder.index', $article]);
            $item->setElement('summary', $article->fulltext);
        }
    }
    /**
     * @param string $extension The extension for which a language file should be loaded
     * @param string $basePath  The basepath to use
     *
     * @return  bool  True, if the file has successfully loaded.
     */
    public function loadLanguage($extension = '', $basePath = JPATH_ADMINISTRATOR)
    {
        parent::loadLanguage('plg_system_regularlabs', JPATH_LIBRARIES . '/regularlabs');
        return parent::loadLanguage();
    }
    public function onAfterDispatch(): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterDispatch();
        $buffer = \RegularLabs\Library\Document::getComponentBuffer();
        $this->loadStylesAndScripts($buffer);
        if (!$buffer) {
            return;
        }
        $this->changeDocumentBuffer($buffer);
        \RegularLabs\Library\Document::setComponentBuffer($buffer);
    }
    /**
     * @return  void
     */
    public function onAfterInitialise(): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterInitialise();
    }
    public function onAfterRender(): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterRender();
        $html = $this->app->getBody();
        if ($html == '') {
            return;
        }
        if (!$this->changeFinalHtmlOutput($html)) {
            return;
        }
        $this->cleanFinalHtmlOutput($html);
        $this->app->setBody($html);
    }
    /**
     * @param object $module
     * @param array  $params
     */
    public function onAfterRenderModule(&$module, &$params): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterRenderModule($module, $params);
    }
    /**
     * @param string $buffer
     * @param array  $params
     */
    public function onAfterRenderModules(&$buffer, &$params): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterRenderModules($buffer, $params);
        if (empty($buffer)) {
            return;
        }
        $this->changeModulePositionOutput($buffer, $params);
    }
    public function onAfterRoute(): void
    {
        $this->_doc_ready = \true;
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnAfterRoute();
    }
    public function onBeforeCompileHead(): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnBeforeCompileHead();
    }
    /**
     * @param string    $context The context of the content being passed to the plugin.
     * @param mixed    &$row     An object with a "text" property
     * @param mixed    &$params  Additional parameters. See {@see PlgContentContent()}.
     * @param integer   $page    Optional page number. Unused. Defaults to zero.
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0): void
    {
        if (!$this->passChecks()) {
            return;
        }
        $area = isset($article->created_by) ? 'article' : 'other';
        $context = $params instanceof JRegistry && $params->get('rl_search') ? 'com_search.' . $params->get('readmore_limit') : $context;
        if (!$this->handleOnContentPrepare($area, $context, $article, $params, $page)) {
            return;
        }
        \RegularLabs\Library\Article::process($article, $context, $this, 'processArticle', [$area, $context, $article, $page]);
    }
    /**
     * @param JForm    $form The form
     * @param stdClass $data The data
     */
    public function onContentPrepareForm(JForm $form, $data): bool
    {
        if (!$this->passChecks()) {
            return \true;
        }
        return $this->handleOnContentPrepareForm($form, $data);
    }
    /**
     * @param JIndexerResult $item  The search result
     * @param array          $query The search query of this result
     */
    public function onFinderResult(JIndexerResult $item, $query)
    {
        if (!$this->passChecks()) {
            return;
        }
        $this->handleOnFinderResult($item, $query);
    }
    /**
     * @param string &$string
     * @param string  $area
     * @param string  $context The context of the content being passed to the plugin.
     * @param mixed   $article An object with a "text" property
     * @param int     $page    Optional page number. Unused. Defaults to zero.
     *
     * @return  void
     */
    public function processArticle(&$string, $area = 'article', $context = '', $article = null, $page = 0)
    {
    }
    /**
     * @param string $buffer
     *
     * @return  bool
     */
    protected function changeDocumentBuffer(&$buffer)
    {
        return \false;
    }
    /**
     * @param string $html
     *
     * @return  bool
     */
    protected function changeFinalHtmlOutput(&$html)
    {
        return \false;
    }
    /**
     * @param string $buffer
     * @param string $params
     *
     * @return  void
     */
    protected function changeModulePositionOutput(&$buffer, &$params)
    {
    }
    /**
     * @param string $html
     *
     * @return  void
     */
    protected function cleanFinalHtmlOutput(&$html)
    {
    }
    protected function extraChecks()
    {
        return \true;
    }
    /**
     * @return  void
     */
    protected function handleFeedArticles()
    {
        if (!empty($this->_page_types) && !in_array('feed', $this->_page_types, \true)) {
            return;
        }
        if (!\RegularLabs\Library\Document::isFeed() && \RegularLabs\Library\Input::get('option', '') != 'com_acymailing') {
            return;
        }
        if (!isset(\RegularLabs\Library\Document::get()->items)) {
            return;
        }
        $context = 'feed';
        $items = \RegularLabs\Library\Document::get()->items;
        $params = null;
        foreach ($items as $item) {
            $this->handleOnContentPrepare('article', $context, $item, $params);
        }
    }
    /**
     * @return  void
     */
    protected function handleOnAfterDispatch()
    {
        $this->handleFeedArticles();
    }
    /**
     * @return  void
     */
    protected function handleOnAfterInitialise()
    {
    }
    /**
     * @return  void
     *
     * Consider using changeFinalHtmlOutput instead
     */
    protected function handleOnAfterRender()
    {
    }
    /**
     * @param object $module
     * @param array  $params
     *
     * @return  void
     */
    protected function handleOnAfterRenderModule(&$module, &$params)
    {
    }
    /**
     * @param string $buffer
     * @param array  $params
     *
     * @return  void
     */
    protected function handleOnAfterRenderModules(&$buffer, &$params)
    {
    }
    /**
     * @return  void
     */
    protected function handleOnAfterRoute()
    {
    }
    /**
     * @return  void
     */
    protected function handleOnBeforeCompileHead()
    {
    }
    /**
     * @param string    $area
     * @param string    $context The context of the content being passed to the plugin.
     * @param mixed     $article An object with a "text" property
     * @param mixed    &$params  Additional parameters. See {@see PlgContentContent()}.
     * @param int       $page    Optional page number. Unused. Defaults to zero.
     *
     * @return  bool
     */
    protected function handleOnContentPrepare($area, $context, &$article, &$params, $page = 0)
    {
        return \true;
    }
    /**
     * @param JForm    $form The form
     * @param stdClass $data The data
     *
     * @return  bool
     */
    protected function handleOnContentPrepareForm(JForm $form, $data)
    {
        return \true;
    }
    /**
     * @return  bool
     */
    protected function is3rdPartyEditPage()
    {
        //        // Disable on Gridbox edit form: option=com_gridbox&view=gridbox
        //        if (Input::get('option', '') == 'com_gridbox' && Input::get('view', '') == 'gridbox')
        //        {
        //            return false;
        //        }
        // Disable on SP PageBuilder edit form: option=com_sppagebuilder&view=form
        if (\RegularLabs\Library\Input::get('option', '') == 'com_sppagebuilder' && \RegularLabs\Library\Input::get('view', '') == 'form') {
            return \true;
        }
        return \false;
    }
    /**
     * @param string $buffer
     *
     * @return  void
     */
    protected function loadStylesAndScripts(&$buffer)
    {
    }
    /**
     * @return  bool
     */
    protected function passChecks()
    {
        if (!is_null($this->_pass)) {
            return $this->_pass;
        }
        $this->setPass(\false);
        if (!$this->isFrameworkEnabled()) {
            return \false;
        }
        if ($this->is3rdPartyEditPage()) {
            return \false;
        }
        if ($this->_doc_ready && !$this->passPageTypes()) {
            return \false;
        }
        if (!$this->_enable_in_frontend && $this->app->isClient('site')) {
            return \false;
        }
        $is_indexer = $this->app->input->get('option') == 'com_finder' && $this->app->input->get('task') == 'batch';
        if ($this->app->input->get('option')) {
            $this->resetPass();
        }
        if (!$this->_enable_in_indexer && $is_indexer) {
            return \false;
        }
        $is_admin = !$this->app->isClient('site') && !$is_indexer;
        if (!$this->_enable_in_admin && $is_admin) {
            return \false;
        }
        // disabled by url?
        if ($this->_can_disable_by_url && \RegularLabs\Library\Protect::isDisabledByUrl($this->_alias)) {
            return \false;
        }
        if (!$this->extraChecks()) {
            return \false;
        }
        $this->setPass(\true);
        return \true;
    }
    protected function passPageTypes()
    {
        if (empty($this->_page_types)) {
            return \true;
        }
        if (in_array('*', $this->_page_types, \true)) {
            return \true;
        }
        if (\RegularLabs\Library\Document::isFeed()) {
            return in_array('feed', $this->_page_types, \true);
        }
        if (\RegularLabs\Library\Document::isPDF()) {
            return in_array('pdf', $this->_page_types, \true);
        }
        $page_type = \RegularLabs\Library\Document::get()->getType();
        return in_array($page_type, $this->_page_types, \true);
    }
    /**
     * Place an error in the message queue
     */
    protected function throwError($error)
    {
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        // Return if page is not an admin page or the admin login page
        if (!JFactory::getApplication()->isClient('administrator') || $user->get('guest')) {
            return;
        }
        // load the admin language file
        JFactory::getApplication()->getLanguage()->load('plg_' . $this->_type . '_' . $this->_name, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name);
        $text = JText::sprintf($this->_lang_prefix . '_' . $error, JText::_($this->_title));
        $text = JText::_($text) . ' ' . JText::sprintf($this->_lang_prefix . '_EXTENSION_CAN_NOT_FUNCTION', JText::_($this->_title));
        // Check if message is not already in queue
        $messagequeue = JFactory::getApplication()->getMessageQueue();
        foreach ($messagequeue as $message) {
            if ($message['message'] == $text) {
                return;
            }
        }
        JFactory::getApplication()->enqueueMessage($text, 'error');
    }
    /**
     * Check if the Regular Labs Library is enabled
     *
     * @return bool
     */
    private function isFrameworkEnabled(): bool
    {
        if (!defined('REGULAR_LABS_LIBRARY_ENABLED')) {
            $this->setIsFrameworkEnabled();
        }
        if (!REGULAR_LABS_LIBRARY_ENABLED) {
            $this->throwError('REGULAR_LABS_LIBRARY_NOT_ENABLED');
        }
        return REGULAR_LABS_LIBRARY_ENABLED;
    }
    /**
     * @return  void
     */
    private function resetPass(): void
    {
        $this->_pass = null;
    }
    /**
     * Set the define with whether the Regular Labs Library is enabled
     */
    private function setIsFrameworkEnabled(): void
    {
        if (!JPluginHelper::isEnabled('system', 'regularlabs')) {
            $this->throwError('REGULAR_LABS_LIBRARY_NOT_ENABLED');
            define('REGULAR_LABS_LIBRARY_ENABLED', \false);
            return;
        }
        define('REGULAR_LABS_LIBRARY_ENABLED', \true);
    }
    private function setPass(bool $pass): void
    {
        if (!$this->_doc_ready) {
            return;
        }
        $this->_pass = (bool) $pass;
    }
}
