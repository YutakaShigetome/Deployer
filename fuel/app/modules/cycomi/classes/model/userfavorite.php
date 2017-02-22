<?php
namespace Cycomi\Model;

/**
 * お気に入り状況を扱うクラス。
 *
 * @package Cycomi\Model
 * @SWG\Definition(
 *     definition="UserFavorite",
 *     @SWG\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ユーザー ID",
 *     ),
 *     @SWG\Property(
 *         property="title_id",
 *         type="integer",
 *         description="作品 ID",
 *     ),
 *     @SWG\Property(
 *         property="created",
 *         type="integer",
 *         description="お気に入り登録日時（Unix 時間）",
 *     ),
 * )
 */
class UserFavorite extends AbstractMangaOrm
{
    protected static $_table_name = 'user_favorites';
    protected static $_columns = [
        'user_id' => ['uint'],
        'title_id' => ['uint'],
        'created' => ['timestamp'],
    ];
    protected static $_primary_key = [
        'user_id',
        'title_id',
    ];
    protected static $_created_at = 'created';


    /**
     * レコード PK 参照により単体インスタンス化する。
     *
     * @param int $user_id ユーザ ID
     * @param int $title_id タイトル ID
     * @return self|null インスタンス化したモデル
     */
    public static function find_one_by_user_id_and_title_id($user_id, $title_id)
    {
        return static::_find_one_by_pk([
            'user_id' => $user_id,
            'title_id' => $title_id,
        ]);
    }

    /**
     * ロック付きレコード PK 参照により単体インスタンス化する。
     *
     * @param int $user_id ユーザ ID
     * @param int $title_id タイトル ID
     * @return self|null インスタンス化したモデル
     */
    public static function find_locked_one_by_user_id_and_title_id($user_id, $title_id)
    {
        return static::_find_locked_one_by_pk([
            'user_id' => $user_id,
            'title_id' => $title_id,
        ]);
    }

    /**
     * ユーザ ID 参照でインスタンス化する。
     *
     * @param int $user_id ユーザ ID
     * @return self[] インスタンス化したモデル配列
     */
    public static function find_by_user_id($user_id)
    {
        return static::_find([
            'user_id' => $user_id,
        ]);
    }

    /**
     * レコードを作成しつつ、インスタンス化する。
     *
     * @param int $user_id ユーザ ID
     * @param int $title_id タイトル ID
     * @return self|null
     */
    public static function create_by_user_id_and_title_id($user_id, $title_id)
    {
        return static::create([
            'user_id' => $user_id,
            'title_id' => $title_id,
        ]);
    }
}
