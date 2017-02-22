    <footer id="footer">
        <div id="footer_first">
            <img class="no-opacity" src="/img/footer.png" alt="サイコミ｜どこでも人気作品が読める！続々と新作登場!!">
            <div id="store_btn_area">
                <a href="https://itunes.apple.com/jp/app/id1099688273" target="_blank"><img id="ios_store" class="left store_btn" src="/img/ios.png" alt="App Storeからダウンロード"></a>
                <a href="https://play.google.com/store/apps/details?id=com.cygames.cycomi" target="_blank"><img id="android_store" class="right store_btn" src="/img/android.png" alt="Google Playで手にいれよう"></a>
                <div class="clearfix"></div>
            </div>
        </div>
        <div id="footer_second">
            <div class="row">
<?php if (!$context['is_smartphone_browser_app']) { ?>
                <p class="left">&copy; Cygames, Inc.</p>
<?php } ?>
                <ul class="right">
                    <li style="border:none;"><a href="/help.php">ヘルプ</a></li>
                    <li><a href="/contact.php">お問い合わせ</a></li>
                    <li><a href="https://www.cygames.co.jp/policy/" target="_blank">プライバシーポリシー</a></li>
                    <li><a href="/terms.php">利用規約</a></li>
                </ul>
<?php if ($context['is_smartphone_browser_app']) { ?>
                <div class="clearfix"></div>
                <p class="text-center">&copy; Cygames, Inc.</p>
                <p class="text-center">JASRAC 出 9016870002Y45040</p>
<?php } else { ?>
                <p class="clearfix">JASRAC 出 9016870002Y45040</p>
<?php } ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </footer>
