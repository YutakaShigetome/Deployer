<?php
namespace Cycomi\Model\Base;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Fuel\Core\Date;

class ColumnDefinition
{
    /**
     * @var string カラム名
     */
    private $_name;
    /**
     * @var string 型
     */
    private $_type;
    /**
     * @var array[] 設定配列
     */
    private $_configs;


    public function __construct($name, $configs)
    {
        $this->_name = $name;
        $this->_configs = [];
        foreach ($configs as $config) {
            $this->_parse_config($config);
        }
    }

    private function _parse_config($config)
    {
        // ショートカット対応。
        if (is_string($config)) {
            switch ($config) {
                case 'int':
                case 'string':
                case 'date':
                case 'timestamp':
                    $this->_parse_config(['type', $config]);
                    return;
                case 'uint':
                    $this->_parse_config('int');
                    $this->_parse_config(['range', 0, null]);
                    return;
                default:
                    break;
            }
            throw new \InvalidArgumentException('config');
        } else {
            if ($config[0] == 'type') {
                $this->_type = $config[1];
            }
            array_push($this->_configs, $config);
        }
    }

    /**
     * 型取得。
     *
     * @return string
     */
    public function get_type()
    {
        return $this->_type;
    }
    /**
     * バリデーション。
     *
     * @param mixed $value 対象値
     * @return string[] バリデーションエラー種別
     */
    public function validate($value)
    {
        $errors = [];
        foreach ($this->_configs as $config) {
            if (!$this->_validate($config, $value)) {
                array_push($errors, $config[0]);
            }
        }
        return $errors;
    }
    /**
     * バリデーション。
     *
     * @param array $config バリデーション設定
     * @param mixed $value 対象値
     * @return bool バリデーションの結果、問題なかったかどうか
     */
    private function _validate(array $config, $value)
    {
        switch ($config[0]) {
            case 'type':
                return $this->_validate_with_type($config, $value);
            case 'range':
                return $this->_validate_with_range($config, $value);
            case 'length':
                return $this->_validate_with_length($config, $value);
            case 'choice':
                // TODO : 必要なタイミングで実装。
                throw new \InvalidArgumentException();
            case 'regexp':
                // TODO : 必要なタイミングで実装。
                throw new \InvalidArgumentException();
            case 'function':
                // TODO : 必要なタイミングで実装。
                throw new \InvalidArgumentException();
            default:
                throw new \InvalidArgumentException();
        }
    }
    /**
     * 型情報によるバリデーション。
     *
     * @param array $config バリデーション設定
     * @param mixed $value 対象値
     * @return bool バリデーションの結果、問題なかったかどうか
     */
    private function _validate_with_type(array $config, $value)
    {
        $type = $config[1];
        switch ($type) {
            case 'int':
                return is_int($value);
            case 'string':
                return is_string($value);
            case 'date':
                return $value instanceof Date;
            case 'timestamp':
                return $value instanceof Date;
            default:
                throw new \LogicException('Invalid Type');
        }
    }
    /**
     * レンジバリデーション。
     *
     * @param array $config バリデーション設定
     * @param int $value 対象値
     * @return bool バリデーションの結果、問題なかったかどうか
     */
    private function _validate_with_range(array $config, $value)
    {
        $min = $config[1];
        $max = $config[2];
        if (!is_int($value)) {
            return false;
        }
        if (isset($min) && !is_int($min)) {
            throw new InvalidArgumentException('min');
        }
        if (isset($max) && !is_int($max)) {
            throw new InvalidArgumentException('max');
        }
        return (!isset($min) || $min <= $value) && (!isset($max) || $value <= $max);
    }
    /**
     * 文字数バリデーション。
     *
     * @param array $config バリデーション設定
     * @param string $value 対象値
     * @return bool バリデーションの結果、問題なかったかどうか
     */
    private function _validate_with_length(array $config, $value)
    {
        $min = $config[1];
        $max = $config[2];
        if (!is_string($value)) {
            return false;
        }
        if (isset($min) && !is_int($min)) {
            throw new InvalidArgumentException('min');
        }
        if (isset($max) && !is_int($max)) {
            throw new InvalidArgumentException('max');
        }
        return (!isset($min) || $min <= strlen($value)) && (!isset($max) || strlen($value) <= $max);
    }

    /**
     * DB から取得した値を適切な形式へ変換する。
     *
     * @param string $previous_value DB から取得した値（文字列）
     * @return mixed 変換後の値
     */
    public function inflate($previous_value)
    {
        switch ($this->_type) {
            case 'int':
                $value = intval($previous_value);
                break;
            case 'date':
                $value = Date::create_from_string($previous_value, 'mysql_date');
                break;
            case 'timestamp':
                $value = Date::create_from_string($previous_value, 'mysql');
                break;
            default:
                $value = $previous_value;
                break;
        }
        return $value;
    }
    /**
     * DB 格納形式へ変換する。
     *
     * @param mixed $previous_value 変換前の値
     * @return mixed 変換後の値
     */
    public function deflate($previous_value)
    {
        switch ($this->_type) {
            case 'date':
                $value = $previous_value->format('%Y-%m-%d');
                break;
            case 'timestamp':
                $value = $previous_value->format('%Y-%m-%d %H:%M:%S');
                break;
            default:
                $value = $previous_value;
                break;
        }
        return $value;
    }
}
