<?php
namespace Cycomi\Model\Base;

use \Fuel\Core\DB;
use \Fuel\Core\Arr;

class QueryBuilder
{
    /**
     * @var string $_table_name テーブル名
     */
    private $_table_name;
    /**
     * @var string $_suffix_separator サフィックスを付ける際のセパレーター
     */
    private $_suffix_separator;


    /**
     * コンストラクタ。
     *
     * @param string $table_name テーブル名
     * @param string $suffix_separator レコード対応データ
     */
    public function __construct($table_name, $suffix_separator = '__')
    {
        if (!(is_string($table_name) && strlen($table_name) > 0)) {
            throw new \InvalidArgumentException('table_name');
        }
        if (!(is_string($suffix_separator) && strlen($suffix_separator) > 0)) {
            throw new \InvalidArgumentException('suffix_separator');
        }
        $this->_table_name = $table_name;
        $this->_suffix_separator = $suffix_separator;
    }


    /**
     * INSERT クエリを組み立てる。
     *
     * @param array[] $column_maps カラム情報の連想配列（カラム名 => カラム値）の配列
     * @param bool $throw_duplicate_entry_exception Duplicate Entry 例外を投げるかどうか
     * @return \Fuel\Core\Database_Query INSERT クエリ
     */
    public function build_insert_query($column_maps, $throw_duplicate_entry_exception)
    {
        list($statement, $bind_param)
            = $this->_get_insert_statement_and_bind_param($column_maps, $throw_duplicate_entry_exception);
        return DB::query($statement, DB::INSERT)->parameters($bind_param);
    }
    /**
     * SELECT クエリを組み立てる。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @param bool $with_lock 排他ロック有無
     * @return \Fuel\Core\Database_Query SELECT クエリ
     */
    public function build_select_query($where_column_maps, $with_lock)
    {
        list($statement, $bind_param) = $this->_get_select_statement_and_bind_param($where_column_maps, $with_lock);
        return DB::query($statement, DB::SELECT)->parameters($bind_param);
    }
    /**
     * UPDATE クエリを組み立てる。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @param array $set_column_map 更新カラム情報の連想配列（カラム名 => カラム値）
     * @return \Fuel\Core\Database_Query UPDATE クエリ
     */
    public function build_update_query($where_column_maps, $set_column_map)
    {
        list($statement, $bind_param)
            = $this->_get_update_statement_and_bind_param($where_column_maps, $set_column_map);
        return DB::query($statement, DB::UPDATE)->parameters($bind_param);
    }
    /**
     * DELETE クエリを組み立てる。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return \Fuel\Core\Database_Query DELETE クエリ
     */
    public function build_delete_query($where_column_maps)
    {
        list($statement, $bind_param) = $this->_get_delete_statement_and_bind_param($where_column_maps);
        return DB::query($statement, DB::DELETE)->parameters($bind_param);
    }

