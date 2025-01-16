@php
    $route = Route::currentRouteName();

    if ($route == 'register') {
        $title = $page['reg_title']['en'] ?? 'Default title'; // Accessing 'en' value
        $banner = $page['reg_banner'];
        $slogans1 = $page['reg_slogans1']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans2 = $page['reg_slogans2']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans3 = $page['reg_slogans3']['en'] ?? 'Default slogan'; // Accessing 'en' value
    } elseif ($route == 'login') {
        $title = $page['title']['en'] ?? 'Default title'; // Accessing 'en' value
        $banner = $page['banner'];
        $slogans1 = $page['slogans1']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans2 = $page['slogans2']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans3 = $page['slogans3']['en'] ?? 'Default slogan'; // Accessing 'en' value
    } else {
        $title = $page['forget_title']['en'] ?? 'Default title'; // Accessing 'en' value
        $banner = $page['forget_banner'];
        $slogans1 = $page['forget_slogans1']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans2 = $page['forget_slogans2']['en'] ?? 'Default slogan'; // Accessing 'en' value
        $slogans3 = $page['forget_slogans3']['en'] ?? 'Default slogan'; // Accessing 'en' value
    }
@endphp

<div class="login_wrapper_right">
    <div class="login_main_info">
        <h4>การลงทะเบียน</h4>
        <h4>{{ htmlspecialchars($title ?? 'Welcome to Infix Learning Management System') }}</h4>
        <div class="thumb">
            <img src="{{ asset($banner ?? 'frontend/infixlmstheme/img/banner/global.png') }}" alt="">
        </div>
        <div class="other_links">
            <span>{{ htmlspecialchars($slogans1 ?? 'Excellence.') }}</span>
            <span>{{ htmlspecialchars($slogans2 ?? 'Community.') }}</span>
            <span>{{ htmlspecialchars($slogans3 ?? 'Diversity.') }}</span>
        </div>
    </div>
</div>
