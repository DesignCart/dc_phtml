<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DcPhtmlBlock extends ObjectModel
{
    /** @var int */
    public $id_block;

    /** @var int */
    public $id_shop;

    /** @var string */
    public $title_tag;

    /** @var string */
    public $bg_color;

    /** @var string */
    public $title_font_size;

    /** @var string */
    public $title_color;

    /** @var string */
    public $title_font_weight;

    /** @var bool */
    public $title_uppercase;

    /** @var string */
    public $title_align;

    /** @var string */
    public $content_font_size;

    /** @var string */
    public $content_color;

    /** @var bool */
    public $content_center;

    /** @var bool */
    public $content_uppercase;

    /** @var array */
    public $title;

    /** @var array */
    public $content;

    public static $definition = [
        'table' => 'dc_phtml_block',
        'primary' => 'id_block',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'title_tag' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 10],
            'bg_color' => ['type' => self::TYPE_STRING, 'size' => 64],
            'title_font_size' => ['type' => self::TYPE_STRING, 'size' => 20],
            'title_color' => ['type' => self::TYPE_STRING, 'size' => 64],
            'title_font_weight' => ['type' => self::TYPE_STRING, 'size' => 10],
            'title_uppercase' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'title_align' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20],
            'content_font_size' => ['type' => self::TYPE_STRING, 'size' => 20],
            'content_color' => ['type' => self::TYPE_STRING, 'size' => 64],
            'content_center' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'content_uppercase' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'content' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
        ],
    ];

    /**
     * @param int $idHook
     * @param int $idShop
     * @param int $instancePosition 1-based
     * @return int|null id_block or null
     */
    public static function getIdBlockByInstance($idHook, $idShop, $instancePosition)
    {
        $id = (int) Db::getInstance()->getValue(
            'SELECT id_block FROM `' . _DB_PREFIX_ . 'dc_phtml_hook_instance` 
             WHERE id_hook = ' . (int) $idHook . ' AND id_shop = ' . (int) $idShop . ' 
             AND instance_position = ' . (int) $instancePosition
        );

        return $id ? (int) $id : null;
    }

    /**
     * @param int $idHook
     * @param int $idShop
     * @return array [instance_position => id_block, ...]
     */
    public static function getAssignmentsByHook($idHook, $idShop)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT instance_position, id_block FROM `' . _DB_PREFIX_ . 'dc_phtml_hook_instance` 
             WHERE id_hook = ' . (int) $idHook . ' AND id_shop = ' . (int) $idShop . ' 
             ORDER BY instance_position'
        );
        $out = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $out[(int) $row['instance_position']] = (int) $row['id_block'];
            }
        }
        return $out;
    }

    /**
     * @param int $idHook
     * @param int $idShop
     * @param array $assignments [instance_position => id_block, ...]
     */
    public static function saveAssignments($idHook, $idShop, array $assignments)
    {
        $db = Db::getInstance();
        $db->delete('dc_phtml_hook_instance', 'id_hook = ' . (int) $idHook . ' AND id_shop = ' . (int) $idShop);
        foreach ($assignments as $pos => $idBlock) {
            $db->insert('dc_phtml_hook_instance', [
                'id_hook' => (int) $idHook,
                'id_shop' => (int) $idShop,
                'instance_position' => (int) $pos,
                'id_block' => (int) $idBlock,
            ]);
        }
    }

    /**
     * @param int $idShop
     * @return array list of blocks for dropdown
     */
    public static function getBlocksForShop($idShop)
    {
        $idLang = (int) Context::getContext()->language->id;
        $rows = Db::getInstance()->executeS(
            'SELECT b.id_block, l.title 
             FROM `' . _DB_PREFIX_ . 'dc_phtml_block` b
             LEFT JOIN `' . _DB_PREFIX_ . 'dc_phtml_block_lang` l ON l.id_block = b.id_block AND l.id_lang = ' . $idLang . '
             WHERE b.id_shop = ' . (int) $idShop . '
             ORDER BY b.id_block'
        );
        return is_array($rows) ? $rows : [];
    }
}
