<?php
namespace Cycomi\Controller;

use \Fuel\Core\Fuel;
use \Fuel\Core\Input;
use \Cycomi\Context\Container as ContextContainer;
use \Cycomi\Context\WebContext;

/**
 * コントローラー共通処理を扱うトレイト。
 *
 * @package Cycomi\Controller
 */
trait Base
{
    /** @var WebContext コンテキスト */
    private $_context;


    /**
     * コンテキストを取得する。
     *
     * @return WebContext コンテキスト
     */
    protected function _get_context()
    {
        return $this->_context;
    }

    /**
     * コンテキストを初期化する。
     *
     * @param string|null $os ネイティブアプリ OS
     * @param int|null $app_version ネイティブアプリバージョン
     * @param string|null $device_id デバイス識別子
     * @param string|null $session_id セッション識別子
     */
    private function _initialize_context($os, $app_version, $device_id, $session_id)
    {
        $ip = Input::real_ip();
        $user_agent = Input::user_agent();
        $this->_context = new WebContext(
            $os,
            $app_version,
            $device_id,
            $session_id,
            $ip,
            $user_agent
        );
        ContextContainer::set($this->_get_context());
    }

    /**
     * 旧システムの遺産をロードする。
     */
    private function _load_legacy_php()
    {
        // ロード対象。
        $legacy_lib_dir = dirname(__FILE__) . '/../../../../../../../../lib';
        $legacy_config_php = $legacy_lib_dir . '/config.php';
        $legacy_local_config_php = $legacy_lib_dir . '/config.local.php';
        $legacy_api_php = $legacy_lib_dir . '/base/api.php';
        $legacy_session_class_php = $legacy_lib_dir . '/base/session_class.php';
        // ロード。
        if (file_exists($legacy_local_config_php)) {
            require_once $legacy_local_config_php;
        } else {
            require_once $legacy_config_php;
        }
        require_once $legacy_api_php;
        require_once $legacy_session_class_php;
    }
    /**
     * 新旧システム横断 URL を作成する。
     *
     * @param string $path
     * @return string
     */
    private function _make_global_url($path)
    {
        // 個人開発環境は HTTP にする。
        $scheme = Fuel::$env !== 'local' ? 'https://' : 'http://';
        $current_host = Input::server('HTTP_HOST');
        return $scheme . $current_host . '/' . $path;
    }
}
