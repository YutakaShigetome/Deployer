<?php
namespace CycomiApi\Controller;

use Fuel\Core\Controller;
use Fuel\Core\Config;
use Fuel\Core\Fuel;

/**
 * Swagger 関連を扱うコントローラー。
 *
 * @package CycomiApi\Controller
 */
class Swagger extends Controller
{
    /**
     * swagger.json を返す API。
     *
     * @return string swagger.json
     */
    public function get_json()
    {
        return file_get_contents(__DIR__ . '/../../../../../../swagger.json');
    }

    /**
     * CORS 対策のためだけのメソッド。
     *
     * @return null
     */
    public function options_preflight()
    {
        return null;
    }

    public function after($response)
    {
        /** @var \Fuel\Core\Response $response */
        $response = parent::after($response);
        if (Fuel::$env === 'local') {
            // 個人開発環境 Swagger からアクセスする際の CORS 対策。
            $response->set_header('Access-Control-Allow-Origin', '*');
            $response->set_header(
                'Access-Control-Allow-Headers',
                'X-Cycomi-Os, X-Cycomi-App-Version, X-Cycomi-Device-Id, X-Cycomi-Session-Id'
            );
        }
        return $response;
    }
}
