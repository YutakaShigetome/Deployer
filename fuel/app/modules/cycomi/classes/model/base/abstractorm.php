<?php
namespace Cycomi\Model\Base;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use \Fuel\Core\Model;
use \Fuel\Core\Arr;
use \Fuel\Core\Database_Connection;
use \Cycomi\Context\Container as ContextContainer;

abstract class AbstractOrm extends Model
{
    protected static $_write_connection;
    protected static $_connection;
    protected static $_table_name;
    protected static $_columns;
    protected static $_primary_key = ['id'];
    protected static $_created_at = 'created_at';
    protected static $_updated_at = 'updated_at';

    /**
     * @var array テーブル定義オブジェクト
     */
    protected static $_table_definition_map = [];
    /**
     * @var array クエリビルダー
     */
    protected static $_query_builder = [];

    /**
     * 必要であれば初期化しつつ、テーブル定義オブジェクトを取得する。
     * @return TableDefinition テーブル定義オブジェクト
     */
    protected static function _get_table_definition()
    {
        if (!array_key_exists(static::class, static::$_table_definition_map)) {
            static::$_table_definition_map[static::class]
                =  new TableDefinition(static::$_table_name, static::$_columns);
        }
        return static::$_table_definition_map[static::class];
    }
    /**
     * 必要であれば初期化しつつ、クエリビルダーを取得する。
     * @return QueryBuilder クエリビルダー
     */
    protected static function _get_query_builder()
    {
        if (!array_key_exists(static::class, static::$_query_builder)) {
            static::$_query_builder[static::class] = new QueryBuilder(static::$_table_name);
        }
        return static::$_query_builder[static::class];
    }

    /**
     * カラム名を取得する。
     *
     * @return \string[] カラム名配列
     */
    protected static function _get_column_names()
    {
        return static::_get_table_definition()->get_column_names();
    }

    /**
     * バリデーションする。
     *
     * @param $column_map array[] カラム連想配列
     * @return ValidationError|null バリデーションエラー
     */
    public static function validate($column_map)
    {
        return static::_get_table_definition()->validate($column_map);
    }

    /**
     * インスタンス化したモデル配列から一つ取り出す。
     *
     * @param array $models インスタンス化したモデル配列
     * @return self|null インスタンス化したモデル
     */
    protected static function _pickup_one(array $models)
    {
        return count($models) > 0 ? $models[0] : null;
    }

