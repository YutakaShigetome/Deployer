<?php
namespace Cycomi\Model;

class User extends AbstractUserOrm
{
    protected static $_table_name = 'users';
    protected static $_columns = [
        'id' => ['uint'],
        'email' => ['string'],
        'password' => ['string'],
        'language' => ['string'],
        'nickname' => ['string'],
        'sex' => ['string'],
        'birthday' => ['date'],
        'icon_status' => ['uint'],
        'login_status' => ['uint'],
        'ban_status' => ['uint'],
        'created' => ['timestamp'],
    ];
    protected static $_created_at = 'created';


    public static function find_one_by_email_and_password($email, $password)
    {
        return static::_find(['email' => $email, 'password' => $password]);
    }

    public static function find_one_by_id($id)
    {
        return static::_find_one_by_pk(['id' => $id]);
    }
    public static function find_one_by_id_with_lock($id)
    {
        return static::_find_locked_one_by_pk(['id' => $id]);
    }

    public function get_id()
    {
        return $this->_get('id');
    }
    public function get_nickname()
    {
        return $this->_get('nickname');
    }
    public function get_icon_id()
    {
        return $this->_get('icon_status');
    }
}
