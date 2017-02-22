<?php
namespace CycomiBrowser\Controller;

use \Fuel\Core\Controller_Template;
use \Fuel\Core\Cookie;
use \Fuel\Core\Response;
use \Fuel\Core\View;
use \Cycomi\Controller\Base;

/**
 * 抽象コントローラークラス。
 *
 * @package CycomiBrowser\Controller
 * @property \Fuel\Core\View $template
 */
abstract class AbstractController extends Controller_Template
{
    use Base;

    public function before()
    {
        parent::before();

        // 既存設定を読み込む。
        $this->_load_legacy_php();

        // コンテキスト初期化に必要なパラメータをクッキーから取得。
        $os = null;
        $app_version = null;
        $device_id = null;
        $session_id = Cookie::get('X-Cycomi-Session-Id');

        // コンテキスト初期化。
        $this->_initialize_context($os, $app_version, $device_id, $session_id);

        // メンテナンスチェック。
        if ($this->_get_context()->is_under_maintenance()) {
            return;
        }
        // 互換性（バージョン）チェック。
        if (!$this->_get_context()->is_compatible()) {
//            return;
        }

        // 認証。
        $this->_get_context()->authenticate_by_cookie();

        // テンプレート準備。
        $this->template->set('head', View::forge('head'));
        $this->template->set('header', View::forge('header'));
        $this->template->set('footer', View::forge('footer'));
        $this->template->set_global('context', $this->_get_context()->to_view_param());
    }

    public function router($method, $params)
    {
        // メンテナンスチェック。
        if ($this->_get_context()->is_under_maintenance()) {
            return $this->_set_error_response('is_under_maintenance');
        }
        // 互換性（バージョン）チェック。
        if (!$this->_get_context()->is_compatible()) {
//            $this->_set_error_response('is_not_compatible');
//            return;
        }
        $controller_method = join('_', [$this->request->get_method(), $method]);
        if (method_exists($this, $controller_method))
        {
            return call_fuel_func_array(array($this, $controller_method), $params);
        }
        else
        {
            Response::redirect($this->_make_global_url('404.php'));
        }
    }

    /**
     * エラーレスポンスをセットする。
     *
     * @param string $error エラー内容。
     */
    private function _set_error_response($error)
    {
        // 今は既存ページを使ったメンテ対応のみ。
        if ($error === 'is_under_maintenance') {
            Response::redirect($this->_make_global_url('maintenance.php'));
        }
    }
}
