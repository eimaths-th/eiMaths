@extends('layouts.default', [
	'paceTop' => false,
	'appSidebarHide' => true,
	'appHeaderHide' => true,
	'appContentClass' => 'p-0'
])

@section('title', 'Login Page')

@push('css')
<style>
/* Custom CSS for the beautiful login page */
.login-split-container {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    display: flex;
    font-family: 'Inter', sans-serif;
    background-color: #0f172a;
}

.bg-image {
    position: absolute;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background-image: url('/images/bg-e-leaning.png');
    background-size: cover;
    background-position: center;
    /* opacity: 0.3; */
    z-index: 1;
}

.brand-title {
    position: absolute;
    top: 8%;
    left: 0;
    width: 100%;
    text-align: center;
    z-index: 5;
    pointer-events: none;
    animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.brand-title h1 {
    font-size: 5rem;
    font-weight: 900;
    margin: 0;
    background: linear-gradient(135deg, #f4390a 0%, #ff8a00 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -2px;
    line-height: 1.1;
    filter: drop-shadow(0px 8px 16px rgba(244, 57, 10, 0.25));
}

.brand-title h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #475569;
    margin: 10px 0 0 0;
    letter-spacing: 6px;
    text-transform: uppercase;
    text-shadow: 0 4px 6px rgba(255, 255, 255, 0.8);
}

@media (max-width: 850px) {
    .brand-title {
        top: 4%;
    }
    .brand-title h1 {
        font-size: 3rem;
        letter-spacing: -1px;
    }
    .brand-title h2 {
        font-size: 1.2rem;
        letter-spacing: 4px;
    }
}

/* Glassmorphism cards */
.choice-cards-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 60px;
    z-index: 10;
}

.choice-card {
    background: rgba(255, 255, 255, 0.08) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 30px;
    padding: 50px 40px;
    width: 380px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    color: #fff !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2) !important;
    position: relative;
    z-index: 15;
}

.choice-card:hover {
    transform: translateY(-15px);
    background: rgba(255, 255, 255, 0.12) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3) !important;
}

.choice-card .icon-wrapper {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    width: 90px;
    height: 90px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
}

.choice-card.login-opt-guest .icon-wrapper {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
}

.choice-card h3 {
    font-size: 28px !important;
    font-weight: 800 !important;
    margin-bottom: 16px !important;
    color: #685858ff !important;
    letter-spacing: -0.5px;
}

.choice-card p {
    text-align: center !important;
    color: rgba(119, 110, 110, 0.8) !important;
    font-size: 15px !important;
    line-height: 1.6 !important;
    margin: 0 !important;
}

/* Forms Panel */
.form-panel {
    position: absolute;
    top: 0;
    width: 50%;
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    z-index: 20;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 120px 80px 60px 80px;
    box-shadow: 0 0 60px rgba(0,0,0,0.4);
}

.form-panel.student-form {
    right: -50%;
    border-left: 1px solid rgba(255,255,255,0.5);
}

.form-panel.guest-form {
    left: -50%;
    border-right: 1px solid rgba(255,255,255,0.5);
}

.close-btn {
    position: absolute;
    top: 40px;
    right: 80px;
    cursor: pointer;
    color: #94a3b8;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: #f1f5f9;
}

.close-btn:hover {
    color: #0f172a;
    background: #e2e8f0;
    transform: rotate(90deg);
}

.back-btn-absolute {
    position: absolute;
    top: 40px;
    left: 80px;
    cursor: pointer;
    color: #64748b;
    height: 48px;
    padding: 0 20px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    background: #f1f5f9;
    font-weight: 600;
    font-size: 14px;
    border: none;
    z-index: 10;
}

.back-btn-absolute:hover {
    color: #0f172a;
    background: #e2e8f0;
}

.form-header {
    margin-bottom: 48px;
}

.form-header h2 {
    font-size: 36px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 16px;
    letter-spacing: -1px;
}

.form-header p {
    color: #64748b;
    font-size: 16px;
    margin: 0;
}

.custom-form-group {
    margin-bottom: 28px;
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
    border-color: #f97316;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
}

.guest-form .custom-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
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
    color: #f97316;
}

.guest-form .custom-input:focus ~ .input-icon,
.guest-form .custom-input:not(:placeholder-shown) ~ .input-icon {
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

.btn-student {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2);
}

