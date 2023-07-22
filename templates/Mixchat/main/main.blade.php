@if((new \Sura\Http\Request)->checkAjax() === false)<!DOCTYPE html>
<html lang="{{ $lang }}" prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article# profile: http://ogp.me/ns/profile#">
<head>
    <title>{{ $title }}</title>
    <link rel="shortcut icon" href="/images/uic.png"/>
    <link rel="stylesheet" href="/assets/css/style.css?12" type="text/css">
{{--    <link href="/css/style.css" rel="stylesheet">--}}
    {{ $js }}
    @if(!$logged)
        <script type="text/javascript" src="/js/reg.js"></script>
    @endif
{{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">--}}

</head>
<body class="body-bg-fon">
<header class="d-header">
    <div class="wrap">
        <div class="d-header_contents">

            <div id="togglemenu" class="mb-none mr10">
                <i class="bi-list gray-600 text-xl"></i>
            </div>

            <div class="menu__button none mb-block mr10">
                <i class="bi-list gray-600 text-xl"></i>
            </div>

            <a title="{{ $home }}" class="logo" href="/">{{ $home }}</a>

{{--            <div class="ml45 mb-ml10 relative w-100">--}}
{{--                <form class="form mb-none" method="get" action="?php url('search.go'); ?>">--}}
{{--                    <input type="text" name="q" autocomplete="off" id="find" placeholder="?php  __('app.find'); ?>" class="search">--}}
{{--                </form>--}}
{{--                <div class="absolute box-shadow bg-white p15 pt0 mt5 br-rd3 none" id="search_items"></div>--}}
{{--            </div>--}}

            @if(!$logged)
{{--            <div class="flex right items-center">--}}
{{--                <div id="toggledark" class="header-menu-item mb-none ml45">--}}
{{--                    <i class="bi-brightness-high gray-600 text-xl"></i>--}}
{{--                </div>--}}
{{--                ?php if (config('general.invite') == false) : ?>--}}
{{--                <a class="w94 gray ml45 mr15 mb-mr5 mb-ml5 block" href="?php url('register'); ?>">--}}
{{--                    ?php __('app.registration'); ?>--}}
{{--                </a>--}}
{{--                ?php endif; ?>--}}
{{--                <a class="w94 btn btn-outline-primary ml20" href="?php url('login'); ?>">--}}
{{--                    ?php __('app.sign_in'); ?>--}}
{{--                </a>--}}
{{--            </div>--}}
            @else

            <div class="flex right ml45 mb-ml0 items-center text-xl">

{{--               Html::addPost($facet)--}}

                <div id="toggledark" class="only-icon ml45 mb-ml20">
                    <i class="bi-brightness-high gray-600"></i>
                </div>

{{--                <a class="gray-600 ml45 mb-ml20" href="?php url('notifications'); ?>">--}}
{{--                    ?php $notif = \App\Controllers\NotificationController::setBell(UserData::getUserId()); ?>--}}
{{--                    ?php if (!empty($notif)) : ?>--}}
{{--                    ?php if ($notif['action_type'] == 1) : ?>--}}
{{--                    <i class="bi-envelope red"></i>--}}
{{--                    ?php else : ?>--}}
{{--                    <i class="bi-bell-fill red"></i>--}}
{{--                    ?php endif; ?>--}}
{{--                    ?php else : ?>--}}
{{--                    <i class="bi-bell"></i>--}}
{{--                    ?php endif; ?>--}}
{{--                </a>--}}

{{--                <div class="ml45 mb-ml20">--}}
{{--                    <div class="trigger">--}}
{{--                        ?php Html::image(UserData::getUserAvatar(), UserData::getUserLogin(), 'ava-base', 'avatar', 'small'); ?>--}}
{{--                    </div>--}}
{{--                    <ul class="dropdown">--}}
{{--                        ?php Tpl::insert('/_block/navigation/menu', ['type' => $type, 'list' => config('navigation/menu.user')]); ?>--}}
{{--                    </ul>--}}
{{--                </div>--}}
            </div>
            @endif
        </div>
    </div>
</header>
<div id="contentWrapper" class="wrap">
    <nav class="menu__left mb-none">
        <ul class="menu sticky top-sm">
            <li>
                <a aria-current="page" class="active" href="/"><i class="bi-sort-down"></i>Лента</a>
            </li>
            <li>
                <a href="/topics"><i class="bi-columns-gap"></i>Темы</a>
            </li>
            <li>
                <a href="/blogs"><i class="bi-journals"></i>Блоги</a>
            </li>
{{--            <li>--}}
{{--                <a href="/users"><i class="bi-people"></i>Участники</a>--}}
{{--            </li>--}}
            <li>
                <a href="/answers"><i class="bi-chat-dots"></i>Ответы</a>
            </li>
            <li>
                <a href="/comments"><i class="bi-chat-quote"></i>Комментарии</a>
            </li>
            <li>
                <a href="/web"><i class="bi-link-45deg"></i>Каталог</a>
            </li>
        </ul>
    </nav>
    <main>

    </main>
    <aside>

    </aside>
</div>

@if(!$logged)
    {{-- todo update --}}
    <script>
        function boxlogin() {
            $.post('/login', function (d) {
                Box.Show('lang', 300, lang_login, d, lang_box_cancel);
            });
        }
    </script>
@endif
@if($available === 'main')
{{--    <small style="color:#ccc;padding:3px;position:absolute">Sura</small>--}}
{{--    <div class="header_flex">--}}
{{--        <a href="/" class="new_logo" onClick="Page.Go(this.href); return false" style="margin-top:-10px">{{ $home }}</a>--}}
{{--        <div class="new_descr">@_e('site_desc')<br/>--}}
{{--        </div>--}}
{{--        <div class="new_but fl_r cursor_pointer" onClick="boxlogin();" style="margin-top:-32px">@_e('login')</div>--}}
{{--    </div>--}}
@else
    <div class="head none">
        <div class="wr">
            <div class="headwr">
                @if($logged)
                    <a href="/news" onClick="Page.Go(this.href); return false">
                        <div class="logo">{{ $home }}</div>
                    </a>
                @else
                    <div class="logo"></div>
                @endif
                <div class="headhr"></div>
                @if($logged)
                <!--search-->
                    {{--<div id="seNewB">
                            <input type="text" value="Поиск" class="fave_input search_input"
                                   onBlur="if(this.value == '' || this.value=='Поиск'){this.value='Поиск';this.style.color = '#c1cad0'}"
                                   onFocus="if(this.value=='Поиск'){this.value='';this.style.color = '#000'}"
                                   onKeyPress="if(event.keyCode == 13) gSearch.go();"
                                   onKeyUp="FSE.Txt()"
                                   onClick="if(this.value != 0) $('.fast_search_bg').show()"
                                   id="query" maxlength="65"/>
                            <div id="search_types">
                                <input type="hidden" value="1" id="se_type"/>
                                <div class="search_type" id="search_selected_text"
                                     onClick="gSearch.open_types('#sel_types'); return false">@_e('by_people')
                                </div>
                                <div class="search_alltype_sel no_display" id="sel_types">
                                    <div id="1"
                                         onClick="gSearch.select_type(this.id, 'по людям'); FSE.GoSe($('#query').val()); return false"
                                         class="search_type_selected">@_e('by_people')
                                    </div>
                                    <div id="2"
                                         onClick="gSearch.select_type(this.id, 'по видеозаписям'); FSE.GoSe($('#query').val()); return false">
                                        по видеозаписям
                                    </div>
                                    <div id="3"
                                         onClick="gSearch.select_type(this.id, 'по заметкам');  FSE.GoSe($('#query').val()); return false">
                                        по заметкам
                                    </div>
                                    <div id="4"
                                         onClick="gSearch.select_type(this.id, 'по сообществам'); FSE.GoSe($('#query').val()); return false">
                                        по сообществам
                                    </div>
                                    <div id="5"
                                         onClick="gSearch.select_type(this.id, 'по аудиозаписям');  FSE.GoSe($('#query').val()); return false">
                                        по аудиозаписям
                                    </div>
                                </div>
                            </div>
                            <div class="fast_search_bg no_display" id="fast_search_bg">
                                <a href="/" style="padding:12px;background:#eef3f5" onClick="gSearch.go(); return false"
                                   onMouseOver="FSE.ClrHovered(this.id)" id="all_fast_res_clr1">
                                    <text>Искать</text>
                                    <b id="fast_search_txt"></b>
                                    <div class="fl_r fast_search_ic"></div>
                                </a>
                                <span id="reFastSearch"></span>
                            </div>
                        </div>--}}
                    <!--/search-->
                @endif
                <div class="headmenu fl_r none">
                    <div id="audioMP"></div>
                    @if($logged)
                        <a href="/index.php?go=search&online=1"
                           onClick="Page.Go(this.href); return false">@_e('main_tpl_people')</a>
                        <a href="/index.php?go=search&type=4" onClick="Page.Go(this.href); return false">@_e('main_tpl_lang_1')</a>
                        <a href="/audio" onClick="Page.Go(this.href); return false" id="fplayer_pos">@_e('main_music')</a>
                        <a href="/support?act=new" onClick="Page.Go(this.href); return false">@_e('main_support')</a>
                        <a href="/?act=logout">@_e('main_logout')</a>
                    @else
                        <a href="/">главная</a>
                        <a href="/" onClick="boxlogin(); return false">@_e('login')</a>
                        <a href="/" onClick="reg.rules(); return false">@_e('main_signup')</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
<div class="wr none">
    <div class="page">
        @if($logged)
            <div class="panelUser">
                <a href="{{ $my_page_link }}" onClick="Page.Go(this.href); return false;">
                    <div class="ic_profile" id="myprof1"
                         onMouseOver="myhtml.title('1', 'Моя Страница', 'myprof')"></div>
                </a>
                <a href="/messages" onClick="Page.Go(this.href); return false;">
                    <div class="ic_msg" id="myprof2" onMouseOver="myhtml.title('2', 'Сообщения', 'myprof')">
                        <div id="new_msg">{{ $msg }}</div>
                    </div>
                </a>
                <a href="/friends{requests-link}" onClick="Page.Go(this.href); return false;" id="requests_link">
                    <div class="ic_friends" id="myprof3" onMouseOver="myhtml.title('3', 'Друзья', 'myprof')">
                        <div id="new_requests">{{ $demands }}</div>
                    </div>
                </a>
                <a href="/albums/{my-id}" onClick="Page.Go(this.href); return false;" id="requests_link_new_photos">
                    <div class="ic_photo" id="myprof4" onMouseOver="myhtml.title('4', 'Фотографии', 'myprof')">
                        <div id="new_photos">{{ $new_photos }}</div>
                    </div>
                </a>
                <a href="/fave" onClick="Page.Go(this.href); return false;">
                    <div id="myprof5" class="ic_fave" onMouseOver="myhtml.title('5', 'Закладки', 'myprof')"></div>
                </a>
                <a href="/videos" onClick="Page.Go(this.href); return false;">
                    <div id="myprof6" class="ic_video" onMouseOver="myhtml.title('6', 'Видеозаписи', 'myprof')"></div>
                </a>
                <a href="{{ $groups_link }}" onClick="Page.Go(this.href); return false;" id="new_groups_lnk">
                    <div id="myprof8" class="ic_groups" onMouseOver="myhtml.title('8', 'Сообщества', 'myprof')">
                        <div id="new_groups">{{ $new_groups }}</div>
                    </div>
                </a>
                <a href="/news{{ $news_link }}" onClick="Page.Go(this.href); return false;" id="news_link">
                    <div id="myprof9" class="ic_news" onMouseOver="myhtml.title('9', 'Новости', 'myprof')">
                        <div id="new_news">{{ $new_news }}</div>
                    </div>
                </a>
                <a href="/notes" onClick="Page.Go(this.href); return false;">
                    <div id="myprof10" class="ic_notes" onMouseOver="myhtml.title('10', 'Заметки', 'myprof')"></div>
                </a>
                <a href="/settings" onClick="Page.Go(this.href); return false;">
                    <div id="myprof11" class="ic_settings"
                         onMouseOver="myhtml.title('11', 'Настройки', 'myprof')"></div>
                </a>
                <a href="/support" onClick="Page.Go(this.href); return false;">
                    <div id="myprof12" class="ic_support" onMouseOver="myhtml.title('12', 'Помощь', 'myprof')">
                        <div id="new_support">{{ $new_support }}</div>
                    </div>
                </a>
                <a href="{{ $ubm_link }}" onClick="Page.Go(this.href); return false;" id="ubm_link">
                    <div id="myprof13" class="ic_balance" onMouseOver="myhtml.title('13', 'Баланс', 'myprof')">
                        <div id="new_ubm">{{ $new_ubm }}</div>
                    </div>
                </a>
            </div>
        @endif
        {{--        <div id="audioPlayer"></div>--}}
        <div id="page">
            @endif
            @yield('content')
            @if((new \Sura\Http\Request)->checkAjax() === false)
                <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="footer none">
    <a href="/index.php?go=search&online=1"
       onClick="Page.Go(this.href); return false">@_e('main_tpl_people')</a>
    <a href="/index.php?go=search&type=4" onClick="Page.Go(this.href); return false">@_e('main_tpl_lang_1')</a>
    <a href="/support" onClick="Page.Go(this.href); return false">@_e('main_support')</a>
    <div>Mixchat &copy; 2023
        <a class="cursor_pointer" onClick="trsn.box();"
           onMouseOver="myhtml.title('1', '@_e('lang_toltip')', 'langTitle', 1)"
           id="langTitle1">{{ $lang }}</a>
    </div>
</div>
@if($logged)
    <script type="text/javascript">
        function upClose(xnid) {
            $('#event' + xnid).remove();
            $('#updates').css('height', $('.update_box').size() * 123 + 'px');
        }

        function GoPage(event, p) {
            var oi = (event.target) ? event.target.id : ((event.srcElement) ? event.srcElement.id : null);
            if (oi == 'no_ev' || oi == 'update_close' || oi == 'update_close2') return false;
            else {
                pattern = new RegExp(/photo[0-9]/i);
                pattern2 = new RegExp(/video[0-9]/i);
                if (pattern.test(p))
                    Photo.Show(p);
                else if (pattern2.test(p)) {
                    vid = p.replace('/video', '');
                    vid = vid.split('_');
                    videos.show(vid[1], p, location.href);
                } else
                    Page.Go(p);
            }
        }

        $(document).ready(function () {
            setInterval(function () {
                $.post('/updates', function (d) {
                    row = d.split('|');
                    if (d && row[1]) {
                        if (row[0] == 1) uTitle = 'Новый ответ на стене';
                        else if (row[0] == 2) uTitle = 'Новый комментарий к фотографии';
                        else if (row[0] == 3) uTitle = 'Новый комментарий к видеозаписи';
                        else if (row[0] == 4) uTitle = 'Новый комментарий к заметке';
                        else if (row[0] == 5) uTitle = 'Новый ответ на Ваш комментарий';
                        else if (row[0] == 6) uTitle = 'Новый ответ в теме';
                        else if (row[0] == 7) uTitle = 'Новый подарок';
                        else if (row[0] == 8) uTitle = 'Новое сообщение';
                        else if (row[0] == 9) uTitle = 'Новая оценка';
                        else if (row[0] == 10) uTitle = 'Ваша запись понравилась';
                        else if (row[0] == 11) uTitle = 'Новая заявка';
                        else if (row[0] == 12) uTitle = 'Заявка принята';
                        else if (row[0] == 13) uTitle = 'Подписки';
                        else uTitle = 'Событие';
                        if (row[0] == 8) {
                            sli = row[6].split('/');
                            tURL = (location.href).replace('https://' + location.host, '').replace('/', '').split('#');
                            if (!sli[2] && tURL[0] == 'messages') return false;
                            if ($('#new_msg').text()) msg_num = parseInt($('#new_msg').text().replace(')', '').replace('(', '')) + 1;
                            else msg_num = 1;
                            $('#new_msg').html("<div class=\"ic_newAct\">" + msg_num + "</div>");
                        }
                        setTimeout('upClose(' + row[4] + ');', 10000);
                        temp = '<div class="update_box cursor_pointer" id="event' + row[4] + '" onClick="GoPage(event, \'' + row[6] + '\'); upClose(' + row[4] + ')"><div class="update_box_margin"><div style="height:19px"><span>' + uTitle + '</span><div class="update_close fl_r no_display" id="update_close" onMouseDown="upClose(' + row[4] + ')"><div class="update_close_ic" id="update_close2"></div></div></div><div class="clear"></div><div class="update_inpad"><a href="/u' + row[2] + '" onClick="Page.Go(this.href); return false"><div class="update_box_marginimg"><img src="' + row[5] + '" id="no_ev" /></div></a><div class="update_data"><a id="no_ev" href="/u' + row[2] + '" onClick="Page.Go(this.href); return false">' + row[1] + '</a>&nbsp;&nbsp;' + row[3] + '</div></div><div class="clear"></div></div></div>';
                        $('#updates').html($('#updates').html() + temp);
                        var beepThree = $("#beep-three")[0];
                        document.getElementById("beep-three").volume = 0.7;
                        beepThree.play();
                        if ($('.update_box').size() <= 5) $('#updates').animate({'height': (123 * $('.update_box').size()) + 'px'});
                        if ($('.update_box').size() > 5) {
                            evFirst = $('.update_box:first').attr('id');
                            $('#' + evFirst).animate({'margin-top': '-123px'}, 400, function () {
                                $('#' + evFirst).fadeOut('fast', function () {
                                    $('#' + evFirst).remove();
                                });
                            });
                        }
                    }
                });
            }, 3500);
        });
    </script>
    <div class="no_display none">
        <audio id="beep-three" controls preload="auto">
            <source src="/images/soundact.ogg">
        </audio>
    </div>
@endif
<div id="updates"></div>
<div id="audioPlayList"></div>
<footer class="box-shadow-top">
    <div class="wrap">
        <div class="left">
            <div class="mb5">
                Miaxchat © 2023        <span class="mb-none">— сообщество</span>
            </div>
        </div>
        <div class="flex right">
            <ul class="mb-none">
                <li><a href="/blogs">Блоги</a></li>
                <li><a href="/topics">Темы</a></li>
                <li><a href="/web">Каталог</a></li>
            </ul>
            <ul class="mb-none">
                <li><a href="/users">Участники</a></li>
                <li><a href="/answers">Ответы</a></li>
                <li><a href="/comments">Комментарии</a></li>
            </ul>
            <ul class="mb-pl0">
                <li><a href="/info/article/information">Информация</a></li>
                <li><a href="/info/article/privacy">Конфиденциальность</a></li>
            </ul>
        </div>
    </div>
</footer>
</body>
</html>
@endif