    /**
     * INSERT 文と対応するバインドパラメータを取得する。
     *
     * @param array[] $column_maps カラム情報の連想配列（カラム名 => カラム値）の配列
     * @param bool $throw_duplicate_entry_exception Duplicate Entry 例外を投げるかどうか
     * @return mixed[] INSERT 文とバインドパラメータ
     */
    private function _get_insert_statement_and_bind_param(array $column_maps, $throw_duplicate_entry_exception)
    {
        if (!is_bool($throw_duplicate_entry_exception)) {
            throw new \InvalidArgumentException('throw_duplicate_entry_exception');
        }

        $column_names = $this->_validate_column_maps_and_get_column_names($column_maps);

        $column_maps_count = count($column_maps);
        $rows = [];
        for ($i = 1; $i <= $column_maps_count; $i++) {
            $suffix = "$i";
            $place_holders = [];
            foreach ($column_names as $column_name) {
                $place_holder_name = $this->_add_suffix_to_column_name($column_name, $suffix);
                array_push($place_holders, ":$place_holder_name");
            }
            array_push($rows, join(', ', $place_holders));
        }
        $table_name = $this->_table_name;
        $ignore_statement = $throw_duplicate_entry_exception ? '' : ' IGNORE';
        $column_statement = '(' . join(', ', $column_names) . ')';
        $values_statement = 'VALUES (' . join('), (', $rows) . ')';
        $insert_statement = "INSERT$ignore_statement INTO `$table_name` $column_statement $values_statement";
        $bind_param = $this->_get_bind_param_by_column_maps($column_maps);
        return [$insert_statement, $bind_param];
    }
    /**
     * SELECT 文と対応するバインドパラメータを取得する。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @param bool $with_lock 排他ロック有無
     * @return mixed[] SELECT 文とバインドパラメータ
     */
    private function _get_select_statement_and_bind_param($where_column_maps, $with_lock)
    {
        if (!is_bool($with_lock)) {
            throw new \InvalidArgumentException('with_lock');
        }
        list($where_statement, $bind_param) = $this->_get_where_statement_and_bind_param($where_column_maps);
        $table_name = $this->_table_name;
        $select_statement = "SELECT * FROM `$table_name` $where_statement";
        return [$with_lock ? "$select_statement FOR UPDATE" : $select_statement, $bind_param];
    }
    /**
     * UPDATE 文と対応するバインドパラメータを取得する。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @param array $set_column_map 更新カラム情報の連想配列（カラム名 => カラム値）
     * @return mixed[] UPDATE 文とバインドパラメータ
     */
    private function _get_update_statement_and_bind_param($where_column_maps, array $set_column_map)
    {
        if (!(\Arr::is_assoc($set_column_map) && count(array_keys($set_column_map)) > 0)) {
            throw new \InvalidArgumentException('set_column_map');
        }
        $table_name = $this->_table_name;
        list($where_statement, $bind_param) = $this->_get_where_statement_and_bind_param($where_column_maps);
        $set_column_names = array_keys($set_column_map);
        $set_statement = $this->_get_set_statement($set_column_names);
        $bind_param += $this->_get_bind_param_by_column_map($set_column_map, null);
        return ["UPDATE `$table_name` $set_statement $where_statement", $bind_param];
    }
    /**
     * DELETE 文と対応するバインドパラメータを取得する。
     *
     * @param array[] $where_column_maps 参照条件に利用するカラム情報の連想配列の配列
     * @return mixed[] DELETE 文とバインドパラメータ
     */
    private function _get_delete_statement_and_bind_param($where_column_maps)
    {
        $table_name = $this->_table_name;
        list($where_statement, $bind_param) = $this->_get_where_statement_and_bind_param($where_column_maps);
        return ["DELETE FROM `$table_name` $where_statement", $bind_param];
    }
    /**
     * WHERE 句と対応するバインドパラメータを取得する。
     *
     * @param array[] $column_maps カラム情報の連想配列（カラム名 => カラム値）の配列
     * @return mixed[] WHERE 句とバインドパラメータ
     */
    private function _get_where_statement_and_bind_param($column_maps)
    {
        $column_names = $this->_validate_column_maps_and_get_column_names($column_maps);

        $column_map_count = count($column_maps);
        $columns_statements = [];
        for ($i = 1; $i <= $column_map_count; $i++) {
            $suffix = "$i";
            array_push($columns_statements, $this->_get_columns_statement($column_names, $suffix));
        }
        $where_statement = 'WHERE (' . join(') OR (', $columns_statements) . ')';
        $bind_param = $this->_get_bind_param_by_column_maps($column_maps);
        return [$where_statement, $bind_param];
    }
    /**
     * カラム情報の連想配列の配列をバリデーションする。
     *
     * @param array[] $column_maps カラム情報の連想配列（カラム名 => カラム値）の配列
     * @return string[] カラム名の配列
     */
    protected function _validate_column_maps_and_get_column_names(array $column_maps)
    {
        if (!(!Arr::is_assoc($column_maps) && count($column_maps) > 0)) {
            throw new \InvalidArgumentException('column_maps');
        }
        $column_names = array_keys($column_maps[0]);
        foreach ($column_maps as $column_map) {
            if (!(Arr::is_assoc($column_map) && count(array_keys($column_map)) > 0)) {
                throw new \InvalidArgumentException('column_maps');
            }
            $current_key_count = count(array_keys($column_map));
            $valid_key_count = count(array_keys(Arr::filter_keys($column_map, $column_names)));
            $correct_key_count = count(array_keys($column_names));
            if ($current_key_count !== $correct_key_count || $valid_key_count !== $correct_key_count) {
                throw new \InvalidArgumentException('column_maps');
            }
        }
        return $column_names;
    }
    /**
     * SET 句を取得する。
     *
     * @param string[] $column_names 更新カラム名の配列
     * @return string SET 句（例：'SET col1 = :col1, col2 = :col2'）
     */
    private function _get_set_statement(array $column_names)
    {
        if (!(!Arr::is_assoc($column_names) && count($column_names) > 0)) {
            throw new \InvalidArgumentException('column_names');
        }
        $column_statements = [];
        foreach ($column_names as $column_name) {
            array_push($column_statements, $this->_get_column_statement($column_name, null));
        }
        return 'SET ' . join(', ', $column_statements);
    }
    /**
     * カラム条件文を取得する。複数条件対応のため、プレースホルダーのサフィックスに対応。
     *
     * @param string[] $column_names カラム名の配列
     * @param string $suffix プレースホルダーのサフィックス
     * @return string カラム条件文（例：'col1 = :col1 AND col2 = :col2'）
     */
    private function _get_columns_statement(array $column_names, $suffix)
    {
        if (!(!Arr::is_assoc($column_names) && count($column_names) > 0)) {
            throw new \InvalidArgumentException('column_names');
        }
        $column_statements = [];
        foreach ($column_names as $column_name) {
            array_push($column_statements, $this->_get_column_statement($column_name, $suffix));
        }
        return join(' AND ', $column_statements);
    }
    /**
     * カラム条件文を取得する。複数条件対応のため、プレースホルダーのサフィックスに対応。
     *
     * @param string $column_name カラム名
     * @param string $suffix プレースホルダーのサフィックス
     * @return string カラム条件文（例：'col1 = :col1'）
     */
    private function _get_column_statement($column_name, $suffix)
    {
        $place_holder_name = $this->_add_suffix_to_column_name($column_name, $suffix);
        return "$column_name = :$place_holder_name";
    }
    /**
     * バインドパラメータを取得する。
     *
     * @param array[] $column_maps カラム情報の連想配列（カラム名 => カラム値）の配列
     * @return array バインドパラメータ（プレイスホルダー名 => 値）
     */
    private function _get_bind_param_by_column_maps(array $column_maps)
    {
        if (!(!Arr::is_assoc($column_maps) && count($column_maps) > 0)) {
            throw new \InvalidArgumentException('column_maps');
        }
        $count = count($column_maps);
        $bind_param = [];
        for ($i = 1; $i <= $count; $i++) {
            $bind_param += $this->_get_bind_param_by_column_map($column_maps[$i - 1], "$i");
        }
        return $bind_param;
    }
    /**
     * バインドパラメータを取得する。
     *
     * @param array $column_map カラム情報の連想配列（カラム名 => カラム値）
     * @param string $suffix プレースホルダーのサフィックス
     * @return array バインドパラメータ（プレイスホルダー名 => 値）
     */
    private function _get_bind_param_by_column_map(array $column_map, $suffix)
    {
        if (!(Arr::is_assoc($column_map) && count(array_keys($column_map)) > 0)) {
            throw new \InvalidArgumentException('column_map');
        }
        $bind_param = [];
        foreach ($column_map as $column_name => $column_value) {
            $bind_param[$this->_add_suffix_to_column_name($column_name, $suffix)] = $column_value;
        }
        return $bind_param;
    }
    /**
     * （プレースホルダー対応のため）カラム名にサフィックスを追加する。
     *
     * @param string $column_name カラム名
     * @param string|null $suffix サフィックス
     * @return string サフィックスを追加したカラム名
     */
    private function _add_suffix_to_column_name($column_name, $suffix)
    {
        if (!(is_string($column_name) && strlen($column_name) > 0)) {
            throw new \InvalidArgumentException('column_name');
        }
        if (strpos($column_name, $this->_suffix_separator) !== false) {
            throw new \InvalidArgumentException('column_name');
        }
        if (isset($suffix)) {
            if (!(is_string($suffix) && strlen($suffix) > 0)) {
                throw new \InvalidArgumentException('suffix');
            }
            if (strpos($suffix, $this->_suffix_separator) !== false) {
                throw new \InvalidArgumentException('suffix');
            }
        }
        return isset($suffix) ? $column_name . $this->_suffix_separator . $suffix : $column_name;
    }
}
