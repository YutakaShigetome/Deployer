<?php
namespace Cycomi\Context;

use \Fuel\Core\Config;
use \Fuel\Core\Date;
use \Fuel\Core\DB;

/**
 * コンテキスト抽象クラス。
 *
 * @package Cycomi\Context
 */
abstract class AbstractContext implements IContext
{
    /** @var string ユーザー DB を識別するための文字列 */
    private $_user_db_type = 'user';
    /** @var string マンガ DB を識別するための文字列 */
    private $_manga_db_type = 'manga';
    /** @var string ログ DB を識別するための文字列 */
    private $_log_db_type = 'log';

    /** @var \Fuel\Core\Date 初期化時の日時 */
    protected $_date_at_initialization;


    /**
     * コンストラクタ。
     */
    public function __construct()
    {
        $this->_date_at_initialization = new Date();
    }

    /**
     * （現在の）日時を取得する。
     *
     * @return \Fuel\Core\Date 日時
     */
    abstract public function get_date();

    /**
     * 日時オフセットをセットすることで、取得できる（現在の）日時をずらす。
     *
     * @param int $offset 日時オフセット
     */
    public function set_date_offset($offset)
    {
        if (!is_int($offset)) {
            throw new \InvalidArgumentException('offset');
        }
        $previous_offset = Config::get('server_gmt_offset', 0);
        Config::set('server_gmt_offset', $offset);
        // 初期化時に作成した Date オブジェクトは作り直し。
        if (isset($this->_date_at_initialization)) {
            $this->_date_at_initialization
                = new Date($this->_date_at_initialization->get_timestamp() + $offset - $previous_offset);
        }
    }

    /**
     * ユーザー DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function user_transaction($function)
    {
        return $this->_transaction($this->_user_db_type, $function);
    }
    /**
     * マンガ DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function manga_transaction($function)
    {
        return $this->_transaction($this->_manga_db_type, $function);
    }
    /**
     * ログ DB におけるトランザクション処理を実行する。
     *
     * @param callback $function トランザクション処理。
     * @return mixed トランザクション処理の返り値
     */
    public function log_transaction($function)
    {
        return $this->_transaction($this->_log_db_type, $function);
    }

    /**
     * トランザクション処理を実行する。
     *
     * @param string $db_type DB を識別するための文字列
     * @param callable $function トランザクション処理
     * @return mixed トランザクション処理の返り値
     * @throws \Exception 例外
     */
    private function _transaction($db_type, callable $function)
    {
        if (!(is_string($db_type) && strlen($db_type) > 0)) {
            throw new \InvalidArgumentException('db_type');
        }
        /** @var \Fuel\Core\Database_Connection $db */
        $db = DB::instance($db_type . '_master');
        $db->start_transaction();
        try {
            return $function($db);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $db->rollback_transaction();
        }
    }
}
