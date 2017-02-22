<?php
namespace Cycomi\Context;

use \Cycomi\Model\Device;
use \Cycomi\Model\User;

/**
 * Web アプリケーション用コンテキストを扱うクラス。
 *
 * @package Cycomi\Context
 */
class WebContext extends AbstractContext
{
    /** @var string|null ネイティブアプリ OS */
    private $_os;
    /** @var int|null ネイティブアプリバージョン */
    private $_app_version;
    /** @var string|null デバイス識別子 */
    private $_device_id;
    /** @var string|null セッション識別子 */
    private $_session_id;
    /** @var string 接続元 IP */
    private $_ip;
    /** @var string|null ユーザーエージェント */
    private $_user_agent;

    /** @var int ユーザー ID */
    private $_user_id;
    /** @var string ニックネーム */
    private $_nickname;
    /** @var int アイコン ID */
    private $_icon_id;


    /**
     * コンストラクタ。
     *
     * @param string|null $os ネイティブアプリ OS
     * @param int|null $app_version ネイティブアプリバージョン
     * @param string|null $device_id デバイス識別子
     * @param string|null $session_id セッション識別子
     * @param string $ip 接続元 IP
     * @param string|null $user_agent ユーザーエージェント
     */
    public function __construct($os, $app_version, $device_id, $session_id, $ip, $user_agent)
    {
        parent::__construct();
        $this->_os = $os;
        $this->_app_version = $app_version;
        $this->_device_id = $device_id;
        $this->_session_id = $session_id;
        $this->_ip = $ip;
        $this->_user_agent = $user_agent;
    }

    /**
     * ネイティブアプリかどうかを判定。
     *
     * @return bool ネイティブアプリなら真。
     */
    private function _is_native_app()
    {
        return isset($this->_app_version);
    }
    /**
     * ブラウザアプリかどうかを判定。
     *
     * @return bool ブラウザアプリなら真。
     */
    private function _is_browser_app()
    {
        return isset($this->_user_agent);
    }
    /**
     * スマートフォンブラウザアプリかどうかを判定。
     *
     * @return bool スマートフォンブラウザアプリなら真。
     */
    public function is_smartphone_browser_app()
    {
        if (!$this->_is_browser_app()) {
            return false;
        }
        // 既存処理を移植。
        $ua = strtolower($this->_user_agent);
        return preg_match('/iphone|ipod|ipad|android|windows phone|blackberry/', $ua);
    }

    /**
     * Android ネイティブアプリかどうかを判定。
     *
     * @return bool Android ネイティブアプリなら真。
     */
    private function _is_native_android_app()
    {
        return $this->_is_native_app() && isset($this->_os) && $this->_os === 'Android';
    }
    /**
     * iOS ネイティブアプリかどうかを判定。
     *
     * @return bool iOS ネイティブアプリなら真。
     */
    private function _is_native_ios_app()
    {
        return $this->_is_native_app() && isset($this->_os) && $this->_os === 'iOS';
    }

    /**
     * メンテナンス中かどうかを判定。
     *
     * @return bool メンテナンス中なら真。
     */
    public function is_under_maintenance()
    {
        // 既存フラグを流用。接続元 IP チェックも兼ねている。
        return MAINTAINACE_MODE;
    }

    /**
     * 互換性があるネイティブアプリからのアクセスかどうかを判定。
     *
     * @return bool 互換性があれば真。
     */
    public function is_compatible()
    {
        if ($this->_is_native_android_app() && $this->_get_android_min_version() <= $this->_app_version) {
            return true;
        }
        if ($this->_is_native_ios_app() && $this->_get_ios_min_version() <= $this->_app_version) {
            return true;
        }
        return false;
    }
    /**
     * 事前アクセスモードかどうかを判定。
     *
     * @return bool 事前アクセスなら真
     */
    public function is_preplay_mode()
    {
        if ($this->_is_native_ios_app()) {
            if ($this->_get_ios_store_version() < $this->_app_version) {
                return true;
            }
        }
        return false;
    }

    /**
     * Android ネイティブアプリ最低バージョンを取得。本バージョン未満のアプリアクセスは認めない。
     *
     * @return int Android ネイティブアプリ最低バージョン
     */
    private function _get_android_min_version()
    {
        // 既存設定を流用。
        return ANDROID_MIN_VERSION;
    }
    /**
     * iOS ネイティブアプリ最低バージョンを取得。本バージョン未満のアプリアクセスは認めない。
     *
     * @return int iOS ネイティブアプリ最低バージョン
     */
    private function _get_ios_min_version()
    {
        // 既存設定を流用。
        return IOS_MIN_VERSION;
    }
    /**
     * ストアに並んでいる iOS ネイティブアプリバージョンを取得。本バージョンを超えるアプリアクセスは事前アクセスとする。
     *
     * @return int ストアに並んでいる iOS ネイティブアプリバージョン
     */
    private function _get_ios_store_version()
    {
        // 既存設定を流用。
        return IOS_STORE_VERSION;
    }

    /**
     * （現在の）日時を取得する。
     *
     * @return \Fuel\Core\Date 日時
     */
    public function get_date()
    {
        // Web では初期化時のものをずっと使う。
        return $this->_date_at_initialization;
    }

    /**
     * ログインしているかどうかを判定。
     *
     * @return bool ログインしていれば真。
     */
    public function is_logged_in()
    {
        return isset($this->_user_id);
    }
    /**
     * ユーザ ID を取得する。
     *
     * @return int ユーザ ID
     */
    public function get_user_id()
    {
        return $this->_user_id;
    }
    /**
     * ニックネームを取得する。
     *
     * @return string ニックネーム
     */
    public function get_nickname()
    {
        return $this->_nickname;
    }
    /**
     * アイコン ID を取得する。
     *
     * @return int|null アイコン ID
     */
    public function get_iconId()
    {
        return $this->_icon_id;
    }

    /**
     * クッキーによる認証を行う。
     */
    public function authenticate_by_cookie()
    {
        // 既存認証処理を流用。
        \session_class::auth();
        if (isset($_SESSION['user_id'])) {
            $this->_user_id = $_SESSION['user_id'];
        }
        if (isset($_SESSION['nickname'])) {
            $this->_nickname = $_SESSION['nickname'];
        }
        if (isset($_SESSION['icon'])) {
            $this->_icon_id = $_SESSION['icon'];
        }
    }
    /**
     * セッション識別子による認証を行う。
     */
    public function authenticate_by_session_id()
    {
        $device = Device::find_one_by_secret($this->_session_id);
        if (isset($device)) {
            $user = User::find_one_by_id($device->get_user_id());
            if (isset($user)) {
                $this->_user_id = $user->get_id();
                $this->_nickname = $user->get_nickname();
                $this->_icon_id = $user->get_icon_id();
            }
        }
    }

    /**
     * ビューで利用するパラメータへ変換する。
     *
     * @return array ビュー用パラメータ
     */
    public function to_view_param()
    {
        return [
            'is_under_maintenance' => $this->is_under_maintenance(),
            'is_smartphone_browser_app' => $this->is_smartphone_browser_app(),
            'is_logged_in' => $this->is_logged_in(),
            'user_id' => $this->get_user_id(),
            'nickname' => $this->get_nickname(),
            'icon_id' => $this->get_iconId(),
        ];
    }
}
