@php
    $totalPermissions = collect($permissionGroups)->sum(fn ($group) => count($group['permissions']));
@endphp

<div id="{{ $panelId }}" class="permission-panel" data-permission-panel>
    <div class="permission-toolbar">
        <div class="permission-search-wrap">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" class="form-control permission-search"
                placeholder="ค้นหา Permission..." aria-label="ค้นหา Permission">
        </div>
        <div class="permission-toolbar-actions">
            <span class="permission-total" aria-live="polite">
                เลือกแล้ว <strong data-total-selected>0</strong>/{{ $totalPermissions }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-primary permission-select-all">
                <i class="fas fa-check-double"></i> เลือกทั้งหมด
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary permission-clear-all">
                <i class="fas fa-times"></i> ล้างทั้งหมด
            </button>
        </div>
    </div>

    <div class="permission-groups-grid">
        @forelse ($permissionGroups as $group)
            <section class="permission-group-card" data-permission-group="{{ $group['key'] }}">
                <header class="permission-group-header">
                    <div class="permission-group-heading">
                        <span class="permission-group-icon"><i class="{{ $group['icon'] }}"></i></span>
                        <div>
                            <h5>{{ $group['label'] }}</h5>
                            <p>{{ $group['description'] }}</p>
                        </div>
                    </div>
                    <div class="permission-group-control">
                        <span class="permission-group-count">
                            <span data-group-selected>0</span>/{{ count($group['permissions']) }}
                        </span>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input permission-group-toggle"
                                id="{{ $panelId }}-{{ $group['key'] }}-all">
                            <label class="custom-control-label" for="{{ $panelId }}-{{ $group['key'] }}-all">
                                เลือกทั้งกลุ่ม
                            </label>
                        </div>
                    </div>
                </header>

                <div class="permission-group-items">
                    @foreach ($group['permissions'] as $item)
                        <div class="permission-option"
                            data-permission-option
                            data-permission-search="{{ strtolower($item['label'].' '.$item['name']) }}">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="{{ $inputName }}" value="{{ $item['id'] }}"
                                    id="{{ $inputPrefix.$item['id'] }}"
                                    class="custom-control-input name permission-item-checkbox">
                                <label for="{{ $inputPrefix.$item['id'] }}" class="custom-control-label">
                                    <span class="permission-friendly-name">{{ $item['label'] }}</span>
                                    <small class="permission-code">{{ $item['name'] }}</small>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="alert alert-light border mb-0">ไม่มี Permission ที่สามารถมอบหมายได้</div>
        @endforelse
    </div>

    <div class="permission-no-results" hidden>
        <i class="fas fa-search"></i> ไม่พบ Permission ที่ค้นหา
    </div>
</div>
