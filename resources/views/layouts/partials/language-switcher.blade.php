@auth('web')
    <li class="nav-item d-flex align-items-center">
        <form method="POST" action="{{ route('locale.update') }}"
            class="ui-language-switcher" aria-label="{{ __('common.language.label') }}">
            @csrf
            <label class="sr-only" for="ui-language-select">{{ __('common.language.label') }}</label>
            <select id="ui-language-select" name="locale" class="ui-language-switcher__select"
                aria-label="{{ __('common.language.label') }}" onchange="this.form.submit()">
                @foreach (config('ui-localization.supported_locales') as $locale)
                    <option value="{{ $locale }}" lang="{{ $locale }}" @selected(app()->getLocale() === $locale)>
                        {{ config('ui-localization.labels.' . $locale) }}
                    </option>
                @endforeach
            </select>
        </form>
    </li>
@endauth
