@php
    $slug = \Illuminate\Support\Str::slug($sign['manufacturer'] ?? '');
    $logo = $slug && file_exists(public_path("images/logos/{$slug}.png"))
        ? asset("images/logos/{$slug}.png")
        : asset('images/logos/berelas.png');
@endphp

<div class="sign">
    <img class="sign-logo" src="{{ $logo }}" alt="">

    <div class="sign-device">{{ $sign['device'] }}</div>

    <div class="sign-spec">
        <h3 class="sign-title">{{ $sign['title'] }}</h3>
        <div class="sign-rule"></div>
        <p class="sign-lines">{{ $sign['lines'] }}</p>
    </div>

    <div class="sign-price">
        <div class="sign-prefix">{{ $sign['prefix'] }}</div>
        <div class="sign-amount">{{ $sign['price'] }}</div>
    </div>
</div>