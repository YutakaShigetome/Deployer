<?php
namespace Cycomi\Context;

/**
 * コンテキスト格納クラス。
 *
 * @package Cycomi\Context
 */
class Container
{
    /** @var IContext コンテキスト */
    private static $_context;


    /**
     * コンテキストを取得。
     *
     * @return IContext コンテキスト
     */
    public static function get()
    {
        return static::$_context;
    }

    /**
     * コンテキストをセット。
     *
     * @param IContext $context コンテキスト
     */
    public static function set(IContext $context)
    {
        static::$_context = $context;
    }
}