    /**
     * レコード PK 参照により単体インスタンス化する。
     *
     * @param $pk_map array PK 連想配列
     * @return self|null インスタンス化したモデル
     */
    protected static function _find_one_by_pk($pk_map)
    {
        return static::_pickup_one(static::_find_by_pks([$pk_map]));
    }
    /**
     * 最新レコード PK 参照により単体インスタンス化する。
     *
     * @param $pk_map array PK 連想配列
     * @return self|null インスタンス化したモデル
     */
    protected static function _find_latest_one_by_pk($pk_map)
    {
        return static::_pickup_one(static::_find_latest_by_pks([$pk_map]));
    }
    /**
     * ロック付きレコード PK 参照により単体インスタンス化する。
     *
     * @param $pk_map array PK 連想配列
     * @return self|null インスタンス化したモデル
     */
    protected static function _find_locked_one_by_pk($pk_map)
    {
        return static::_pickup_one(static::_find_locked_by_pks([$pk_map]));
    }
    /**
     * レコード PK 参照によりインスタンス化する。
     *
     * @param $pk_maps array[] PK 連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_by_pks($pk_maps)
    {
        static::_check_pks($pk_maps);
        return static::_find_by_column_maps($pk_maps);
    }
    /**
     * 最新レコード PK 参照によりインスタンス化する。
     *
     * @param $pk_maps array[] PK 連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_latest_by_pks(array $pk_maps)
    {
        static::_check_pks($pk_maps);
        return static::_find_latest_by_column_maps($pk_maps);
    }
    /**
     * ロック付きレコード PK 参照によりインスタンス化する。
     *
     * @param $pk_maps array[] PK 連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_locked_by_pks($pk_maps)
    {
        static::_check_pks($pk_maps);
        return static::_find_locked_by_column_maps($pk_maps);
    }

    /**
     * レコード参照により単体インスタンス化する。
     *
     * @param array $where_column_map 参照条件に利用するカラム情報の連想配列
     * @return self|null インスタンス化したモデル
     */
    protected static function _find_one($where_column_map)
    {
        return static::_pickup_one(static::_find($where_column_map));
    }
    /**
     * レコード参照によりインスタンス化する。
     *
     * @param array $where_column_map 参照条件に利用するカラム情報の連想配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find($where_column_map)
    {
        return static::_find_by_column_maps([$where_column_map]);
    }

    /**
     * レコード参照によりインスタンス化する。
     *
     * @param array $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_by_column_maps($where_column_maps)
    {
        list($query, $connection) = static::_get_select_query_and_connection($where_column_maps);
        return static::_instantiate_models_by_query_and_connection($query, $connection);
    }
    /**
     * 最新レコード参照によりインスタンス化する。
     *
     * @param $where_column_maps array[] 参照条件に利用するカラム情報の連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_latest_by_column_maps($where_column_maps)
    {
        list($query, $connection) = static::_get_select_latest_query_and_connection($where_column_maps);
        return static::_instantiate_models_by_query_and_connection($query, $connection);
    }
    /**
     * ロック付きレコード参照によりインスタンス化する。
     *
     * @param $where_column_maps array[] 参照条件に利用するカラム情報の連想配列の配列
     * @return self[] インスタンス化したモデル配列
     */
    protected static function _find_locked_by_column_maps($where_column_maps)
    {
        list($query, $connection) = static::_get_select_locked_query_and_connection($where_column_maps);
        $models = static::_instantiate_models_by_query_and_connection($query, $connection);
        foreach ($models as $model) {
            $model->_lock();
        }
        return $models;
    }

    /**
     * クエリとコネクションでインスタンス化する。
     *
     * @param \Fuel\Core\Database_Query $query クエリ
     * @param \Fuel\Core\Database_Connection $connection コネクション
     * @return self[] インスタンス化したモデル配列
     */
    private static function _instantiate_models_by_query_and_connection($query, $connection)
    {
        return static::_instantiate_models_by_records($query->execute($connection)->as_array());
    }
    /**
     * レコード内容でインスタンス化する。
     *
     * @param array $records レコード内容
     * @return self[] インスタンス化したモデル配列
     */
    private static function _instantiate_models_by_records(array $records)
    {
        return array_map(\Closure::bind(static function ($record) {
            return new static($record);
        }, null, static::class), $records);
    }
    /**
     * レコード参照クエリとコネクションを取得。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return array [クエリ, コネクション]
     */
    private static function _get_select_query_and_connection($where_column_maps)
    {
        return [
            static::_get_query_builder()->build_select_query($where_column_maps, false),
            static::_get_slave_connection()
        ];
    }
    /**
     * 最新レコード参照クエリとコネクションを取得。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return array [クエリ, コネクション]
     */
    private static function _get_select_latest_query_and_connection($where_column_maps)
    {
        return [
            static::_get_query_builder()->build_select_query($where_column_maps, false),
            static::_get_master_connection()
        ];
    }
    /**
     * ロック付きレコード参照クエリとコネクションを取得。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return array [クエリ, コネクション]
     */
    private static function _get_select_locked_query_and_connection($where_column_maps)
    {
        return [
            static::_get_query_builder()->build_select_query($where_column_maps, true),
            static::_get_master_connection()
        ];
    }

