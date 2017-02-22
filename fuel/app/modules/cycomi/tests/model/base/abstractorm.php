<?php

use Fuel\Core\TestCase;
use Cycomi\Model\Base\ColumnDefinition;
/**
 * ORM のテスト
 *
 * @group Modules
 */
class Test_Model_Base_AbstractOrm extends TestCase
{
    /**
     * コンテキストの初期化。
     *
     * @before
     */
    public function initialize_context()
    {
        \Cycomi\Context\Container::set(new \Cycomi\Context\CliContext());
    }

    /**
     * コネクションテスト。
     */
    public function test_connection()
    {
        // SLAVE と MASTER それぞれ二回ずつコネクションを取得。
        $slave_connection1 = $this->_invoke_orm_static_method('_get_slave_connection');
        $master_connection1 = $this->_invoke_orm_static_method('_get_master_connection');
        $slave_connection2 = $this->_invoke_orm_static_method('_get_slave_connection');
        $master_connection2 = $this->_invoke_orm_static_method('_get_master_connection');
        // コネクションを使い回しているかチェック。
        $this->assertEquals($slave_connection1, $slave_connection2);
        $this->assertEquals($master_connection1, $master_connection2);
        // マスターとスレーブのコネクションは別物かチェック。
        $this->assertNotEquals($slave_connection1, $master_connection1);
    }

    /**
     * 参照クエリとコネクションテスト。
     */
    public function test_select_query_and_connection()
    {
        // 入力値
        $where_column_maps = [['id' => 1]];
        // 期待値
        $expected_sql = 'SELECT * FROM `test_single_pk` WHERE (id = :id__1)';
        $expected_sql_with_lock = "$expected_sql FOR UPDATE";
        $expected_parameters = ['id__1' => 1];
        $expected_slave_connection = $this->_invoke_orm_static_method('_get_slave_connection');
        $expected_master_connection = $this->_invoke_orm_static_method('_get_master_connection');

        // SLAVE 参照、かつ、SQL が正しいか（ロックなし）チェック。
        list($query, $connection) = $this->_invoke_orm_static_method_with_args('_get_select_query_and_connection', [$where_column_maps]);
        $this->_assert_query($expected_sql, $expected_parameters, $query);
        $this->assertEquals($expected_slave_connection, $connection);
        // MASTER 参照、かつ、SQL が正しいか（ロックなし）チェック。
        list($query, $connection) = $this->_invoke_orm_static_method_with_args('_get_select_latest_query_and_connection', [$where_column_maps]);
        $this->_assert_query($expected_sql, $expected_parameters, $query);
        $this->assertEquals($expected_master_connection, $connection);
        // MASTER 参照、かつ、SQL が正しいか（ロックあり）チェック。
        list($query, $connection) = $this->_invoke_orm_static_method_with_args('_get_select_locked_query_and_connection', [$where_column_maps]);
        $this->_assert_query($expected_sql_with_lock, $expected_parameters, $query);
        $this->assertEquals($expected_master_connection, $connection);
    }

