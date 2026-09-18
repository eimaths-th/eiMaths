@auth('web')
    <li class="nav-item d-flex align-items-center">
        <form method="POST" action="{{ route('locale.update') }}"
            class="ui-language-switcher" aria-label="{{ __('common.language.label') }}">
            @csrf
            @foreach (config('ui-localization.supported_locales') as $locale)
                @php($is_current = app()->getLocale() === $locale)
                <button type="submit" name="locale" value="{{ $locale }}"
                    class="btn btn-sm {{ $is_current ? 'btn-primary' : 'btn-light' }}"
                    lang="{{ $locale }}" aria-pressed="{{ $is_current ? 'true' : 'false' }}">
                    @if ($is_current)
                        <i class="fas fa-check" aria-hidden="true"></i>
                    @endif
                    {{ config('ui-localization.labels.' . $locale) }}
                </button>
            @endforeach
        </form>
    </li>
@endauth
