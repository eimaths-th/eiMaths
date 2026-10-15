@extends('layouts.app')

@section('style')
    <style>
        .profile-avatar { display: inline-flex; align-items: center; justify-content: center; width: 76px; height: 76px; border-radius: 50%; background: #eaf4ff; color: #1668ad; font-size: 2rem; font-weight: 600; }
        .profile-account-details dd, .profile-account-name { overflow-wrap: anywhere; }
        .profile-account-details dt { color: #6c757d; font-size: .875rem; font-weight: 400; margin-bottom: .25rem; }
        .profile-account-details dd { margin-bottom: 1.25rem; }
    </style>
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="h4 mb-1">{{ __('profile.title') }}</h1>
            <p class="text-muted mb-0">{{ __('profile.subtitle') }}</p>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success" role="status">
                    <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>{{ __('profile.saved') }}
                </div>
            @endif
            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <span class="profile-avatar mb-3" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($user->name), 0, 1, 'UTF-8'), 'UTF-8') }}</span>
                                <h2 class="h5 profile-account-name">{{ $user->name }}</h2>
                                <p class="text-muted profile-account-name mb-0">{{ $user->email }}</p>
                            </div>
                            <h3 class="h6 border-bottom pb-2 mb-3">{{ __('profile.account') }}</h3>
                            <dl class="profile-account-details mb-0">
                                <dt><i class="fas fa-building mr-1" aria-hidden="true"></i>{{ __('profile.centre') }}</dt>
                                <dd>{{ $user->department?->name ?? '—' }}</dd>
                                <dt><i class="fas fa-users mr-1" aria-hidden="true"></i>{{ __('profile.department') }}</dt>
                                <dd>{{ $user->position?->name ?? '—' }}</dd>
                                <dt><i class="fas fa-user-shield mr-1" aria-hidden="true"></i>{{ __('profile.role') }}</dt>
                                <dd>
                                    @forelse ($user->roles as $role)
                                        <span class="badge badge-light border mr-1 mb-1">{{ $role->display_name ?: $role->name }}</span>
                                    @empty
                                        —
                                    @endforelse
                                </dd>
                                <dt>{{ __('profile.member_since') }}</dt>
                                <dd class="mb-0">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</dd>
                            </dl>
                        </div>
                        <div class="card-footer text-muted small">{{ __('profile.access_hint') }}</div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="far fa-user mr-1" aria-hidden="true"></i>{{ __('profile.personal') }}</h2>
                        </div>
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="profile-name">{{ __('profile.name') }}</label>
                                    <input id="profile-name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"
                                        @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                                    @error('name')<div id="profile-name-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="profile-email">{{ __('profile.email') }}</label>
                                    <input id="profile-email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email"
                                        @error('email') aria-invalid="true" aria-describedby="profile-email-error" @enderror>
                                    @error('email')<div id="profile-email-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label for="profile-current-password">{{ __('profile.current_password') }}</label>
                                    <input id="profile-current-password" name="current_password" type="password"
                                        class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password"
                                        aria-describedby="profile-password-hint @error('current_password') profile-password-error @enderror"
                                        @error('current_password') aria-invalid="true" @enderror>
                                    @error('current_password')<div id="profile-password-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small id="profile-password-hint" class="form-text text-muted">{{ __('profile.password_hint') }}</small>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1" aria-hidden="true"></i>{{ __('profile.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
