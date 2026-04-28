@extends('layouts.default', [
	'paceTop' => false,
	'appSidebarHide' => true,
	'appHeaderHide' => true,
	'appContentClass' => 'p-0'
])

@section('title', 'Free Trial Registration')

@push('css')
<style>
/* Reusing the beautiful CSS from login page */
.login-split-container {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Inter', sans-serif;
    background-color: #0f172a;
}

.bg-image {
    position: absolute;
    top: -5%;
    left: -5%;
    width: 110%;
    height: 110%;
    background-image: url('/images/student_site.jpg');
    background-size: cover;
    background-position: center;
    opacity: 0.3;
    z-index: 1;
}

.register-panel {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-radius: 30px;
    padding: 60px 80px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    z-index: 20;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.register-panel::-webkit-scrollbar {
    width: 8px;
}
.register-panel::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.register-panel::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.register-panel::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.form-header {
    margin-bottom: 40px;
    text-align: center;
}

.form-header h2 {
    font-size: 36px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    letter-spacing: -1px;
}

.form-header p {
    color: #64748b;
    font-size: 16px;
    margin: 0;
}

.custom-form-group {
    margin-bottom: 24px;
    position: relative;
}

.custom-form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 10px;
}

.custom-input {
    width: 100%;
    height: 56px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 0 20px 0 52px;
    font-size: 16px;
    color: #0f172a;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.custom-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

select.custom-input {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 40px;
    cursor: pointer;
}

.select-arrow {
    position: absolute;
    right: 20px;
    top: 50px;
    color: #94a3b8;
    pointer-events: none;
}

.input-icon {
    position: absolute;
    left: 20px;
    top: 48px;
    color: #94a3b8;
    transition: color 0.3s ease;
}

.custom-input:focus ~ .input-icon,
.custom-input:not(:placeholder-shown) ~ .input-icon {
    color: #3b82f6;
}

.custom-btn {
    width: 100%;
    height: 56px;
    border-radius: 16px;
    border: none;
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.btn-guest {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
}

.btn-guest:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px rgba(37, 99, 235, 0.3);
}

.form-footer {
    margin-top: 32px;
    text-align: center;
    font-size: 15px;
    color: #64748b;
}

.form-footer a {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s;
}

.form-footer a:hover {
    color: #2563eb;
    text-decoration: underline;
}

.alert-beautiful {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.alert-beautiful ul {
    margin: 0;
    padding-left: 20px;
}

.alert-info-beautiful {
    background: #eff6ff;
    /* border-left: 4px solid #3b82f6; */
    color: #1e3a8a;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-col {
    flex: 1;
}

@media (max-width: 768px) {
    .register-panel {
        width: 100%;
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
        padding: 40px 24px;
    }
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>
@endpush

@section('content')
<div class="login-split-container">
    <div class="bg-image"></div>
    
    <div class="register-panel" id="register-panel">
        <div class="form-header">
            <h2><i data-lucide="rocket" style="color: #3b82f6;" width="40" height="40"></i> Register Free Trial</h2>
            <p>Fill in your details to get 7 days of free access.</p>
        </div>

        <form action="{{ route('guest.quiz.register.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert-beautiful">
                    <i data-lucide="alert-circle" width="20" height="20" style="flex-shrink: 0; margin-top: 2px;"></i>
                    <div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="form-row">
                <div class="form-col">
                    <div class="custom-form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" class="custom-input" placeholder="First Name" id="first_name" name="first_name" value="{{ old('first_name') }}" required/>
                        <i data-lucide="user" width="20" height="20" class="input-icon"></i>
                    </div>
                </div>
                <div class="form-col">
                    <div class="custom-form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" class="custom-input" placeholder="Last Name" id="last_name" name="last_name" value="{{ old('last_name') }}" required/>
                        <i data-lucide="user" width="20" height="20" class="input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="custom-form-group">
                        <label for="nickname">Nickname</label>
                        <input type="text" class="custom-input" placeholder="Nickname" id="nickname" name="nickname" value="{{ old('nickname') }}"/>
                        <i data-lucide="smile" width="20" height="20" class="input-icon"></i>
                    </div>
                </div>
                <div class="form-col">
                    <div class="custom-form-group">
                        <label for="grade">Grade / Class *</label>
                        <select class="custom-input" id="grade" name="grade" required>
                            <option value="">-- Select Grade --</option>
                            <option value="K1" {{ old('grade') == 'K1' ? 'selected' : '' }}>K1</option>
                            <option value="K2" {{ old('grade') == 'K2' ? 'selected' : '' }}>K2</option>
                            <option value="K3" {{ old('grade') == 'K3' ? 'selected' : '' }}>K3</option>
                            <option value="P1" {{ old('grade') == 'P1' ? 'selected' : '' }}>P1</option>
                            <option value="P2" {{ old('grade') == 'P2' ? 'selected' : '' }}>P2</option>
                            <option value="P3" {{ old('grade') == 'P3' ? 'selected' : '' }}>P3</option>
                            <option value="P4" {{ old('grade') == 'P4' ? 'selected' : '' }}>P4</option>
                            <option value="P5" {{ old('grade') == 'P5' ? 'selected' : '' }}>P5</option>
                            <option value="P6" {{ old('grade') == 'P6' ? 'selected' : '' }}>P6</option>
                            <option value="M1" {{ old('grade') == 'M1' ? 'selected' : '' }}>M1</option>
                            <option value="M2" {{ old('grade') == 'M2' ? 'selected' : '' }}>M2</option>
                            <option value="M3" {{ old('grade') == 'M3' ? 'selected' : '' }}>M3</option>
                        </select>
                        <i data-lucide="graduation-cap" width="20" height="20" class="input-icon"></i>
                        <i data-lucide="chevron-down" width="20" height="20" class="select-arrow"></i>
                    </div>
                </div>
            </div>

            <div class="custom-form-group">
                <label for="school">School</label>
                <input type="text" class="custom-input" placeholder="School Name" id="school" name="school" value="{{ old('school') }}"/>
                <i data-lucide="building" width="20" height="20" class="input-icon"></i>
            </div>

            <div class="custom-form-group">
                <label for="email">Email Address *</label>
                <input type="email" class="custom-input" placeholder="Email (used for login)" id="email" name="email" value="{{ old('email') }}" required/>
                <i data-lucide="mail" width="20" height="20" class="input-icon"></i>
            </div>

            <div class="custom-form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" class="custom-input" placeholder="Phone Number (used as password)" id="phone" name="phone" value="{{ old('phone') }}" required/>
                <i data-lucide="phone" width="20" height="20" class="input-icon"></i>
            </div>

            <div class="alert-info-beautiful">
                <i data-lucide="info" width="24" height="24"></i>
                <span><strong>Note:</strong> Your phone number will be used as your password for login.</span>
            </div>

            <button type="submit" class="custom-btn btn-guest">
                Register & Start Free Trial <i data-lucide="play-circle" width="20" height="20"></i>
            </button>

            <div class="form-footer">
                Already have an account? <a href="{{ route('student.login') }}">Sign In Here</a>
            </div>
            
            <div style="margin-top: 30px; text-align: center; color: #94a3b8; font-size: 14px;">
                &copy; eiMaths Thailand {{ date('Y') }}
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Animate panel on load
    gsap.from("#register-panel", {
        duration: 1,
        y: 50,
        opacity: 0,
        ease: "power3.out",
        delay: 0.2,
        clearProps: "all"
    });
</script>
@endpush