.btn-student:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px rgba(234, 88, 12, 0.3);
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

/* Error alerts */
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
    align-items: center;
    gap: 12px;
}

.alert-beautiful ul {
    margin: 0;
    padding-left: 20px;
}

.alert-info-beautiful {
    background: #eff6ff;
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

.form-panel::-webkit-scrollbar {
    width: 8px;
}
.form-panel::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.form-panel::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.form-panel::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (max-width: 1024px) {
    .choice-cards-wrapper {
        gap: 30px;
    }
    .choice-card {
        width: 320px;
        padding: 40px 30px;
    }
    .form-panel {
        width: 60%;
    }
    .form-panel.student-form {
        right: -60%;
    }
    .form-panel.guest-form {
        left: -60%;
    }
}

@media (max-width: 850px) {
    .choice-cards-wrapper {
        flex-direction: column;
        gap: 15px;
        padding-top: 120px;
    }
    .choice-card {
        width: 90%;
        max-width: 380px;
        padding: 25px 20px;
        height: auto;
    }
    .choice-card .icon-wrapper {
        width: 70px;
        height: 70px;
        margin-bottom: 15px;
    }
    .choice-card h3 {
        font-size: 24px !important;
        margin-bottom: 10px !important;
    }
    .form-panel {
        width: 100%;
        padding: 100px 24px 40px 24px;
    }
    .close-btn {
        top: 20px;
        right: 24px;
    }
    .back-btn-absolute {
        top: 20px;
        left: 24px;
    }
    .form-panel.student-form {
        right: -100%;
    }
    .form-panel.guest-form {
        left: -100%;
    }
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    .close-btn {
        top: 20px;
        right: 20px;
    }
    .guest-form .close-btn {
        left: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="login-split-container">
    <div class="bg-image"></div>
    
    <div class="brand-title">
        <h1>EiMaths</h1>
        <h2>E-Learning</h2>
    </div>
    
    <!-- Choice Cards -->
    <div class="choice-cards-wrapper" id="choice-cards">
        <div class="choice-card login-opt-student" onclick="openStudentForm()">
            <div class="icon-wrapper">
                <i data-lucide="graduation-cap" class="text-white" width="44" height="44"></i>
            </div>
            <h3>Student Login</h3>
            <p>Access your eiMaths Thailand student account and continue your learning.</p>
        </div>
        <!-- Free Trial card — temporarily hidden -->
        <div class="choice-card login-opt-guest" onclick="openGuestForm()">
            <div class="icon-wrapper">
                <i data-lucide="rocket" class="text-white" width="44" height="44"></i>
            </div>
            <h3>Free Trial</h3>
            <p>7-day free trial for guests. Test your math skills with our exclusive quizzes.</p>
        </div>
    </div>

    <!-- Student Form Panel (Slides from Right) -->
    <div class="form-panel student-form" id="student-form-panel">
        <div class="close-btn" onclick="closeForms()">
            <i data-lucide="x" width="24" height="24"></i>
        </div>
        <div class="form-header">
            <h2><i data-lucide="graduation-cap" style="color: #f97316;" width="40" height="40"></i> Student Login</h2>
            <p>Welcome back! Please enter your details.</p>
        </div>

        <form action="{{ route('student.check') }}" method="POST">
            @csrf
            @if (isset($message) || $errors->has('error'))
                <div class="alert-beautiful">
                    <i data-lucide="alert-circle" width="20" height="20"></i>
                    {{ $message ?? $errors->first('error') }}
                </div>
            @endif
            
            <div class="custom-form-group">
                <label for="code">Student Code</label>
                <input type="text" class="custom-input" placeholder="Enter your student code" id="code" name="code" required/>
                <i data-lucide="user" width="20" height="20" class="input-icon"></i>
            </div>
            
            <div class="custom-form-group">
                <label for="password">Password</label>
                <input type="password" class="custom-input" placeholder="Enter your password" id="password" name="password" required/>
                <i data-lucide="lock" width="20" height="20" class="input-icon"></i>
            </div>
            
            <div class="custom-form-group" style="display: flex; align-items: center; gap: 10px; margin-top: -10px;">
                <input type="checkbox" id="rememberMe" name="rememberMe" value="1" style="width: 18px; height: 18px; accent-color: #f97316; cursor: pointer;" />
                <label for="rememberMe" style="margin-bottom: 0; cursor: pointer; font-weight: 500; font-size: 15px;">Remember Me</label>
            </div>
            
            <button type="submit" class="custom-btn btn-student">
                Sign In <i data-lucide="arrow-right" width="20" height="20"></i>
            </button>
        </form>
        <div class="form-footer" style="margin-top: 50px; color: #94a3b8;">
            &copy; eiMaths Thailand {{ date('Y') }}
        </div>
    </div>

    <!-- Guest Form Panel (Slides from Left) -->
    <div class="form-panel guest-form" id="guest-form-panel" style="overflow-y: auto;">
        <div class="close-btn" onclick="closeForms()">
            <i data-lucide="x" width="24" height="24"></i>
        </div>

        <button type="button" class="back-btn-absolute" id="back-btn" onclick="showGuestLogin()" style="display: none;">
            <i data-lucide="arrow-left" width="20" height="20"></i> Back to Login
        </button>

        <!-- Register Section -->
        <div id="guest-register-section" style="display: none;">
            <div class="form-header">
                <h2><i data-lucide="rocket" style="color: #3b82f6;" width="40" height="40"></i> Free Trial</h2>
                <p>Register to get 7 days of free access.</p>
            </div>

            <form action="{{ route('guest.quiz.register.store') }}" method="POST">
                @csrf

                @if ($errors->any() && !($errors->has('error') || $errors->has('guest_error') || $errors->has('guest_email') || $errors->has('guest_password')))
                    <div class="alert-beautiful" style="align-items: flex-start;">
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
                    <i data-lucide="info" width="24" height="24" style="flex-shrink: 0;"></i>
                    <span><strong>Note:</strong> Your phone number will be used as your password.</span>
                </div>
                <button type="submit" class="custom-btn btn-guest">
                    Register & Start Free Trial <i data-lucide="play-circle" width="20" height="20"></i>
                </button>
            </form>
        </div>

        <!-- Login Section -->
        <div id="guest-login-section">
            <div class="form-header">
                <h2><i data-lucide="log-in" style="color: #3b82f6;" width="40" height="40"></i> Guest Login</h2>
                <p>Enter your details to start your free access.</p>
            </div>

            <form action="{{ route('guest.quiz.login') }}" method="POST">
                @csrf
                @if ($errors->has('guest_error'))
                    <div class="alert-beautiful">
                        <i data-lucide="alert-circle" width="20" height="20"></i>
                        {{ $errors->first('guest_error') }}
                    </div>
                @endif
                
                <div class="custom-form-group">
                    <label for="guest_email">Email Address</label>
                    <input type="email" class="custom-input" placeholder="Enter your email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required/>
                    <i data-lucide="mail" width="20" height="20" class="input-icon"></i>
                    @error('guest_email')
                        <span style="color: #ef4444; font-size: 13px; margin-top: 6px; display: block; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="custom-form-group">
                    <label for="guest_password">Password</label>
                    <input type="password" class="custom-input" placeholder="Enter your password" id="guest_password" name="guest_password" required/>
                    <i data-lucide="lock" width="20" height="20" class="input-icon"></i>
                    <span style="color: #64748b; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 6px; font-weight: 500;">
                        <i data-lucide="info" width="16" height="16"></i> Use registered phone number
                    </span>
                    @error('guest_password')
                        <span style="color: #ef4444; font-size: 13px; margin-top: 6px; display: block; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="custom-btn btn-guest">
                    Start Free Trial <i data-lucide="play" width="20" height="20"></i>
                </button>
                
                <div class="form-footer">
                    Don't have an account? <a href="javascript:void(0)" onclick="showGuestRegister()">Register Now</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    // Initialize Lucide icons
    lucide.createIcons();

    const choiceCards = document.getElementById('choice-cards');
    const studentForm = document.getElementById('student-form-panel');
    const guestForm = document.getElementById('guest-form-panel');
    
    // Animate cards wrapper on load to avoid conflicting with CSS hover transforms
    gsap.from("#choice-cards", {
        duration: 1.2,
        y: 40,
        opacity: 0,
        ease: "power4.out",
        delay: 0.1,
        clearProps: "all" // Clears the inline styles after animation so CSS can take over
    });

    const isTablet = window.innerWidth <= 1024;
    const isMobile = window.innerWidth <= 850;
    let offset = '50%';
    if (isTablet) offset = '60%';
    if (isMobile) offset = '100%';
    let currentOpen = null;

    function openStudentForm() {
        currentOpen = 'student';
        // Ensure guest form is fully hidden
        gsap.set(guestForm, { left: '-' + offset });
        // Hide cards
        gsap.to(choiceCards, {
            duration: 0.6,
            scale: 0.9,
            opacity: 0,
            display: 'none',
            ease: "power3.inOut"
        });
        
        // Slide in student form from right
        gsap.to(studentForm, {
            duration: 0.8,
            right: 0,
            ease: "power4.out",
            delay: 0.2
        });
        
        // Move background slightly for parallax effect
        gsap.to(".bg-image", {
            duration: 1.2,
            x: "-5%",
            ease: "power3.out"
        });
    }

    function openGuestForm() {
        currentOpen = 'guest';
        // Ensure student form is fully hidden
        gsap.set(studentForm, { right: '-' + offset });
        // Hide cards
        gsap.to(choiceCards, {
            duration: 0.6,
            scale: 0.9,
            opacity: 0,
            display: 'none',
            ease: "power3.inOut"
        });
        
        // Slide in guest form from left
        gsap.to(guestForm, {
            duration: 0.8,
            left: 0,
            ease: "power4.out",
            delay: 0.2
        });
        
        // Move background slightly for parallax effect
        gsap.to(".bg-image", {
            duration: 1.2,
            x: "5%",
            ease: "power3.out"
        });
    }

    function closeForms() {
        currentOpen = null;
        // Slide out forms
        gsap.to(studentForm, {
            duration: 0.7,
            right: '-' + offset,
            ease: "power3.inOut"
        });
        
        gsap.to(guestForm, {
            duration: 0.7,
            left: '-' + offset,
            ease: "power3.inOut"
        });
        
        // Show cards
        gsap.to(choiceCards, {
            duration: 0.6,
            scale: 1,
            opacity: 1,
            display: 'flex',
            ease: "power3.out",
            delay: 0.4
        });
        
        // Reset background
        gsap.to(".bg-image", {
            duration: 1.2,
            x: "0%",
            ease: "power3.out"
        });
    }

    // Check for errors on load to auto-open forms
    document.addEventListener("DOMContentLoaded", function() {
        @if (isset($message) || $errors->has('error') || $errors->has('code') || $errors->has('password'))
            openStudentForm();
        @elseif ($errors->has('guest_error') || $errors->has('guest_email') || $errors->has('guest_password'))
            openGuestForm();
        @elseif ($errors->any())
            openGuestForm();
            showGuestRegister();
        @endif
        
        // Update offset on resize
        window.addEventListener('resize', () => {
            const width = window.innerWidth;
            if (width <= 850) offset = '100%';
            else if (width <= 1024) offset = '60%';
            else offset = '50%';
            
            // Re-apply hidden state positions to prevent peeking
            if (currentOpen !== 'student') {
                gsap.set(studentForm, { right: '-' + offset });
            }
            if (currentOpen !== 'guest') {
                gsap.set(guestForm, { left: '-' + offset });
            }
        });
    });

    function showGuestLogin() {
        document.getElementById('back-btn').style.display = 'none';
        gsap.to("#guest-register-section", {
            duration: 0.3, opacity: 0, x: -20, display: "none", ease: "power2.inOut"
        });
        gsap.fromTo("#guest-login-section", 
            { opacity: 0, x: 20 },
            { duration: 0.3, opacity: 1, x: 0, display: "block", delay: 0.3, ease: "power2.out" }
        );
    }

    function showGuestRegister() {
        document.getElementById('back-btn').style.display = 'flex';
        gsap.to("#guest-login-section", {
            duration: 0.3, opacity: 0, x: 20, display: "none", ease: "power2.inOut"
        });
        gsap.fromTo("#guest-register-section", 
            { opacity: 0, x: -20 },
            { duration: 0.3, opacity: 1, x: 0, display: "block", delay: 0.3, ease: "power2.out" }
        );
    }
</script>
@endpush
