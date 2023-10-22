@if((new \Sura\Http\Request)->checkAjax() === false)<!DOCTYPE html>
<html lang="{{ $lang }}" prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article# profile: http://ogp.me/ns/profile#">
<head>
    <title>{{ $home }}</title>
    <link rel="shortcut icon" href="/images/uic.png"/>
</head>
<body>
@endif
@yield('content')
@if((new \Sura\Http\Request)->checkAjax() === false)

</body>
</html>
@endif