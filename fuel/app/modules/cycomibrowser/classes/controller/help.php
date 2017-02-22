<?php
namespace CycomiBrowser\Controller;

use \Fuel\Core\View;

/**
 * ヘルプを扱うコントローラー。
 *
 * @package CycomiBrowser\Controller
 */
class Help extends AbstractController
{
    /**
     * ヘルプ一覧ページ。
     */
    public function get_index()
    {
        $this->template->set('title', 'ヘルプ｜サイコミ');
        $this->template->set('content', View::forge('help/index', [
        ]));
    }
}