    /**
     * レコードを作成しつつ、インスタンス化する。
     *
     * @param array $column_map カラム連想配列
     * @return self|null
     */
    public static function create($column_map)
    {
        // 自動タイムスタンプ。強制的に付ける。
        if (isset(static::$_created_at) && in_array(static::$_created_at, static::_get_column_names())) {
            $column_map[static::$_created_at] = ContextContainer::get()->get_date();
        }
        if (static::validate($column_map) !== null) {
            throw new InvalidArgumentException('column_map');
        }
        // デフレート。
        $deflated_column_map = [];
        foreach ($column_map as $column_name => $column_value) {
            $deflated_column_map[$column_name] = static::_get_table_definition()->deflate($column_name, $column_value);
        }
        $pk_map = static::_extract_pk_map_from_column_map($deflated_column_map);
        $query = static::_get_query_builder()->build_insert_query([$deflated_column_map], false);
        list($insert_id, $rows_affected) = $query->execute(static::_get_master_connection());
        if ($rows_affected === 0) {
            return null;
        }
        // 念のため参照し直す。
        return static::_find_locked_one_by_pk($pk_map);
    }

    /**
     * 連想配列の配列が PK 連想配列の配列かどうかをチェックする。
     *
     * @param array $maps チェック対象連想配列
     */
    private static function _check_pks(array $maps)
    {
        if (!(!Arr::is_assoc($maps) && count($maps) > 0)) {
            throw new \InvalidArgumentException('pk_maps');
        }
        foreach ($maps as $pk_map) {
            if (!static::_is_pk($pk_map)) {
                throw new \InvalidArgumentException('pk_maps');
            }
        }
    }
    /**
     * 連想配列が PK 連想配列かどうかをチェックする。
     *
     * @param array $map チェック対象連想配列
     * @return bool PK か否か
     */
    private static function _is_pk(array $map)
    {
        if (!Arr::is_assoc($map)) {
            throw new InvalidArgumentException('column_map');
        }
        if (!(count(array_keys($map)) === count(static::$_primary_key))) {
            throw new InvalidArgumentException('column_map');
        }
        $pk_map = Arr::filter_keys($map, static::$_primary_key);
        return count(array_keys($pk_map)) === count(static::$_primary_key);
    }
    /**
     * カラム連想配列から PK 連想配列を取り出す。
     *
     * @param array $column_map カラム連想配列
     * @return array PK 連想配列
     */
    private static function _extract_pk_map_from_column_map(array $column_map)
    {
        if (!Arr::is_assoc($column_map)) {
            throw new InvalidArgumentException('column_map');
        }
        $pk_map = Arr::filter_keys($column_map, static::$_primary_key);
        if (count(array_keys($pk_map)) != count(static::$_primary_key)) {
            throw new InvalidArgumentException('column_map');
        }
        return $pk_map;
    }

    /**
     * マスター接続を取得する。
     *
     * @return \Fuel\Core\Database_Connection DB接続
     */
    protected static function _get_master_connection()
    {
        return Database_Connection::instance(static::$_write_connection);
    }
    /**
     * スレーブ接続を取得する。
     *
     * @return \Fuel\Core\Database_Connection SB接続
     */
    protected static function _get_slave_connection()
    {
        return Database_Connection::instance(static::$_connection);
    }


    /**
     * @var array $_record レコード対応データコンテナ
     */
    protected $_record;
    /**
     * @var bool $_with_lock ロック有無
     */
    private $_with_lock = false;

    /**
     * コンストラクタ。
     *
     * @param array $record レコード内容
     */
    protected function __construct(array $record)
    {
        if (!Arr::is_assoc($record)) {
            throw new InvalidArgumentException('$record');
        }
        $valid_record = Arr::filter_keys($record, static::_get_column_names());
        if (!(count(array_keys($record)) === count(array_keys($valid_record)))) {
            throw new \InvalidArgumentException('$record');
        }
        $this->_record = $valid_record;
    }

