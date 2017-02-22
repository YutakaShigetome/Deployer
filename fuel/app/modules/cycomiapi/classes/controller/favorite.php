<?php
namespace CycomiApi\Controller;

use \Fuel\Core\Input;
use \Cycomi\Model\UserFavorite;

/**
 * 本棚を扱うコントローラー。
 *
 * @package CycomiApi\Controller
 * @SWG\Tag(
 *   name="Favorite",
 *   description="本棚関連"
 * )
 */
class Favorite extends AbstractAuthenticationController
{
    /**
     * @SWG\Get(
     *     path="/fw/cycomiapi/favorite/",
     *     summary="本棚に入っている作品を取得する",
     *     tags={"Favorite"},
     *     description="本棚に入っている作品を取得する。",
     *     produces={"application/json"},
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Os"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-App-Version"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Session-Id"),
     *     @SWG\Response(
     *         response=200,
     *         description="成功",
     *         @SWG\Schema(
     *             @SWG\Property(
     *                 property="user_favorites",
     *                 type="array",
     *                 @SWG\Items(ref="#/definitions/UserFavorite"),
     *                 description="お気に入り状況の配列",
     *             )
     *         )
     *     )
     * )
     */
    public function get_index()
    {
        $user_id = $this->_get_context()->get_user_id();

        $user_favorites = UserFavorite::find_by_user_id($user_id);

        return $this->response([
            'user_favorites' => array_map(function ($user_favorite) { return $user_favorite->to_primitive_array(); }, $user_favorites),
        ]);
    }

    /**
     * @SWG\Post(
     *     path="/fw/cycomiapi/favorite/add",
     *     summary="作品を本棚に追加する",
     *     tags={"Favorite"},
     *     description="作品を本棚に追加する。",
     *     produces={"application/json"},
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Os"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-App-Version"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Session-Id"),
     *     @SWG\Parameter(
     *         name="title_id",
     *         type="integer",
     *         description="作品 ID",
     *         in="formData",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="成功",
     *         @SWG\Schema(
     *             @SWG\Property(
     *                 property="user_favorite",
     *                 ref="#/definitions/UserFavorite",
     *                 description="お気に入り状況",
     *             )
     *         )
     *     )
     * )
     */
    public function post_add()
    {
        $title_id = Input::post('title_id', null);
        $title_id = intval($title_id);

        $user_id = $this->_get_context()->get_user_id();

        return $this->_get_context()->manga_transaction(function ($db) use ($user_id, $title_id) {
            $user_favorite = UserFavorite::create_by_user_id_and_title_id($user_id, $title_id);
            if (isset($user_favorite)) {
                $db->commit_transaction();
            }
            return $this->response([
                'user_favorite' => isset($user_favorite) ? $user_favorite->to_primitive_array() : null,
            ]);
        });
    }

    /**
     * @SWG\Post(
     *     path="/fw/cycomiapi/favorite/remove",
     *     summary="作品を本棚から削除する",
     *     tags={"Favorite"},
     *     description="作品を本棚から削除する。",
     *     produces={"application/json"},
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Os"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-App-Version"),
     *     @SWG\Parameter(ref="#/parameters/X-Cycomi-Session-Id"),
     *     @SWG\Parameter(
     *         name="title_id",
     *         type="integer",
     *         description="作品 ID",
     *         in="formData",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="成功",
     *         @SWG\Schema(
     *             @SWG\Property(
     *                 property="user_favorite",
     *                 ref="#/definitions/UserFavorite",
     *                 description="お気に入り状況",
     *             )
     *         )
     *     )
     * )
     */
    public function post_remove()
    {
        $title_id = Input::post('title_id', null);
        $title_id = intval($title_id);

        $user_id = $this->_get_context()->get_user_id();

        return $this->_get_context()->manga_transaction(function ($db) use ($user_id, $title_id) {
            // ロックを掛けて取得。
            $user_favorite = UserFavorite::find_locked_one_by_user_id_and_title_id($user_id, $title_id);
            // 削除。
            if (isset($user_favorite) && $user_favorite->delete()) {
                $db->commit_transaction();
            }

            return $this->response([
                'user_favorite' => isset($user_favorite) ? $user_favorite->to_primitive_array() : null,
            ]);
        });
    }
}
