@extends('main.main')
@section('content')
    <style>
        .oneb > img {
            width: 60px;
            height: 60px;
        }
    </style>

    @foreach($mods as $key => $value)
        <div class="card m-3">
            <div class="card-body">
                <a href="/admin/{{ $value['link'] }}" onclick="Page.Go(this.href); return false;">
                    <div class="oneb">
                        <img src="/assets/images/admin/{{ $value['icon'] }}.png" alt="{dir}"
                             title="{dir}"/>{{ $value['description'] }}
                    </div>
                </a>
            </div>
        </div>
    @endforeach

@endsection