    /**
     * バリデーションテスト。
     */
    public function test_validation()
    {
        // まずはバリデーション挙動詳細チェック。

        // int 型
        $column_definition = new ColumnDefinition('int', ['int']);
        $this->assertEquals([], $column_definition->validate(1));
        $this->assertEquals(['type'], $column_definition->validate('1'));
        // string 型
        $column_definition = new ColumnDefinition('string', ['string']);
        $this->assertEquals([], $column_definition->validate('1'));
        $this->assertEquals(['type'], $column_definition->validate(1));
        // date 型
        $column_definition = new ColumnDefinition('date', ['date']);
        $this->assertEquals([], $column_definition->validate(Date::create_from_string('2017-01-01', 'mysql_date')));
        $this->assertEquals(['type'], $column_definition->validate('2017-01-01'));
        // timestamp 型
        $column_definition = new ColumnDefinition('timestamp', ['timestamp']);
        $this->assertEquals([], $column_definition->validate(Date::create_from_string('2017-01-01 00:00:00', 'mysql')));
        $this->assertEquals(['type'], $column_definition->validate('2017-01-01 00:00:00'));
        // uint 型（int 型 + range のショートカット）
        $column_definition = new ColumnDefinition('uint', ['uint']);
        $this->assertEquals(['range'], $column_definition->validate(-1));
        $this->assertEquals([], $column_definition->validate(0));
        $this->assertEquals([], $column_definition->validate(1));
        $this->assertEquals(['type', 'range'], $column_definition->validate('1'));
        // range 最小チェック
        $column_definition = new ColumnDefinition('int_min', ['int', ['range', 10, null]]);
        $this->assertEquals(['range'], $column_definition->validate(9));
        $this->assertEquals([], $column_definition->validate(10));
        $this->assertEquals([], $column_definition->validate(100));
        $this->assertEquals([], $column_definition->validate(101));
        $this->assertEquals(['type', 'range'], $column_definition->validate('10'));
        // range 最大チェック
        $column_definition = new ColumnDefinition('int_max', ['int', ['range', null, 100]]);
        $this->assertEquals([], $column_definition->validate(9));
        $this->assertEquals([], $column_definition->validate(10));
        $this->assertEquals([], $column_definition->validate(100));
        $this->assertEquals(['range'], $column_definition->validate(101));
        $this->assertEquals(['type', 'range'], $column_definition->validate('10'));
        // range 最小最大チェック
        $column_definition = new ColumnDefinition('int_min_max', ['int', ['range', 10, 100]]);
        $this->assertEquals(['range'], $column_definition->validate(9));
        $this->assertEquals([], $column_definition->validate(10));
        $this->assertEquals([], $column_definition->validate(100));
        $this->assertEquals(['range'], $column_definition->validate(101));
        $this->assertEquals(['type', 'range'], $column_definition->validate('10'));
        // length 最小チェック
        $column_definition = new ColumnDefinition('string_min', ['string', ['length', 3, null]]);
        $this->assertEquals(['length'], $column_definition->validate('12'));
        $this->assertEquals([], $column_definition->validate('123'));
        $this->assertEquals([], $column_definition->validate('12345'));
        $this->assertEquals([], $column_definition->validate('123456'));
        $this->assertEquals(['type', 'length'], $column_definition->validate(123));
        // length 最大チェック
        $column_definition = new ColumnDefinition('string_max', ['string', ['length', null, 5]]);
        $this->assertEquals([], $column_definition->validate('12'));
        $this->assertEquals([], $column_definition->validate('123'));
        $this->assertEquals([], $column_definition->validate('12345'));
        $this->assertEquals(['length'], $column_definition->validate('123456'));
        $this->assertEquals(['type', 'length'], $column_definition->validate(123));
        // length 最小最大チェック
        $column_definition = new ColumnDefinition('string_min_max', ['string', ['length', 3, 5]]);
        $this->assertEquals(['length'], $column_definition->validate('12'));
        $this->assertEquals([], $column_definition->validate('123'));
        $this->assertEquals([], $column_definition->validate('12345'));
        $this->assertEquals(['length'], $column_definition->validate('123456'));
        $this->assertEquals(['type', 'length'], $column_definition->validate(123));

        // 次に実際にバリデーション引っかかった場合の各所挙動。

        // 利用するレコード内容。
        $valid_column_map = [
            'id' => 1,
            'col_int' => -3,
            'col_uint' => 3,
            'col_string' => 'string',
            'col_date' => Date::create_from_string('2017-01-01', 'mysql_date'),
            'col_timestamp' => Date::create_from_string('2017-01-01 00:00:00', 'mysql'),
        ];
        $invalid_column_map = [
            'id' => 1,
            'col_int' => 'string',
            'col_uint' => -3,
            'col_string' => 3,
            'col_date' => '2017-01-01',
            'col_timestamp' => '2017-01-01 00:00:00',
        ];

        // まずはテーブル作成。
        $connection = \Database_Connection::instance('user_master');
        $query = \DB::query(TestSinglePKOrm::get_drop_table_statement());
        $query->execute($connection);
        $query = \DB::query(TestSinglePKOrm::get_ddl());
        $query->execute($connection);

        // 該当レコードが存在していないことをチェック。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($valid_column_map['id']);
        $this->assertNull($test_single_pk_model);

        // バリデーションエラーが起こる内容ではレコード作成失敗。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($invalid_column_map) {
            try {
                TestSinglePKOrm::create($invalid_column_map);
                // ここまで到達しないはず。
                $this->fail();
                $db->commit_transaction();
            } catch (Exception $e) {
            }
        });

        // 該当レコードが作成されなかったことをチェック。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($valid_column_map['id']);
        $this->assertNull($test_single_pk_model);

        // バリデーションエラーが起こらない内容であればレコード作成成功。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($valid_column_map) {
            $test_single_pk_model = TestSinglePKOrm::create($valid_column_map);
            // 返り値が正しいかチェック。
            $this->_assert_test_single_pk_orm($valid_column_map, $test_single_pk_model);
            $db->commit_transaction();
        });

        // バリデーションエラーが起こる内容ではレコード更新失敗。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($valid_column_map, $invalid_column_map) {
            try {
                $test_single_pk_model = TestSinglePKOrm::find_locked_one_by_id($valid_column_map['id']);
                // 作成レコードの中身が正しいかチェック。
                $this->_assert_test_single_pk_orm($valid_column_map, $test_single_pk_model);
                $test_single_pk_model->set($invalid_column_map);
                // ここまで到達しないはず。
                $this->fail();
                $success = $test_single_pk_model->update();
                // 更新成功返り値をチェック。
                $this->assertTrue($success);
                $db->commit_transaction();
            } catch (Exception $e) {
            }
        });
        // 該当レコードが更新されなかったことをチェック。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($valid_column_map['id']);
        $this->_assert_test_single_pk_orm($valid_column_map, $test_single_pk_model);
    }

    /**
     * CRUD テスト。
     */
    public function test_crud()
    {
        // 利用するレコード内容。
        $column_map = [
            'id' => 1,
            'col_int' => -3,
            'col_uint' => 3,
            'col_string' => 'string',
            'col_date' => Date::create_from_string('2017-01-01', 'mysql_date'),
            'col_timestamp' => Date::create_from_string('2017-01-01 00:00:00', 'mysql'),
        ];

        // まずはテーブル作成。
        $connection = \Database_Connection::instance('user_master');
        $query = \DB::query(TestSinglePKOrm::get_drop_table_statement());
        $query->execute($connection);
        $query = \DB::query(TestSinglePKOrm::get_ddl());
        $query->execute($connection);

        // 該当レコードが存在していないことをチェック。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
        $this->assertNull($test_single_pk_model);

        // レコード作成。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($column_map) {
            $test_single_pk_model = TestSinglePKOrm::create($column_map);
            // 返り値が正しいかチェック。
            $this->_assert_test_single_pk_orm($column_map, $test_single_pk_model);
            $db->commit_transaction();
        });

        // 作成したレコードを参照。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
        // 作成レコードの中身が正しいかチェック。
        $this->_assert_test_single_pk_orm($column_map, $test_single_pk_model);

        // 重複レコード作成。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($column_map) {
            $test_single_pk_model = TestSinglePKOrm::create($column_map);
            // 重複エラーで返り値が存在しないかチェック。
            $this->assertNull($test_single_pk_model);
        });

        // レコードを更新。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($column_map) {
            // ロックを掛けていない場合は例外。
            $test_single_pk_model_without_lock = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
            $test_single_pk_model_without_lock->set([
                'col_int' => $test_single_pk_model_without_lock->get_col_int() + 1,
                'col_string' => $test_single_pk_model_without_lock->get_col_string() . '_suffix',
            ]);
            try {
                $test_single_pk_model_without_lock->update();
                $this->fail();
            } catch (Exception $e) {
            }
            // ちゃんとロックを掛けてやり直し。
            $test_single_pk_model = TestSinglePKOrm::find_locked_one_by_id($column_map['id']);
            $test_single_pk_model->set([
                'col_int' => $test_single_pk_model->get_col_int() + 1,
                'col_string' => $test_single_pk_model->get_col_string() . '_suffix',
            ]);
            $success = $test_single_pk_model->update();
            // 更新成功返り値をチェック。
            $this->assertTrue($success);
            $db->commit_transaction();
        });

        // 更新したレコードを参照。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
        // 更新レコードの中身が正しいかチェック。
        $column_map_clone = $column_map;
        $column_map_clone['col_int'] += 1;
        $column_map_clone['col_string'] .= '_suffix';
        $this->_assert_test_single_pk_orm($column_map_clone, $test_single_pk_model);

        // レコードを削除。
        \Cycomi\Context\Container::get()->user_transaction(function ($db) use ($column_map) {
            // ロックを掛けていない場合は例外。
            $test_single_pk_model_without_lock = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
            try {
                $test_single_pk_model_without_lock->delete();
                $this->fail();
            } catch (Exception $e) {
            }
            // ちゃんとロックを掛けてやり直し。
            $test_single_pk_model1 = TestSinglePKOrm::find_locked_one_by_id($column_map['id']);
            $test_single_pk_model2 = TestSinglePKOrm::find_locked_one_by_id($column_map['id']);
            $success = $test_single_pk_model1->delete();
            // 削除成功返り値をチェック。
            $this->assertTrue($success);
            $success = $test_single_pk_model2->delete();
            // 削除失敗返り値をチェック。
            $this->assertFalse($success);
            $db->commit_transaction();
        });
        // 削除したレコードを参照。
        $test_single_pk_model = TestSinglePKOrm::find_latest_one_by_id($column_map['id']);
        // 削除レコードが存在しないかチェック。
        $this->assertNull($test_single_pk_model);
    }
    private function _assert_test_single_pk_orm($column_map, $test_single_pk_model)
    {
        $this->assertEquals($column_map['id'], $test_single_pk_model->get_id());
        $this->assertEquals($column_map['col_int'], $test_single_pk_model->get_col_int());
        $this->assertEquals($column_map['col_uint'], $test_single_pk_model->get_col_uint());
        $this->assertEquals($column_map['col_string'], $test_single_pk_model->get_col_string());
        $this->assertEquals($column_map['col_date'], $test_single_pk_model->get_col_date());
        $this->assertEquals($column_map['col_timestamp'], $test_single_pk_model->get_col_timestamp());
        $this->assertEquals($column_map, $test_single_pk_model->to_array());
        $primitive_column_map = [];
        foreach ($column_map as $column_name => $column_value) {
            $primitive_column_map[$column_name] = ($column_value instanceof Date) ? $column_value->get_timestamp() : $column_value;
        }
        $this->assertEquals($primitive_column_map, $test_single_pk_model->to_primitive_array());
    }

    private function _invoke_orm_static_method($methodName)
    {
        $reflectionClass = new ReflectionClass('TestSinglePKOrm');
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->Invoke(null);
    }
    private function _invoke_orm_static_method_with_args($methodName, array $args)
    {
        $reflectionClass = new ReflectionClass('TestSinglePKOrm');
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }

    private function _assert_query($expected_sql, $expected_parameters, $query)
    {
        $sql = $this->_extract_sql($query);
        $parameters = $this->_extract_parameters($query);
        $this->assertEquals($expected_sql, $sql);
        $this->assertEquals($expected_parameters, $parameters);
    }
    private function _extract_sql($query)
    {
        $reflectionClass = new ReflectionClass($query);
        $sql_accessor = $reflectionClass->getProperty('_sql');
        $sql_accessor->setAccessible(true);
        return $sql_accessor->getValue($query);
    }
    private function _extract_parameters($query)
    {
        $reflectionClass = new ReflectionClass($query);
        $parameters_accessor = $reflectionClass->getProperty('_parameters');
        $parameters_accessor->setAccessible(true);
        return $parameters_accessor->getValue($query);
    }
}

