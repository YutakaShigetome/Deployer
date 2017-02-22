<?php
/**
 * クエリビルダーのテスト
 *
 * @group Modules
 */
class Test_Model_Base_QueryBuilder extends \TestCase
{
    public function test_insert_query()
    {
        // テストケース定義。
        $table_name = 'test_table';
        $test_cases = [
            [[['id' => 1]], true,
                "INSERT INTO `$table_name` (id) VALUES (:id__1)", ['id__1' => 1]],
            [[['a_id' => 1, 'b_id' => 11]], true,
                "INSERT INTO `$table_name` (a_id, b_id) VALUES (:a_id__1, :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11]],
            [[['id' => 1], ['id' => 2]], true,
                "INSERT INTO `$table_name` (id) VALUES (:id__1), (:id__2)", ['id__1' => 1, 'id__2' => 2]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], true,
                "INSERT INTO `$table_name` (a_id, b_id) VALUES (:a_id__1, :b_id__1), (:a_id__2, :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12]],
            [[['id' => 1]], false,
                "INSERT IGNORE INTO `$table_name` (id) VALUES (:id__1)", ['id__1' => 1]],
            [[['a_id' => 1, 'b_id' => 11]], false,
                "INSERT IGNORE INTO `$table_name` (a_id, b_id) VALUES (:a_id__1, :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11]],
            [[['id' => 1], ['id' => 2]], false,
                "INSERT IGNORE INTO `$table_name` (id) VALUES (:id__1), (:id__2)", ['id__1' => 1, 'id__2' => 2]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], false,
                "INSERT IGNORE INTO `$table_name` (a_id, b_id) VALUES (:a_id__1, :b_id__1), (:a_id__2, :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12]],
        ];
        // テスト。
        $qb = new \Cycomi\Model\Base\QueryBuilder($table_name);
        foreach ($test_cases as $test_case) {
            list($where_column_maps, $throw_duplicate_entry_exception, $expected_sql, $expected_parameters) = $test_case;
            $query = $qb->build_insert_query($where_column_maps, $throw_duplicate_entry_exception);
            $this->_assertQuery($query, $expected_sql, $expected_parameters);
        }
    }
    public function test_select_query()
    {
        // テストケース定義。
        $table_name = 'test_table';
        $test_cases = [
            [[['id' => 1]], false,
                "SELECT * FROM `$table_name` WHERE (id = :id__1)", ['id__1' => 1]],
            [[['a_id' => 1, 'b_id' => 11]], false,
                "SELECT * FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11]],
            [[['id' => 1], ['id' => 2]], false,
                "SELECT * FROM `$table_name` WHERE (id = :id__1) OR (id = :id__2)", ['id__1' => 1, 'id__2' => 2]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], false,
                "SELECT * FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1) OR (a_id = :a_id__2 AND b_id = :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12]],
            [[['id' => 1]], true,
                "SELECT * FROM `$table_name` WHERE (id = :id__1) FOR UPDATE", ['id__1' => 1]],
            [[['a_id' => 1, 'b_id' => 11]], true,
                "SELECT * FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1) FOR UPDATE", ['a_id__1' => 1, 'b_id__1' => 11]],
            [[['id' => 1], ['id' => 2]], true,
                "SELECT * FROM `$table_name` WHERE (id = :id__1) OR (id = :id__2) FOR UPDATE", ['id__1' => 1, 'id__2' => 2]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], true,
                "SELECT * FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1) OR (a_id = :a_id__2 AND b_id = :b_id__2) FOR UPDATE", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12]],
        ];
        // テスト。
        $qb = new \Cycomi\Model\Base\QueryBuilder($table_name);
        foreach ($test_cases as $test_case) {
            list($where_column_maps, $with_lock, $expected_sql, $expected_parameters) = $test_case;
            $query = $qb->build_select_query($where_column_maps, $with_lock);
            $this->_assertQuery($query, $expected_sql, $expected_parameters);
        }
    }
    public function test_update_query()
    {
        // テストケース定義。
        $table_name = 'test_table';
        $test_cases = [
            [[['id' => 1]], ['col' => 101],
                "UPDATE `$table_name` SET col = :col WHERE (id = :id__1)", ['id__1' => 1, 'col' => 101]],
            [[['a_id' => 1, 'b_id' => 11]], ['col' => 101],
                "UPDATE `$table_name` SET col = :col WHERE (a_id = :a_id__1 AND b_id = :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11, 'col' => 101]],
            [[['id' => 1], ['id' => 2]], ['col' => 101],
                "UPDATE `$table_name` SET col = :col WHERE (id = :id__1) OR (id = :id__2)", ['id__1' => 1, 'id__2' => 2, 'col' => 101]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], ['col' => 101],
                "UPDATE `$table_name` SET col = :col WHERE (a_id = :a_id__1 AND b_id = :b_id__1) OR (a_id = :a_id__2 AND b_id = :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12, 'col' => 101]],
            [[['id' => 1]], ['col1' => 101, 'col2' => "str"],
                "UPDATE `$table_name` SET col1 = :col1, col2 = :col2 WHERE (id = :id__1)", ['id__1' => 1, 'col1' => 101, 'col2' => "str"]],
            [[['a_id' => 1, 'b_id' => 11]], ['col1' => 101, 'col2' => "str"],
                "UPDATE `$table_name` SET col1 = :col1, col2 = :col2 WHERE (a_id = :a_id__1 AND b_id = :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11, 'col1' => 101, 'col2' => "str"]],
            [[['id' => 1], ['id' => 2]], ['col1' => 101, 'col2' => "str"],
                "UPDATE `$table_name` SET col1 = :col1, col2 = :col2 WHERE (id = :id__1) OR (id = :id__2)", ['id__1' => 1, 'id__2' => 2, 'col1' => 101, 'col2' => "str"]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]], ['col1' => 101, 'col2' => "str"],
                "UPDATE `$table_name` SET col1 = :col1, col2 = :col2 WHERE (a_id = :a_id__1 AND b_id = :b_id__1) OR (a_id = :a_id__2 AND b_id = :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12, 'col1' => 101, 'col2' => "str"]],
        ];
        // テスト。
        $qb = new \Cycomi\Model\Base\QueryBuilder($table_name);
        foreach ($test_cases as $test_case) {
            list($where_column_maps, $set_column_map, $expected_sql, $expected_parameters) = $test_case;
            $query = $qb->build_update_query($where_column_maps, $set_column_map);
            $this->_assertQuery($query, $expected_sql, $expected_parameters);
        }
    }
    public function test_delete_query()
    {
        // テストケース定義。
        $table_name = 'test_table';
        $test_cases = [
            [[['id' => 1]],
                "DELETE FROM `$table_name` WHERE (id = :id__1)", ['id__1' => 1]],
            [[['a_id' => 1, 'b_id' => 11]],
                "DELETE FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1)", ['a_id__1' => 1, 'b_id__1' => 11]],
            [[['id' => 1], ['id' => 2]],
                "DELETE FROM `$table_name` WHERE (id = :id__1) OR (id = :id__2)", ['id__1' => 1, 'id__2' => 2]],
            [[['a_id' => 1, 'b_id' => 11], ['a_id' => 2, 'b_id' => 12]],
                "DELETE FROM `$table_name` WHERE (a_id = :a_id__1 AND b_id = :b_id__1) OR (a_id = :a_id__2 AND b_id = :b_id__2)", ['a_id__1' => 1, 'b_id__1' => 11, 'a_id__2' => 2, 'b_id__2' => 12]],
        ];
        // テスト。
        $qb = new \Cycomi\Model\Base\QueryBuilder($table_name);
        foreach ($test_cases as $test_case) {
            list($where_column_maps, $expected_sql, $expected_parameters) = $test_case;
            $query = $qb->build_delete_query($where_column_maps);
            $this->_assertQuery($query, $expected_sql, $expected_parameters);
        }
    }

    private function _assertQuery($query, $expected_sql, $expected_parameters)
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
