<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once __DIR__ . '/classes/DcPhtmlBlock.php';

class Dc_Phtml extends Module implements WidgetInterface
{
    protected $templateFile;

    /** @var array per-hook instance counter for current request */
    protected static $instanceCounters = [];

    public function __construct()
    {
        $this->name = 'dc_phtml';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Design Cart';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Design Cart pHTML');
        $this->description = $this->l('Custom HTML/text blocks with advanced appearance. Multiple instances per hook.');
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];

        $this->templateFile = 'module:dc_phtml/views/templates/hook/dc_phtml.tpl';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->installTables()
            && $this->installFirstBlock();
    }

    public function uninstall()
    {
        $this->uninstallTables();
        return parent::uninstall();
    }

    protected function installTables()
    {
        $engine = _MYSQL_ENGINE_;
        $prefix = _DB_PREFIX_;

        $sql = [
            'CREATE TABLE IF NOT EXISTS `' . $prefix . 'dc_phtml_block` (
                `id_block` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_shop` INT UNSIGNED NOT NULL,
                `title_tag` VARCHAR(10) DEFAULT \'h2\',
                `bg_color` VARCHAR(32) DEFAULT \'#ffffff\',
                `title_font_size` VARCHAR(20) DEFAULT \'32px\',
                `title_color` VARCHAR(32) DEFAULT \'#111111\',
                `title_font_weight` VARCHAR(10) DEFAULT \'700\',
                `title_uppercase` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `title_align` VARCHAR(20) DEFAULT \'left\',
                `content_font_size` VARCHAR(20) DEFAULT \'16px\',
                `content_color` VARCHAR(32) DEFAULT \'#333333\',
                `content_center` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `content_uppercase` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_block`),
                KEY `id_shop` (`id_shop`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;',
            'CREATE TABLE IF NOT EXISTS `' . $prefix . 'dc_phtml_block_lang` (
                `id_block` INT UNSIGNED NOT NULL,
                `id_lang` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `content` TEXT,
                PRIMARY KEY (`id_block`,`id_lang`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;',
            'CREATE TABLE IF NOT EXISTS `' . $prefix . 'dc_phtml_hook_instance` (
                `id_hook` INT UNSIGNED NOT NULL,
                `id_shop` INT UNSIGNED NOT NULL,
                `instance_position` INT UNSIGNED NOT NULL,
                `id_block` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id_hook`,`id_shop`,`instance_position`),
                KEY `id_block` (`id_block`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;',
        ];

        foreach ($sql as $q) {
            if (!Db::getInstance()->execute($q)) {
                return false;
            }
        }
        return true;
    }

    protected function uninstallTables()
    {
        $prefix = _DB_PREFIX_;
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . $prefix . 'dc_phtml_hook_instance`')
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . $prefix . 'dc_phtml_block_lang`')
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . $prefix . 'dc_phtml_block`');
    }

    protected function installFirstBlock()
    {
        $idShop = (int) $this->context->shop->id;
        $languages = Language::getLanguages(false);
        $title = [];
        $content = [];
        foreach ($languages as $lang) {
            $title[$lang['id_lang']] = $this->l('Custom pHTML block');
            $content[$lang['id_lang']] = $this->l('Edit this content in the module configuration.');
        }
        $block = new DcPhtmlBlock();
        $block->id_shop = $idShop;
        $block->title_tag = 'h2';
        $block->bg_color = '#ffffff';
        $block->title_font_size = '32px';
        $block->title_color = '#111111';
        $block->title_font_weight = '700';
        $block->title_uppercase = false;
        $block->title_align = 'left';
        $block->content_font_size = '16px';
        $block->content_color = '#333333';
        $block->content_center = false;
        $block->content_uppercase = false;
        $block->title = $title;
        $block->content = $content;
        if (!$block->add()) {
            return false;
        }
        $idHook = (int) Hook::getIdByName('displayHome');
        if ($idHook) {
            DcPhtmlBlock::saveAssignments($idHook, $idShop, [1 => (int) $block->id_block]);
        }
        return true;
    }

    protected function processSaveBlock()
    {
        $idBlock = (int) Tools::getValue('id_block');
        $idShop = (int) $this->context->shop->id;
        $languages = Language::getLanguages(false);

        $title = [];
        $content = [];
        foreach ($languages as $lang) {
            $title[$lang['id_lang']] = Tools::getValue('DC_PHTML_TITLE_' . $lang['id_lang']);
            $content[$lang['id_lang']] = Tools::getValue('DC_PHTML_CONTENT_' . $lang['id_lang'], '', true);
        }

        if ($idBlock) {
            $block = new DcPhtmlBlock($idBlock);
            if (!Validate::isLoadedObject($block) || (int) $block->id_shop !== $idShop) {
                return $this->displayError($this->l('Block not found.'));
            }
        } else {
            $block = new DcPhtmlBlock();
            $block->id_shop = $idShop;
        }

        $block->title_tag = Tools::getValue('DC_PHTML_TITLE_TAG');
        $block->bg_color = Tools::getValue('DC_PHTML_BG_COLOR');
        $block->title_font_size = Tools::getValue('DC_PHTML_TITLE_FONT_SIZE');
        $block->title_color = Tools::getValue('DC_PHTML_TITLE_COLOR');
        $block->title_font_weight = Tools::getValue('DC_PHTML_TITLE_FONT_WEIGHT');
        $block->title_uppercase = (bool) Tools::getValue('DC_PHTML_TITLE_UPPERCASE');
        $block->title_align = Tools::getValue('DC_PHTML_TITLE_ALIGN');
        $block->content_font_size = Tools::getValue('DC_PHTML_CONTENT_FONT_SIZE');
        $block->content_color = Tools::getValue('DC_PHTML_CONTENT_COLOR');
        $block->content_center = (bool) Tools::getValue('DC_PHTML_CONTENT_CENTER');
        $block->content_uppercase = (bool) Tools::getValue('DC_PHTML_CONTENT_UPPERCASE');
        $block->title = $title;
        $block->content = $content;

        if ($block->save()) {
            $this->_clearCache($this->templateFile);
            return $this->displayConfirmation($this->l('Block saved.'));
        }
        return $this->displayError($this->l('Error saving block.'));
    }

    protected function processDeleteBlock()
    {
        $idBlock = (int) Tools::getValue('deleteBlock');
        $idShop = (int) $this->context->shop->id;
        $block = new DcPhtmlBlock($idBlock);
        if (Validate::isLoadedObject($block) && (int) $block->id_shop === $idShop) {
            $block->delete();
            Db::getInstance()->delete('dc_phtml_hook_instance', 'id_block = ' . $idBlock);
            $this->_clearCache($this->templateFile);
            return $this->displayConfirmation($this->l('Block deleted.'));
        }
        return $this->displayError($this->l('Block not found.'));
    }

    protected function processSaveAssignments()
    {
        $idShop = (int) $this->context->shop->id;
        $hooks = $this->getHooksWithModule();
        foreach ($hooks as $hookName) {
            $idHook = (int) Hook::getIdByName($hookName);
            if (!$idHook) {
                continue;
            }
            $list = $this->getInstanceCountForHook($hookName);
            $assignments = [];
            for ($i = 1; $i <= $list; $i++) {
                $assignments[$i] = (int) Tools::getValue('dc_phtml_assign_' . $idHook . '_' . $i);
            }
            DcPhtmlBlock::saveAssignments($idHook, $idShop, $assignments);
        }
        $this->_clearCache($this->templateFile);
        return $this->displayConfirmation($this->l('Instance assignments saved.'));
    }

    protected function getHooksWithModule()
    {
        $idModule = (int) $this->id;
        $rows = Db::getInstance()->executeS(
            'SELECT DISTINCT h.name FROM `' . _DB_PREFIX_ . 'hook_module` hm
             INNER JOIN `' . _DB_PREFIX_ . 'hook` h ON h.id_hook = hm.id_hook
             WHERE hm.id_module = ' . $idModule . ' AND hm.id_shop = ' . (int) $this->context->shop->id
        );
        if (!is_array($rows)) {
            return [];
        }
        return array_column($rows, 'name');
    }

    protected function getInstanceCountForHook($hookName)
    {
        $list = Hook::getHookModuleExecList($hookName);
        if (!is_array($list)) {
            return 0;
        }
        $idModule = (int) $this->id;
        $n = 0;
        foreach ($list as $item) {
            if ((int) $item['id_module'] === $idModule) {
                $n++;
            }
        }
        return $n;
    }

    protected function renderBlocksList()
    {
        $idShop = (int) $this->context->shop->id;
        $blocks = DcPhtmlBlock::getBlocksForShop($idShop);
        $currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $token = Tools::getAdminTokenLite('AdminModules');

        $html = '<div class="panel"><h3><i class="icon-list"></i> ' . $this->l('Blocks') . '</h3>';
        $html .= '<p><a class="btn btn-primary" href="' . $currentIndex . '&addBlock=1&token=' . $token . '">' . $this->l('Add block') . '</a></p>';
        if (empty($blocks)) {
            $html .= '<p>' . $this->l('No blocks yet. Add one to assign it to a hook instance.') . '</p>';
        } else {
            $html .= '<table class="table table-bordered"><thead><tr><th>ID</th><th>' . $this->l('Title') . '</th><th>' . $this->l('Actions') . '</th></tr></thead><tbody>';
            foreach ($blocks as $b) {
                $title = $b['title'] ?: ('#' . $b['id_block']);
                $html .= '<tr><td>' . (int) $b['id_block'] . '</td><td>' . htmlspecialchars($title) . '</td><td>';
                $html .= '<a class="btn btn-default" href="' . $currentIndex . '&editBlock=' . (int) $b['id_block'] . '&token=' . $token . '">' . $this->l('Edit') . '</a> ';
                $html .= '<a class="btn btn-danger" href="' . $currentIndex . '&deleteBlock=' . (int) $b['id_block'] . '&token=' . $token . '" onclick="return confirm(\'' . $this->l('Delete this block?') . '\');">' . $this->l('Delete') . '</a>';
                $html .= '</td></tr>';
            }
            $html .= '</tbody></table>';
        }
        $html .= '</div>';
        return $html;
    }

    protected function renderInstanceAssignment()
    {
        $idShop = (int) $this->context->shop->id;
        $hooks = $this->getHooksWithModule();
        $blocks = DcPhtmlBlock::getBlocksForShop($idShop);
        $currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $token = Tools::getAdminTokenLite('AdminModules');

        $html = '<div class="panel"><h3><i class="icon-link"></i> ' . $this->l('Assign blocks to hook instances') . '</h3>';
        $html .= '<p class="help-block">' . $this->l('Each time you add this module to a hook in Design > Positions, a new instance appears. Choose which block each instance displays.') . '</p>';

        $html .= '<form method="post" action="' . $currentIndex . '&token=' . $token . '">';
        $html .= '<input type="hidden" name="submitDcPhtmlAssign" value="1"/>';

        foreach ($hooks as $hookName) {
            $idHook = (int) Hook::getIdByName($hookName);
            $count = $this->getInstanceCountForHook($hookName);
            if ($count === 0) {
                continue;
            }
            $assignments = DcPhtmlBlock::getAssignmentsByHook($idHook, $idShop);
            $html .= '<h4>' . htmlspecialchars($hookName) . '</h4><table class="table"><tbody>';
            for ($pos = 1; $pos <= $count; $pos++) {
                $current = $assignments[$pos] ?? (count($blocks) ? (int) $blocks[0]['id_block'] : 0);
                $html .= '<tr><td>' . $this->l('Instance') . ' ' . $pos . '</td><td><select name="dc_phtml_assign_' . $idHook . '_' . $pos . '">';
                $html .= '<option value="0">-- ' . $this->l('None') . ' --</option>';
                foreach ($blocks as $b) {
                    $sel = ((int) $b['id_block'] === $current) ? ' selected="selected"' : '';
                    $html .= '<option value="' . (int) $b['id_block'] . '"' . $sel . '>' . htmlspecialchars($b['title'] ?: ('#' . $b['id_block'])) . '</option>';
                }
                $html .= '</select></td></tr>';
            }
            $html .= '</tbody></table>';
        }

        if (empty($hooks)) {
            $html .= '<p>' . $this->l('Add this module to a hook in Design > Positions to see instances here.') . '</p>';
        } else {
            $html .= '<button type="submit" class="btn btn-primary">' . $this->l('Save assignments') . '</button>';
        }
        $html .= '</form></div>';
        return $html;
    }

    protected function renderBlockForm()
    {
        $idBlock = (int) Tools::getValue('editBlock');
        $idShop = (int) $this->context->shop->id;
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);

        if ($idBlock) {
            $block = new DcPhtmlBlock($idBlock);
            if (!Validate::isLoadedObject($block) || (int) $block->id_shop !== $idShop) {
                return '';
            }
        } else {
            $block = new DcPhtmlBlock();
            $block->id_shop = $idShop;
            $block->title_tag = 'h2';
            $block->bg_color = '#ffffff';
            $block->title_font_size = '32px';
            $block->title_color = '#111111';
            $block->title_font_weight = '700';
            $block->title_align = 'left';
            $block->content_font_size = '16px';
            $block->content_color = '#333333';
            $langs = [];
            foreach ($languages as $l) {
                $langs[$l['id_lang']] = '';
            }
            $block->title = $langs;
            $block->content = $langs;
        }

        $contentForm = [
            'tinymce' => true,
            'legend' => ['title' => $this->l('Treści'), 'icon' => 'icon-align-left'],
            'input' => [
                ['type' => 'hidden', 'name' => 'id_block', 'value' => $idBlock ? (int) $block->id_block : 0],
                ['type' => 'text', 'label' => $this->l('Module title'), 'name' => 'DC_PHTML_TITLE', 'lang' => true],
                ['type' => 'select', 'label' => $this->l('Title HTML tag'), 'name' => 'DC_PHTML_TITLE_TAG',
                    'options' => ['query' => [
                        ['id' => 'h1', 'name' => 'h1'], ['id' => 'h2', 'name' => 'h2'], ['id' => 'h3', 'name' => 'h3'],
                        ['id' => 'h4', 'name' => 'h4'], ['id' => 'h5', 'name' => 'h5'], ['id' => 'h6', 'name' => 'h6'],
                    ], 'id' => 'id', 'name' => 'name']],
                ['type' => 'textarea', 'label' => $this->l('Content'), 'name' => 'DC_PHTML_CONTENT', 'autoload_rte' => true, 'lang' => true, 'cols' => 60, 'rows' => 10, 'class' => 'rte'],
            ],
        ];
        $appearanceForm = [
            'legend' => ['title' => $this->l('Wygląd'), 'icon' => 'icon-eye'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Background color'), 'name' => 'DC_PHTML_BG_COLOR'],
                ['type' => 'text', 'label' => $this->l('Title font size'), 'name' => 'DC_PHTML_TITLE_FONT_SIZE'],
                ['type' => 'text', 'label' => $this->l('Title color'), 'name' => 'DC_PHTML_TITLE_COLOR'],
                ['type' => 'select', 'label' => $this->l('Title font weight'), 'name' => 'DC_PHTML_TITLE_FONT_WEIGHT',
                    'options' => ['query' => array_map(function ($w) { return ['id' => (string)$w, 'name' => (string)$w]; }, [200,300,400,500,600,700,800,900]), 'id' => 'id', 'name' => 'name']],
                ['type' => 'switch', 'label' => $this->l('Title uppercase'), 'name' => 'DC_PHTML_TITLE_UPPERCASE', 'is_bool' => true,
                    'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                ['type' => 'select', 'label' => $this->l('Title alignment'), 'name' => 'DC_PHTML_TITLE_ALIGN',
                    'options' => ['query' => [['id' => 'left', 'name' => $this->l('Left')], ['id' => 'center', 'name' => $this->l('Center')], ['id' => 'right', 'name' => $this->l('Right')]], 'id' => 'id', 'name' => 'name']],
                ['type' => 'text', 'label' => $this->l('Content font size'), 'name' => 'DC_PHTML_CONTENT_FONT_SIZE'],
                ['type' => 'text', 'label' => $this->l('Content color'), 'name' => 'DC_PHTML_CONTENT_COLOR'],
                ['type' => 'switch', 'label' => $this->l('Content centered'), 'name' => 'DC_PHTML_CONTENT_CENTER', 'is_bool' => true,
                    'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                ['type' => 'switch', 'label' => $this->l('Content uppercase'), 'name' => 'DC_PHTML_CONTENT_UPPERCASE', 'is_bool' => true,
                    'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
            ],
            'submit' => ['title' => $this->l('Save')],
        ];

        $helperLanguages = [];
        foreach ($languages as $lang) {
            $helperLanguages[] = [
                'id_lang' => (int) $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => (int) ($lang['id_lang'] == $defaultLang),
            ];
        }

        $values = [
            'id_block' => $block->id_block,
            'DC_PHTML_TITLE' => $block->title,
            'DC_PHTML_TITLE_TAG' => $block->title_tag,
            'DC_PHTML_CONTENT' => $block->content,
            'DC_PHTML_BG_COLOR' => $block->bg_color,
            'DC_PHTML_TITLE_FONT_SIZE' => $block->title_font_size,
            'DC_PHTML_TITLE_COLOR' => $block->title_color,
            'DC_PHTML_TITLE_FONT_WEIGHT' => $block->title_font_weight,
            'DC_PHTML_TITLE_UPPERCASE' => (int) $block->title_uppercase,
            'DC_PHTML_TITLE_ALIGN' => $block->title_align,
            'DC_PHTML_CONTENT_FONT_SIZE' => $block->content_font_size,
            'DC_PHTML_CONTENT_COLOR' => $block->content_color,
            'DC_PHTML_CONTENT_CENTER' => (int) $block->content_center,
            'DC_PHTML_CONTENT_UPPERCASE' => (int) $block->content_uppercase,
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->submit_action = 'submitDcPhtmlBlock';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $helperLanguages;
        $helper->tpl_vars = ['fields_value' => $values, 'languages' => $helperLanguages, 'id_language' => $this->context->language->id];

        return $helper->generateForm([['form' => $contentForm], ['form' => $appearanceForm]]);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitDcPhtmlBlock')) {
            $output .= $this->processSaveBlock();
        } elseif (Tools::isSubmit('submitDcPhtmlAssign')) {
            $output .= $this->processSaveAssignments();
        } elseif (Tools::getValue('deleteBlock')) {
            $output .= $this->processDeleteBlock();
        }

        if (Tools::getValue('addBlock') || Tools::getValue('editBlock')) {
            $backUrl = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
            $output .= '<p><a class="btn btn-default" href="' . $backUrl . '"><i class="icon-arrow-left"></i> ' . $this->l('Back to list') . '</a></p>';
            $output .= $this->renderBlockForm();
        } else {
            $output .= $this->renderBlocksList();
            $output .= $this->renderInstanceAssignment();
        }
        return $output;
    }

    protected function getInstanceIndex($hookName)
    {
        if (!isset(self::$instanceCounters[$hookName])) {
            self::$instanceCounters[$hookName] = 0;
        }
        self::$instanceCounters[$hookName]++;
        return self::$instanceCounters[$hookName];
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $instanceIndex = $this->getInstanceIndex($hookName);
        $idHook = (int) Hook::getIdByName($hookName);
        $idShop = (int) $this->context->shop->id;
        $idBlock = $idHook ? DcPhtmlBlock::getIdBlockByInstance($idHook, $idShop, $instanceIndex) : null;

        if (!$idBlock) {
            return '';
        }

        $cacheId = 'dc_phtml_' . $idBlock;
        if (!$this->isCached($this->templateFile, $this->getCacheId($cacheId))) {
            $vars = $this->getWidgetVariablesForBlock($idBlock);
            if ($vars === false) {
                return '';
            }
            $this->smarty->assign($vars);
        }
        return $this->fetch($this->templateFile, $this->getCacheId($cacheId));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return [];
    }

    protected function getWidgetVariablesForBlock($idBlock)
    {
        $block = new DcPhtmlBlock($idBlock);
        if (!Validate::isLoadedObject($block)) {
            return false;
        }
        $idLang = (int) $this->context->language->id;
        $title = is_array($block->title) && isset($block->title[$idLang]) ? $block->title[$idLang] : (is_array($block->title) ? reset($block->title) : (string) $block->title);
        $content = is_array($block->content) && isset($block->content[$idLang]) ? $block->content[$idLang] : (is_array($block->content) ? reset($block->content) : (string) $block->content);

        return [
            'dc_phtml_title' => $title,
            'dc_phtml_title_tag' => $block->title_tag,
            'dc_phtml_content' => $content,
            'dc_phtml_bg_color' => $block->bg_color,
            'dc_phtml_title_font_size' => $block->title_font_size,
            'dc_phtml_title_color' => $block->title_color,
            'dc_phtml_title_font_weight' => $block->title_font_weight,
            'dc_phtml_title_uppercase' => (bool) $block->title_uppercase,
            'dc_phtml_title_align' => $block->title_align,
            'dc_phtml_content_font_size' => $block->content_font_size,
            'dc_phtml_content_color' => $block->content_color,
            'dc_phtml_content_center' => (bool) $block->content_center,
            'dc_phtml_content_uppercase' => (bool) $block->content_uppercase,
        ];
    }
}
