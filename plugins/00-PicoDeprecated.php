<?php

/**
 * Serve features of Pico deprecated since v1.0
 *
 * This plugin exists for backward compatibility and is disabled by default.
 * It gets automatically enabled when a plugin which doesn't implement
 * {@link PicoPluginInterface} is loaded. This plugin triggers deprecated
 * events and automatically enables {@link PicoParsePagesContent} and
 * {@link PicoExcerpt}. These plugins heavily impact Picos performance! You
 * can disable this plugin by calling {@link PicoDeprecated::setEnabled()}.
 *
 * The following deprecated events are triggered by this plugin:
 * +---------------------+-----------------------------------------------------------+
 * | Event               | ... triggers the deprecated event                         |
 * +---------------------+-----------------------------------------------------------+
 * | onPluginsLoaded     | plugins_loaded()                                          |
 * | onConfigLoaded      | config_loaded($config)                                    |
 * | onRequestUrl        | request_url($url)                                         |
 * | onContentLoading    | before_load_content($file)                                |
 * | onContentLoaded     | after_load_content($file, $rawContent)                    |
 * | on404ContentLoading | before_404_load_content($file)                            |
 * | on404ContentLoaded  | after_404_load_content($file, $rawContent)                |
 * | onMetaHeaders       | before_read_file_meta($headers)                           |
 * | onMetaParsed        | file_meta($meta)                                          |
 * | onContentParsing    | before_parse_content($rawContent)                         |
 * | onContentParsed     | after_parse_content($content)                             |
 * | onContentParsed     | content_parsed($content)                                  |
 * | onSinglePageLoaded  | get_page_data($pages, $meta)                              |
 * | onPagesLoaded       | get_pages($pages, $currentPage, $previousPage, $nextPage) |
 * | onTwigRegistration  | before_twig_register()                                    |
 * | onPageRendering     | before_render($twigVariables, $twig, $templateName)       |
 * | onPageRendered      | after_render($output)                                     |
 * +---------------------+-----------------------------------------------------------+
 *
 * Since Pico 1.0 the config is stored in {@path "config/config.php"}. This
 * plugin tries to read {@path "config.php"} in Picos root dir and overwrites
 * all settings previously specified in {@path "config/config.php"}.
 *
 * @author  Daniel Rudolf
 * @link    http://picocms.org
 * @license http://opensource.org/licenses/MIT
 * @version 1.0
 */
class PicoDeprecated extends AbstractPicoPlugin
{
    /**
     * This plugin is disabled by default
     *
     * @see AbstractPicoPlugin::$enabled
     */
    protected $enabled = false;

    /**
     * The requested file
     *
     * @var string
     * @see PicoDeprecated::onRequestFile()
     */
    protected $requestFile;

    /**
     * Enables this plugin on demand and triggers the deprecated event
     * plugins_loaded()
     *
     * @see DummyPlugin::onPluginsLoaded()
     */
    public function onPluginsLoaded(&$plugins)
    {
        foreach ($plugins as $plugin) {
            if (!is_a($plugin, 'PicoPluginInterface')) {
                // the plugin doesn't implement PicoPluginInterface; it uses deprecated events
                // enable PicoDeprecated if it hasn't be explicitly enabled/disabled yet
                if (!$this->isStatusChanged()) {
                    $this->setEnabled(true, true, true);
                }
                break;
            }
        }

        if ($this->isEnabled()) {
            $this->triggerEvent('plugins_loaded');
        }
    }

    /**
     * Triggers the deprecated event config_loaded($config), tries to read
     * {@path "config.php"} in Picos root dir, enables the plugins
     * {@link PicoParsePagesContent} and {@link PicoExcerpt} and defines the
     * deprecated constants CONTENT_DIR and CONTENT_EXT
     *
     * @see DummyPlugin::onConfigLoaded()
     */
    public function onConfigLoaded(&$config)
    {
        if (file_exists(ROOT_DIR . 'config.php')) {
            // config.php in ROOT_DIR is deprecated; use CONFIG_DIR instead
            $newConfig = require(ROOT_DIR . 'config.php');
            if (is_array($newConfig)) {
                $config = $newConfig + $config;
            }
        }

        // enable PicoParsePagesContent and PicoExcerpt
        // we can't enable them during onPluginsLoaded because we can't know
        // if the user disabled us (PicoDeprecated) manually in the config
        if (isset($plugins['PicoParsePagesContent'])) {
            // parse all pages content if this plugin hasn't
            // be explicitly enabled/disabled yet
            if (!$plugins['PicoParsePagesContent']->isStatusChanged()) {
                $plugins['PicoParsePagesContent']->setEnabled(true, true, true);
            }
        }
        if (isset($plugins['PicoExcerpt'])) {
            // enable excerpt plugin if it hasn't be explicitly enabled/disabled yet
            if (!$plugins['PicoExcerpt']->isStatusChanged()) {
                $plugins['PicoExcerpt']->setEnabled(true, true, true);
            }
        }

        // CONTENT_DIR constant is deprecated since v0.9,
        // CONTENT_EXT constant since v1.0
        if (!defined('CONTENT_DIR')) {
            define('CONTENT_DIR', $config['content_dir']);
        }
        if (!defined('CONTENT_EXT')) {
            define('CONTENT_EXT', $config['content_ext']);
        }

        $this->triggerEvent('config_loaded', array(&$config));
    }

