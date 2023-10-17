<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width"/>
@include('main.email_style', [])
<table class="body-wrap">
    <tr>
        <td class="container">
            <table>
                <tr>
                    <td class="content">
                        <p>@_e('restore_1'){{ $user_name }}!</p>
                        <p>@_e('restore_2')</p>
                        <p>@_e('restore_3')</p>
                        <table>
                            <tbody><tr>
                                <td align="center">
                                    <p>
                                        <!-- {{ $hash }} -->
                                        <a href="{{ $home_url }}restore?hash={{ $hash }}" class="button">@_e('restore_3')</a>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p>@_e('restore_5')<em>{{ $site_name }}</em> {{ $home_url }}.</p>
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
                        <p>@_e('restore_6')<a href="{{ $home_url }}">{{ $site_name }}</a></p>
                        <p><a href="mailto:">{{ $admin_mail }}</a></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>