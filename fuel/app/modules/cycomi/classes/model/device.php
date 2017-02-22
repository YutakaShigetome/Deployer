<?php
namespace Cycomi\Model;

class Device extends AbstractUserOrm
{
    protected static $_table_name = 'devices';
    protected static $_columns = [
        'id' => ['uint'],
        'secret' => ['string'],
        'os' => ['string'],
        'user_id' => ['uint'],
        'created' => ['timestamp'],
    ];
    protected static $_created_at = 'created';


    public static function find_by_id_with_lock($id)
    {
        return static::_find_locked_one_by_pk(['id' => $id]);
    }

    public static function find_one_by_secret($secret)
    {
        return static::_find_one(['secret' => $secret]);
    }

    public function get_user_id()
    {
        return $this->_get('user_id');
    }
}
