<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width"/>
@include('main.email_style', [])
<table class="body-wrap">
    <tr>
        <td class="container">
            <table>
                <tr>
                    <td class="content">
                        <p>Здравствуйте, {{ $user_name }}!</p>
                        <p>Кто-то пытался войти в ваш аккаунт.</p>
                        <p>Если это были вы, подтвердите свою личность с помощью следующего кода:</p>
                        <table>
                            <tbody><tr>
                                <td align="center">
                                    <p>
                                        <!-- {{ $hash }} -->
                                        <a href="{{ $home_url }}restore?hash={{ $hash }}" class="button">Сменить пароль</a>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p>С уважением, <em>{{ $site_name }}</em> {{ $home_url }}.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="container">
            <table>
                <tr>
                    <td class="content footer" align="center">
                        <p>Sent by <a href="{{ $home_url }}">{{ $site_name }}</a></p>
                        <p><a href="mailto:">{{ $admin_mail }}</a></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>