    <header>
        <div id="main-navigation-group">
            <h1><a href="/"><img src="/img/logo_small.png" alt="サイコミ"/></a></h1>
<?php if (!$context['is_smartphone_browser_app']) { ?>
<?php     if ($context['is_logged_in']) {?>
            <nav id="user-navigation-login">
                <div class="left menu">
                    <ul>
                        <li>
                            <a href="#">
                                <img class="left no-opacity" src="<?=$context['icon_id']?>" style="width:40px;">
                                <p class="left nickname-header" style="padding:0 14px;"><?=$context['nickname']?>さん</p>
                                <img class="img-menu" src="/img/menu.png">
                            </a>
                            <ul class="user-menu">
                                <li class="main">
                                    <div class="right"></div>
                                </li>
                                <li class="top-radius">
                                    <a class="top-radius" href="/bookmark.php">&raquo; 本棚</a>
                                </li>
                                <li>
                                    <a href="/setting.php">&raquo; 設定</a>
                                </li>
                                <li class="bottom-radius">
                                    <a class="bottom-radius" href="/logout.php">&raquo; ログアウト</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
<?php     } else { ?>
            <nav id="user-navigation">
                <a href="/login.php" rel="nofollow">ログイン</a>
                <a href="/register.php" rel="nofollow">無料会員登録</a>
            </nav>
<?php     } ?>
<?php } ?>
        </div>
    </header>
