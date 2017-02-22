<?php
namespace Cycomi\Model;

abstract class AbstractMangaOrm extends Base\AbstractOrm
{
    protected static $_connection = 'manga_slave';
    protected static $_write_connection = 'manga_master';
}
