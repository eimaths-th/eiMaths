<style>
    .premium-modal .modal-dialog {
        max-width: 600px;
        margin: 1.75rem auto;
    }
    .premium-modal .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        background: linear-gradient(135deg, #FFA62E 0%, #FF7B00 100%);
        overflow: hidden;
        animation: modalSlideIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .premium-modal .modal-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: none;
        padding: 2rem 2rem 1rem;
        text-align: center;
        position: relative;
    }
    .premium-modal .modal-title {
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        margin: 0;
    }
    .premium-modal .btn-close-custom {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    .premium-modal .btn-close-custom:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }
    .premium-modal .modal-body {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        padding: 2rem;
        margin: 0;
    }
    .premium-modal .welcome-section {
        text-align: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(255, 166, 46, 0.12), rgba(255, 123, 0, 0.12));
        border-radius: 15px;
        border: 1px solid rgba(255, 166, 46, 0.35);
    }
    .premium-modal .welcome-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        animation: bounce 2s infinite;
    }
    .premium-modal .branch-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        position: relative;
        overflow: hidden;
    }
    .premium-modal .branch-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FFA62E, #FF7B00);
    }
    .premium-modal .branch-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    }
    .premium-modal .branch-icon {
        font-size: 2.5rem;
        background: linear-gradient(135deg, #FFA62E, #FF7B00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    .premium-modal .branch-title {
        color: #2d3748;
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .premium-modal .branch-address {
        color: #718096;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 1rem;
    }
    .premium-modal .contact-info {
        background: rgba(255, 166, 46, 0.06);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .premium-modal .contact-info li {
        padding: 0.25rem 0;
        color: #4a5568;
    }
    .premium-modal .contact-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .premium-modal .btn-contact {
        border-radius: 25px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid transparent;
    }
    .premium-modal .btn-line {
        background: linear-gradient(135deg, #00c851, #00b347);
        color: white;
        box-shadow: 0 4px 15px rgba(0, 200, 81, 0.3);
    }
    .premium-modal .btn-line:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 200, 81, 0.4);
        color: white;
    }
    .premium-modal .btn-call {
        background: linear-gradient(135deg, #FF8C00, #FF6A00);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 140, 0, 0.35);
    }
    .premium-modal .btn-call:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 140, 0, 0.5);
        color: white;
    }
    .premium-modal .btn-facebook {
        background: linear-gradient(135deg, #1877f2, #166fe5);
        color: white;
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3);
    }
    .premium-modal .btn-facebook:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(24, 119, 242, 0.4);
        color: white;
    }
    .premium-modal .modal-footer {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: none;
        padding: 1.5rem 2rem;
        text-align: center;
    }
    .premium-modal .btn-close-modal {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 25px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    .premium-modal .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        color: white;
    }
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }
    @media (max-width: 576px) {
        .premium-modal .modal-dialog {
            margin: 0.5rem;
        }
        .premium-modal .modal-header,
        .premium-modal .modal-body,
        .premium-modal .modal-footer {
            padding: 1.5rem 1rem;
        }
        .premium-modal .contact-buttons {
            flex-direction: column;
        }
        .premium-modal .btn-contact {
            justify-content: center;
        }
    }
</style>

<div class="modal fade premium-modal" id="modalLineContact">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🎉 Order Received | ได้รับคำสั่งซื้อแล้ว</h3>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="welcome-section">
                    <div class="welcome-icon">✅</div>
                    <h4 class="mb-2" style="color: #2d3748; font-weight: 700;">We have received your order!</h4>
                    <p class="mb-3" style="color: #718096;">Please contact the admin via the branches below.</p>
                    <h5 class="mb-2" style="color: #2d3748; font-weight: 700;">เราได้รับคำสั่งซื้อของคุณแล้ว!</h5>
                    <p class="mb-0" style="color: #718096;">กรุณาติดต่อผู้ดูแลระบบตามสาขาด้านล่าง</p>
                </div>

                <div class="row g-3">
                    <!-- Branch 1: Crystal SB Ratchapruek -->
                    <div class="col-12">
                        <div class="branch-card">
                            <div class="d-flex align-items-start">
                                <div class="branch-icon">🏢</div>
                                <div class="flex-grow-1">
                                    <h5 class="branch-title">Crystal SB Ratchapruek</h5>
                                    <p class="branch-address">3rd floor, Zone Education, 555/9 Bang Khanun, Bang Krui, Nonthaburi 11130</p>
                                    <div class="contact-info">
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>📘 Facebook:</strong> eiMaths - TH</li>
                                            <li><strong>💬 Line:</strong> @eiMaths</li>
                                            <li><strong>📱 Mobile:</strong> 061 620 8666</li>
                                        </ul>
                                    </div>
                                    <div class="contact-buttons">
                                        <a class="btn-contact btn-line" href="https://lin.ee/K244eaZ" target="_blank" rel="noopener">
                                            💬 Add Line
                                        </a>
                                        <a class="btn-contact btn-call" href="tel:0616208666">
                                            📞 Call Now
                                        </a>
                                        <a class="btn-contact btn-facebook" href="https://www.facebook.com/search/top?q=eiMaths%20-%20TH" target="_blank" rel="noopener">
                                            📘 Facebook
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch 2: Seacon Bangkae -->
                    <div class="col-12">
                        <div class="branch-card">
                            <div class="d-flex align-items-start">
                                <div class="branch-icon">🏬</div>
                                <div class="flex-grow-1">
                                    <h5 class="branch-title">eiMaths Seacon Bangkae Branch</h5>
                                    <p class="branch-address">Seacon Bangkae, 4th floor, Zone HarborLand, 607 Phetkasem Road, Bang Wa, Phasi Charoen, Bangkok 10160</p>
                                    <div class="contact-info">
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>📘 Facebook:</strong> eiMaths - Bangkae</li>
                                            <li><strong>💬 Line:</strong> @eiMathsBangkae</li>
                                            <li><strong>📱 Mobile:</strong> 093-258-5897</li>
                                        </ul>
                                    </div>
                                    <div class="contact-buttons">
                                        <a class="btn-contact btn-line" href="https://lin.ee/SFpwGwU" target="_blank" rel="noopener">
                                            💬 Add Line
                                        </a>
                                        <a class="btn-contact btn-call" href="tel:0932585897">
                                            📞 Call Now
                                        </a>
                                        <a class="btn-contact btn-facebook" href="https://www.facebook.com/search/top?q=eiMaths%20-%20Bangkae" target="_blank" rel="noopener">
                                            📘 Facebook
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">✨ Close</button>
            </div>
        </div>
    </div>
</div>

<script>

</script>
