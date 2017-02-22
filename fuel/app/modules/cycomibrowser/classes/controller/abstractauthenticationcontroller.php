<?php
namespace CycomiBrowser\Controller;

/**
 * ログイン必須の場合に利用する抽象コントローラー。
 *
 * @package CycomiBrowser\Controller
 */
abstract class AbstractAuthenticationController extends AbstractController
{
    public function before()
    {
        parent::before();

        if (!$this->_get_context()->is_logged_in()) {
            // TODO : ログイン画面。
        }
    }
}
