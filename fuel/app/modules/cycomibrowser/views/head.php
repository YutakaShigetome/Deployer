        <meta charset="utf-8"/>
<?php if ($context['is_smartphone_browser_app']) { ?>
        <meta name="viewport" content="width=device-width,user-scalable=no,maximum-scale=1" />
<?php } ?>

        <meta name="keywords" content="サイコミ,CyComics,マンガ,漫画,Cygames,無料,無料配信,アプリ,ゲームコミカライズ,オリジナル">
        <meta name="description" content="新作マンガを毎日配信！無料で読める漫画アプリ「サイコミ」">

        <link rel="shortcut icon" href="/img/favicon.ico" >
        <link rel="apple-touch-icon" href="/img/icon.jpg">

        <meta property="og:title" content="無料マンガ配信サービス「サイコミ」公式サイト | Cygames">
        <meta property="og:image" content="https://cycomi.com/img/icon.jpg">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://cycomi.com">
        <meta property="og:description" content="新作マンガを毎日配信！無料で読める漫画アプリ「サイコミ」">
<?php if ($context['is_under_maintenance']) { ?>
        <meta http-equiv="refresh" content="15">
<?php } ?>
        <link rel="stylesheet" media="all" href="/style/reset.css"/>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" media="all" href="/style/slick.css"/>
        <link rel="stylesheet" media="all" href="/style/slick-theme.css"/>
<?php if ($context['is_smartphone_browser_app']) { ?>
        <link rel="stylesheet" href="/style/sp.css">
<?php } else { ?>
        <link rel="stylesheet" href="/style/main.css">
<?php } ?>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="/js/slick.js"></script>
        <script src="/js/slider.js"></script>
        <script src="/js/footerFixed.js"></script>
