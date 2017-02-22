<?php
namespace Cycomi\Context;

/**
 * コンテキストインターフェース。
 *
 * @package Cycomi\Context
 */
interface IContext
{
    /**
     * ユーザー DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function user_transaction($function);
    /**
     * マンガ DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function manga_transaction($function);
    /**
     * ログ DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function log_transaction($function);

    /**
     * （現在の）日時を取得する。
     *
     * @return \Fuel\Core\Date 日時
     */
    public function get_date();
}
