<?php

namespace CycomiApi\Controller;

use \Fuel\Core\Input;

/**
 * 横断的な設定を扱うコントローラー。
 *
 * @package CycomiApi\Controller
 * @SWG\Tag(
 *   name="Config",
 *   description="横断的な設定関連"
 * )
 */
class Config extends AbstractController
{
    /**
     * @SWG\Get(
     *     path="/fw/cycomiapi/config/environment",
     *     summary="環境情報を取得する",
     *     tags={"Config"},
     *     description="Apple 審査等、リリース前アプリからのアクセスを専用環境へ向けるための情報を提供する。アプリから送られてくるバージョンを元にリリース済みかどうかを判断し、リリース済みアプリからのアクセスであれば現環境情報を、リリース前アプリからのアクセスであれば事前プレイ環境情報を返す。",
     *     produces={"application/json"},
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Os"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-App-Version"),
     *     @SWG\Response(
     *         response=200,
     *         description="成功",
     *         @SWG\Schema(
     *             @SWG\Property(
     *                 property="host",
     *                 type="string",
     *                 description="接続先ホスト",
     *             ),
     *         ),
     *     )
     * )
     */
    public function get_environment()
    {
        // アクセス元アプリのバージョンから事前アクセスかどうかを判断。
        $preplay_mode = $this->_get_context()->is_preplay_mode();
        // 環境情報。
        $scheme = 'https://';
        $current_host = Input::server('HTTP_HOST');
        $preplay_host = \Fuel\Core\Config::get('web.preplay_host', $current_host);
        // レスポンス生成。
        $response = [
            'host' => $scheme . ($preplay_mode ? $preplay_host : $current_host),
        ];
        return $this->response($response);
    }
}
