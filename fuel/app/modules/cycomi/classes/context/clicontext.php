<?php

namespace Cycomi\Context;

use \Fuel\Core\Date;

/**
 * CLI 用コンテキストを扱うクラス。
 *
 * @package Cycomi\Context
 */
class CliContext extends AbstractContext
{
    /**
     * （現在の）日時を取得する。
     *
     * @return \Fuel\Core\Date 日時
     */
    public function get_date()
    {
        return new Date();
    }
}