    /**
     * ロックを掛ける。
     */
    private function _lock()
    {
        $this->_with_lock = true;
    }
    /**
     * ゲッター。
     *
     * @param string $column_name カラム名
     * @return mixed
     */
    protected function _get($column_name)
    {
        if (!(is_string($column_name) && strlen($column_name) > 0)) {
            throw new \InvalidArgumentException('column_name');
        }
        if (isset($this->_record) && in_array($column_name, static::_get_column_names())) {
            return static::_get_table_definition()->inflate($column_name, $this->_record[$column_name]);
        }
        throw new \InvalidArgumentException('column_name');
    }
    /**
     * セッター。
     *
     * @param array $column_map セットするカラム連想配列。
     */
    protected function _set(array $column_map)
    {
        if (!Arr::is_assoc($column_map)) {
            throw new InvalidArgumentException('column_map');
        }
        $valid_column_map = Arr::filter_keys(
            Arr::filter_keys($column_map, static::_get_column_names()),
            static::$_primary_key,
            true
        );
        if (!(count(array_keys($column_map)) === count(array_keys($valid_column_map)))) {
            throw new \InvalidArgumentException('$column_map');
        }
        if (static::validate($column_map) !== null) {
            throw new InvalidArgumentException('column_map');
        }
        // デフレート。
        $deflated_column_map = [];
        foreach ($column_map as $column_name => $column_value) {
            $deflated_column_map[$column_name] = static::_get_table_definition()->deflate($column_name, $column_value);
        }
        foreach ($deflated_column_map as $key => $value) {
            $this->_record[$key] = $value;
        }
    }

    /**
     * 更新。
     *
     * @return bool 成功したかどうか
     */
    public function update()
    {
        if (!$this->_with_lock) {
            throw new \LogicException('Without Lock');
        }
        // 自動タイムスタンプ。強制的に付ける。
        if (isset(static::$_updated_at) && in_array(static::$_updated_at, static::_get_column_names())) {
            $this->_set([
                static::$_updated_at => ContextContainer::get()->get_date(),
            ]);
        }
        // PK を除いて更新データを作成。
        $column_map = Arr::filter_keys(
            Arr::filter_keys($this->_record, static::_get_column_names()),
            static::$_primary_key,
            true
        );
        $query = static::_get_query_builder()->build_update_query([$this->_get_pk_map()], $column_map);
        $result = $query->execute(static::_get_master_connection());
        return $result > 0;
    }

    /**
     * 削除。
     *
     * @return bool 成功したかどうか
     */
    public function delete()
    {
        if (!$this->_with_lock) {
            throw new \LogicException('Without Lock');
        }
        $query = static::_get_query_builder()->build_delete_query([$this->_get_pk_map()]);
        $result = $query->execute(static::_get_master_connection());
        return $result > 0;
    }

    /**
     * PK 連想配列を取得する。
     *
     * @return array PK 連想配列
     */
    private function _get_pk_map()
    {
        $pk_value = [];
        foreach (static::$_primary_key as $pk_key) {
            $pk_value[$pk_key] = $this->_get($pk_key);
        }
        return $pk_value;
    }

    /**
     * 連想配列変換。
     *
     * @return array 連想配列
     */
    public function to_array()
    {
        $array = [];
        foreach (static::_get_column_names() as $column_name) {
            $array[$column_name] = $this->_get($column_name);
        }
        return $array;
    }
    /**
     * オブジェクトを含まない連想配列変換。
     *
     * @return array 連想配列
     */
    public function to_primitive_array()
    {
        $array = [];
        foreach (static::_get_column_names() as $column_name) {
            $type = static::_get_table_definition()->get_column_type($column_name);
            switch ($type) {
                case 'date':
                    $array[$column_name] = $this->_get($column_name)->get_timestamp();
                    break;
                case 'timestamp':
                    $array[$column_name] = $this->_get($column_name)->get_timestamp();
                    break;
                default:
                    $array[$column_name] = $this->_get($column_name);
                    break;
            }
        }
        return $array;
    }
}
