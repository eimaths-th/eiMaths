@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tracking Link Generator</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-link"></i> Create UTM Link</h3>
            </div>
            <form method="POST" action="{{ route('tracking-links.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Link name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Example: Facebook June Video 01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_url">Landing page URL *</label>
                                <input type="url" id="base_url" name="base_url" value="{{ old('base_url', $baseUrl) }}" class="form-control tracking-field" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_source">
                                    Source *
                                    <i class="fas fa-circle-info text-muted ml-1" data-toggle="tooltip"
                                        title="แพลตฟอร์มหรือแหล่งที่ลูกค้าเข้ามา เช่น Facebook, Google, TikTok หรือ LINE"></i>
                                </label>
                                <select id="utm_source" name="utm_source" class="form-control tracking-field" required>
                                    <option value="">Select source</option>
                                    @foreach([
                                        'facebook' => 'Facebook - โฆษณาหรือโพสต์บน Facebook',
                                        'google' => 'Google - โฆษณาหรือผลค้นหาจาก Google',
                                        'tiktok' => 'TikTok - โฆษณาหรือคอนเทนต์บน TikTok',
                                        'line' => 'LINE - LINE Ads, LINE OA หรือข้อความ LINE',
                                        'email' => 'Email - ลิงก์ที่ส่งผ่านอีเมล',
                                        'referral' => 'Referral - เว็บไซต์หรือพันธมิตรแนะนำมา',
                                        'organic' => 'Organic - การเข้าถึงโดยไม่เสียค่าโฆษณา',
                                    ] as $source => $sourceLabel)
                                        <option value="{{ $source }}" @selected(old('utm_source') === $source)>{{ $sourceLabel }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Source บอกว่าลูกค้ามาจากแพลตฟอร์มใด</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_medium">
                                    Medium *
                                    <i class="fas fa-circle-info text-muted ml-1" data-toggle="tooltip"
                                        title="วิธีที่ลูกค้าเดินทางมาจาก Source เช่น โฆษณาแบบจ่ายเงิน โพสต์ปกติ อีเมล หรือเว็บไซต์แนะนำ"></i>
                                </label>
                                <select id="utm_medium" name="utm_medium" class="form-control tracking-field" required>
                                    <option value="">Select medium</option>
                                    @foreach([
                                        'cpc' => 'cpc - โฆษณาแบบจ่ายต่อคลิก เช่น Google Search Ads',
                                        'paid_social' => 'paid_social - โฆษณาแบบเสียเงินบน Social Media',
                                        'organic_social' => 'organic_social - โพสต์ Social ปกติ ไม่เสียค่าโฆษณา',
                                        'email' => 'email - ลิงก์ที่ส่งผ่าน Email Marketing',
                                        'referral' => 'referral - ลิงก์จากเว็บไซต์หรือพันธมิตร',
                                        'display' => 'display - โฆษณาแบนเนอร์หรือ Display Network',
                                    ] as $medium => $mediumLabel)
                                        <option value="{{ $medium }}" @selected(old('utm_medium') === $medium)>{{ $mediumLabel }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Medium บอกว่าลูกค้าเข้ามาด้วยวิธีใด</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="utm_campaign">Campaign *</label>
                                <input type="text" id="utm_campaign" name="utm_campaign" value="{{ old('utm_campaign') }}" class="form-control tracking-field" placeholder="free_trial_2026" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="utm_content">Content</label>
                                <input type="text" id="utm_content" name="utm_content" value="{{ old('utm_content') }}" class="form-control tracking-field" placeholder="video_01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="utm_term">Term / Keyword</label>
                                <input type="text" id="utm_term" name="utm_term" value="{{ old('utm_term') }}" class="form-control tracking-field" placeholder="math_class_for_kids">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="generated-preview">Generated link preview</label>
                        <div class="input-group">
                            <textarea id="generated-preview" class="form-control" rows="2" readonly></textarea>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary copy-link-btn" data-copy-target="generated-preview" title="Copy link">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-magic"></i> Generate & Save
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Link History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Source</th>
                                <th>Medium</th>
                                <th>Campaign</th>
                                <th>Content</th>
                                <th>Created by</th>
                                <th>Created at</th>
                                <th>Link</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($links as $link)
                                <tr>
                                    <td>{{ $link->name }}</td>
                                    <td>{{ $link->utm_source }}</td>
                                    <td>{{ $link->utm_medium }}</td>
                                    <td>{{ $link->utm_campaign }}</td>
                                    <td>{{ $link->utm_content ?: '-' }}</td>
                                    <td>{{ $link->creator->name ?? 'System' }}</td>
                                    <td>{{ optional($link->created_at)->format('Y-m-d H:i') }}</td>
                                    <td style="min-width: 320px;">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="tracking-link-{{ $link->id }}" value="{{ $link->generated_url }}" class="form-control" readonly>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary copy-link-btn" data-copy-target="tracking-link-{{ $link->id }}" title="Copy link">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('tracking-links.destroy', $link) }}" onsubmit="return confirm('Delete this tracking link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No tracking links created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $links->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && typeof window.jQuery.fn.tooltip === 'function') {
            window.jQuery('[data-toggle="tooltip"]').tooltip();
        }

        const preview = document.getElementById('generated-preview');
        const trackedFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

        function normalizeTag(value) {
            return value.trim().toLowerCase().replace(/[^a-z0-9ก-๙._-]+/gu, '_');
        }

        function updatePreview() {
            const baseUrl = document.getElementById('base_url').value.trim();
            if (!baseUrl) {
                preview.value = '';
                return;
            }

            try {
                const url = new URL(baseUrl);
                trackedFields.forEach(function (field) {
                    const value = normalizeTag(document.getElementById(field).value);
                    if (value) {
                        url.searchParams.set(field, value);
                    } else {
                        url.searchParams.delete(field);
                    }
                });
                preview.value = url.toString();
            } catch (error) {
                preview.value = 'Please enter a valid landing page URL.';
            }
        }

        document.querySelectorAll('.tracking-field').forEach(function (field) {
            field.addEventListener('input', updatePreview);
            field.addEventListener('change', updatePreview);
        });

        document.querySelectorAll('.copy-link-btn').forEach(function (button) {
            button.addEventListener('click', async function () {
                const target = document.getElementById(button.dataset.copyTarget);
                if (!target || !target.value || target.value.startsWith('Please enter')) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(target.value);
                } catch (error) {
                    target.select();
                    document.execCommand('copy');
                }

                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied';
                setTimeout(function () {
                    button.innerHTML = originalHtml;
                }, 1500);
            });
        });

        updatePreview();
    });
</script>
@endsection
