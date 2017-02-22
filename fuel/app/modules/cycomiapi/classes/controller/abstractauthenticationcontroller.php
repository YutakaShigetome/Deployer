<?php

namespace CycomiApi\Controller;

abstract class AbstractAuthenticationController extends AbstractStandardController
{
    public function router($resource, $arguments)
    {
        // 認証チェック。
        if (!$this->_get_context()->is_logged_in()) {
            $this->_set_error_response('is_not_logged_in');
            return;
        }
        parent::router($resource, $arguments);
    }
}
