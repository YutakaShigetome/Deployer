<?php
namespace Cycomi\Model;

abstract class AbstractUserOrm extends Base\AbstractOrm
{
    protected static $_connection = 'user_slave';
    protected static $_write_connection = 'user_master';
}