    /**
     * Triggers the deprecated event request_url($url)
     *
     * @see DummyPlugin::onRequestUrl()
     */
    public function onRequestUrl(&$url)
    {
        $this->triggerEvent('request_url', array(&$url));
    }

    /**
     * Sets {@link PicoDeprecated::$requestFile} to trigger the deprecated
     * events after_load_content() and after_404_load_content()
     *
     * @see DummyPlugin::onRequestFile()
     */
    public function onRequestFile(&$file)
    {
        $this->requestFile = &$file;
    }

    /**
     * Triggers the deprecated before_load_content($file)
     *
     * @see DummyPlugin::onContentLoading()
     */
    public function onContentLoading(&$file)
    {
        $this->triggerEvent('before_load_content', array(&$file));
    }

    /**
     * Triggers the deprecated event after_load_content($file, $rawContent)
     *
     * @see DummyPlugin::onContentLoaded()
     */
    public function onContentLoaded(&$rawContent)
    {
        $this->triggerEvent('after_load_content', array(&$this->requestFile, &$rawContent));
    }

    /**
     * Triggers the deprecated before_404_load_content($file)
     *
     * @see DummyPlugin::on404ContentLoading()
     */
    public function on404ContentLoading(&$file)
    {
        $this->triggerEvent('before_404_load_content', array(&$file));
    }

    /**
     * Triggers the deprecated event after_404_load_content($file, $rawContent)
     *
     * @see DummyPlugin::on404ContentLoaded()
     */
    public function on404ContentLoaded(&$rawContent)
    {
        $this->triggerEvent('after_404_load_content', array(&$this->requestFile, &$rawContent));
    }

    /**
     * Triggers the deprecated event before_read_file_meta($headers)
     *
     * @see DummyPlugin::onMetaHeaders()
     */
    public function onMetaHeaders(&$headers)
    {
        $this->triggerEvent('before_read_file_meta', array(&$headers));
    }

    /**
     * Triggers the deprecated event file_meta($meta)
     *
     * @see DummyPlugin::onMetaParsed()
     */
    public function onMetaParsed(&$meta)
    {
        $this->triggerEvent('file_meta', array(&$meta));
    }

    /**
     * Triggers the deprecated event before_parse_content($rawContent)
     *
     * @see DummyPlugin::onContentParsing()
     */
    public function onContentParsing(&$rawContent)
    {
        $this->triggerEvent('before_parse_content', array(&$rawContent));
    }

    /**
     * Triggers the deprecated events after_parse_content($content) and
     * content_parsed($content)
     *
     * @see DummyPlugin::onContentParsed()
     */
    public function onContentParsed(&$content)
    {
        $this->triggerEvent('after_parse_content', array(&$content));

        // deprecated since v0.8
        $this->triggerEvent('content_parsed', array(&$content));
    }

    /**
     * Triggers the deprecated event get_page_data($pages, $meta)
     *
     * @see DummyPlugin::onSinglePageLoaded()
     */
    public function onSinglePageLoaded(&$pageData)
    {
        // remove array keys
        $pages = array();
        foreach ($pageData as &$page) {
            $pages[] = &$page;
        }

        $this->triggerEvent('get_page_data', array(&$pages, $pageData['meta']));
    }

    /**
     * Triggers the deprecated event get_pages($pages, $currentPage, $previousPage, $nextPage)
     *
     * @see DummyPlugin::onPagesLoaded()
     */
    public function onPagesLoaded(&$pages, &$currentPage, &$previousPage, &$nextPage)
    {
        $this->triggerEvent('get_pages', array(&$pages, &$currentPage, &$previousPage, &$nextPage));
    }

    /**
     * Triggers the deprecated event before_twig_register()
     *
     * @see DummyPlugin::onTwigRegistration()
     */
    public function onTwigRegistration()
    {
        $this->triggerEvent('before_twig_register');
    }

    /**
     * Triggers the deprecated event before_render($twigVariables, $twig, $templateName)
     *
     * @see DummyPlugin::onPageRendering()
     */
    public function onPageRendering(&$twig, &$twigVariables, &$templateName)
    {
        // template name contains file extension since Pico 1.0
        $fileExtension = '';
        if (($fileExtensionPos = strrpos($templateName, '.')) !== false) {
            $fileExtension = substr($templateName, $fileExtensionPos);
            $templateName = substr($templateName, 0, $fileExtensionPos);
        }

        $this->triggerEvent('before_render', array(&$twigVariables, &$twig, &$templateName));

        // add original file extension
        $templateName = $templateName . $fileExtension;
    }

    /**
     * Triggers the deprecated event after_render($output)
     *
     * @see DummyPlugin::onPageRendered()
     */
    public function onPageRendered(&$output)
    {
        $this->triggerEvent('after_render', array(&$output));
    }

    /**
     * Triggers a deprecated event on all plugins
     *
     * @param  string $eventName event to trigger
     * @param  array  $params    parameters to pass
     * @return void
     */
    protected function triggerEvent($eventName, array $params = array())
    {
        foreach ($this->getPlugins() as $plugin) {
            if (method_exists($plugin, $eventName)) {
                call_user_func_array(array($plugin, $eventName), $params);
            }
        }
    }
}
