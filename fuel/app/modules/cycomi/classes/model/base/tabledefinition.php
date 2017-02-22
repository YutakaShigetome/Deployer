<?php

namespace Cycomi\Model\Base;

class TableDefinition
{
    /**
     * @var array カラム定義マップ。
     */
    private $_column_definition_map;

    /**
     * TableDefinition constructor.
     *
     * @param string $table_name テーブル名
     * @param array[] $column_configs カラム設定
     */
    public function __construct($table_name, $column_configs)
    {
        $this->_column_definition_map = [];
        foreach ($column_configs as $column_name => $column_config) {
            $this->_column_definition_map[$column_name] = new ColumnDefinition($column_name, $column_config);
        }
    }

    /**
     * カラム名を取得する。
     *
     * @return string[] カラム名配列。
     */
    public function get_column_names()
    {
        return array_keys($this->_column_definition_map);
    }

    /**
     * 対象カラムの型を取得する。
     *
     * @param string $column_name
     * @return string
     */
    public function get_column_type($column_name)
    {
        return $this->_get_column_definition($column_name)->get_type();
    }

    /**
     * バリデーション。
     *
     * @param $column_map array カラム連想配列
     * @return ValidationError|null バリデーションエラー
     */
    public function validate($column_map)
    {
        $validation_keys = [];
        foreach ($column_map as $column_name => $column_value) {
            $validation_keys
                = array_merge($validation_keys, $this->_get_column_definition($column_name)->validate($column_value));
        }
        return count($validation_keys) > 0 ? new ValidationError($validation_keys) : null;
    }

    /**
     * DB から取得した値を適切な形式へ変換する。
     *
     * @param string $column_name カラム名
     * @param string $previous_value DB から取得した値（文字列）
     * @return mixed 変換後の値
     */
    public function inflate($column_name, $previous_value)
    {
        return $this->_get_column_definition($column_name)->inflate($previous_value);
    }

    /**
     * DB 格納形式へ変換する。
     *
     * @param string $column_name カラム名
     * @param mixed $previous_value 変換前の値
     * @return mixed 変換後の値
     */
    public function deflate($column_name, $previous_value)
    {
        return $this->_get_column_definition($column_name)->deflate($previous_value);
    }

    /**
     * カラム定義オブジェクト取得。
     *
     * @param string $column_name カラム名
     * @return ColumnDefinition カラム定義オブジェクト
     */
    private function _get_column_definition($column_name)
    {
        return $this->_column_definition_map[$column_name];
    }
}
