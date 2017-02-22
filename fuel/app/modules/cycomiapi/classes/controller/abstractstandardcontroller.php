<?php
namespace CycomiApi\Controller;

/**
 * 標準コントローラー抽象クラス。
 *
 * @package CycomiApi\Controller
 */
abstract class AbstractStandardController extends AbstractController
{
    public function before()
    {
        parent::before();

        // メンテナンスチェック。
        if ($this->_get_context()->is_under_maintenance()) {
            return;
        }
        // 互換性（バージョン）チェック。
        if (!$this->_get_context()->is_compatible()) {
            return;
        }

        // 認証。
        $this->_get_context()->authenticate_by_session_id();
    }

    public function router($resource, $arguments)
    {
        // メンテナンスチェック。
        if ($this->_get_context()->is_under_maintenance()) {
            $this->_set_error_response('is_under_maintenance');
            return;
        }
        // 互換性（バージョン）チェック。
        if (!$this->_get_context()->is_compatible()) {
            $this->_set_error_response('is_not_compatible');
            return;
        }
        parent::router($resource, $arguments);
    }

    /**
     * エラーレスポンスをセットする。
     *
     * @param string $error エラー内容。
     */
    protected function _set_error_response($error)
    {
        $this->format = 'json';
        $this->response(['error' => $error], 400);
    }
}
