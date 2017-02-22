<?php
/**
 * @SWG\Info(
 *     version="1.0.0",
 *     title="サイコミ API",
 *     description="一般ユーザから呼ばれるサイコミ API",
 * )
 * @SWG\Parameter(
 *     name="X-Cycomi-Os",
 *     type="string",
 *     description="ネイティブアプリ OS",
 *     in="header",
 * )
 * @SWG\Parameter(
 *     name="X-Cycomi-App-Version",
 *     type="integer",
 *     description="ネイティブアプリバージョン",
 *     in="header",
 * )
 * @SWG\Parameter(
 *     name="X-Cycomi-Device-Id",
 *     type="string",
 *     description="デバイス識別子",
 *     in="header",
 * )
 * @SWG\Parameter(
 *     name="X-Cycomi-Session-Id",
 *     type="string",
 *     description="セッション識別子",
 *     in="header",
 * )
 */
namespace CycomiApi\Controller;

use \Fuel\Core\Config;
use \Fuel\Core\Controller_Rest;
use \Fuel\Core\Fuel;
use \Fuel\Core\Input;
use \Cycomi\Controller\Base;

/**
 * 抽象コントローラークラス。
 *
 * @package CycomiApi\Controller
 */
abstract class AbstractController extends Controller_Rest
{
    use Base;

    public function before()
    {
        parent::before();

        // 既存設定を読み込む。
        $this->_load_legacy_php();

        // Web 設定を読み込む。
        Config::load('web', true);

        // コンテキスト初期化に必要なパラメータをヘッダーから取得。
        $os = Input::headers('X-Cycomi-Os');
        $app_version = Input::headers('X-Cycomi-App-Version');
        $device_id = Input::headers('X-Cycomi-Device-Id');
        $session_id = Input::headers('X-Cycomi-Session-Id');

        // 互換性対応。
        if (!isset($app_version) && isset($_REQUEST['version'])) {
            $app_version = intval($_REQUEST['version']);
        }
        if (!isset($device_id) && isset($_REQUEST['secret'])) {
            $device_id  = $_REQUEST['secret'];
        }

        // コンテキスト初期化。
        $this->_initialize_context($os, $app_version, $device_id, $session_id);
    }

    public function after($response)
    {
        /** @var \Fuel\Core\Response $response */
        $response = parent::after($response);

        if (Fuel::$env === 'local') {
            // 個人開発環境 Swagger からアクセスする際の CORS 対策。
            $response->set_header('Access-Control-Allow-Origin', '*');
        }
        return $response;
    }
}