class TestSinglePKOrm extends Cycomi\Model\Base\AbstractOrm
{
    protected static $_connection = 'user_slave';
    protected static $_write_connection = 'user_master';
    protected static $_table_name = 'test_single_pk';
    protected static $_columns = [
        'id' => ['uint'],
        'col_int' => ['int'],
        'col_uint' => ['uint'],
        'col_string' => ['string'],
        'col_date' => ['date'],
        'col_timestamp' => ['timestamp'],
    ];

    /**
     * @param $id
     * @return self|null
     */
    public static function find_latest_one_by_id($id)
    {
        return static::_find_latest_one_by_pk(['id' => $id]);
    }
    /**
     * @param $id
     * @return self|null
     */
    public static function find_locked_one_by_id($id)
    {
        return static::_find_locked_one_by_pk(['id' => $id]);
    }
    public static function get_drop_table_statement()
    {
        return <<<EOT
DROP TABLE IF EXISTS `test_single_pk`;
EOT;
    }
    public static function get_ddl()
    {
        return <<<EOT
CREATE TABLE `test_single_pk` (
  `id` int unsigned NOT NULL,
  `col_int` int NOT NULL,
  `col_uint` int NOT NULL,
  `col_string` varchar(128) NOT NULL,
  `col_date` date NOT NULL,
  `col_timestamp` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
    }

    public function get_id()
    {
        return $this->_get('id');
    }
    public function get_col_int()
    {
        return $this->_get('col_int');
    }
    public function get_col_uint()
    {
        return $this->_get('col_uint');
    }
    public function get_col_string()
    {
        return $this->_get('col_string');
    }
    public function get_col_date()
    {
        return $this->_get('col_date');
    }
    public function get_col_timestamp()
    {
        return $this->_get('col_timestamp');
    }

    public function set(array $column_map)
    {
        return $this->_set($column_map);
    }
}
