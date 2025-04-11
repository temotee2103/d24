    </main>

    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-icon">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h4 id="modalSuccessTitle">下注成功！</h4>
            <p id="modalSuccessMessage"></p>
            <!-- Area for Receipt Content -->
            <pre id="modalReceiptContent" class="border p-2 bg-light text-start mb-3" style="white-space: pre-wrap; word-wrap: break-word; max-height: 200px; overflow-y: auto; display: none;"></pre>
            <!-- Buttons inside Modal -->
            <div class="d-flex justify-content-center mb-3" id="modalReceiptButtons" style="display: none;">
                 <button type="button" class="btn btn-sm me-2" id="modalCopyReceipt" style="background-color: #198754; color: white; border: none;">
                    <i class="bi bi-clipboard"></i> 复制
                 </button>
                 <button type="button" class="btn btn-sm" id="modalWhatsappShare" style="background-color: #0d6efd; color: white; border: none;">
                     <i class="bi bi-whatsapp"></i> WhatsApp
                 </button>
            </div>
            <button id="closeSuccessModal" class="btn btn-success">确定</button>
        </div>
    </div>
    <!-- End Success Modal -->

    <!-- Error Modal -->
    <div id="errorModal" class="modal-overlay error-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-icon">
                <svg class="error-cross" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="error-cross__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="error-cross__line1" fill="none" d="M16,16 l20,20"/>
                    <path class="error-cross__line2" fill="none" d="M16,36 l20,-20"/>
                  </svg>
            </div>
            <h4 id="modalErrorTitle">操作失败</h4> <!-- Added ID for title -->
            <p id="modalErrorMessage">发生了一个错误。</p> 
            <button id="closeErrorModal" class="btn btn-danger">确定</button>
        </div>
    </div>
    <!-- End Error Modal -->
</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 侧边栏折叠功能
            const toggleSidebar = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
            const topbar = document.querySelector('.topbar');
            const mainContent = document.querySelector('main');
            
            // 检查本地存储中的侧边栏状态
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            // 如果不是移动设备，应用保存的折叠状态
            if (window.innerWidth > 992) {
                if (isSidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                }
            }
            
            // 处理侧边栏折叠切换
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        // 移动设备上滑动显示/隐藏
                        sidebar.classList.toggle('show');
                        sidebarBackdrop.classList.toggle('show');
                    } else {
                        // 桌面设备上折叠/展开
                        sidebar.classList.toggle('collapsed');
                        
                        // 保存侧边栏状态到本地存储
                        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    }
                });
            }
            
            // 点击遮罩层关闭侧边栏
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                });
            }
            
            // 处理侧边栏子菜单
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    // 仅在侧边栏未折叠时处理
                    if (!sidebar.classList.contains('collapsed')) {
                        e.preventDefault();
                        const submenuId = this.getAttribute('href');
                        const submenu = document.querySelector(submenuId);
                        if (submenu) {
                            submenu.classList.toggle('show');
                            this.setAttribute('aria-expanded', submenu.classList.contains('show'));
                        }
                    }
                });
            });
            
            // 如果在折叠状态下点击侧边栏项，展开侧边栏
            const sidebarItems = document.querySelectorAll('.sidebar-menu > li > a:not(.dropdown-toggle)');
            sidebarItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (sidebar.classList.contains('collapsed') && window.innerWidth > 992) {
                        sidebar.classList.remove('collapsed');
                        localStorage.setItem('sidebarCollapsed', 'false');
                    }
                });
            });
            
            // 根据URL激活对应的菜单项
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-menu a');
            
            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.includes(href) && href !== '#reportSubmenu') {
                    link.classList.add('active');
                    
                    // 如果该项在子菜单中，确保父菜单也被激活和展开
                    const parentLi = link.closest('li').parentNode;
                    if (parentLi.classList.contains('sidebar-submenu')) {
                        const parentMenu = parentLi.previousElementSibling;
                        if (parentMenu) {
                            parentMenu.classList.add('active');
                            parentLi.classList.add('show');
                            parentMenu.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });
            
            // 为菜单项添加动画效果
            const menuIcons = document.querySelectorAll('.sidebar-menu i');
            menuIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.2)';
                });
                
                icon.addEventListener('mouseleave', function() {
                    if (!this.closest('a').classList.contains('active')) {
                        this.style.transform = 'scale(1)';
                    } else {
                        this.style.transform = 'scale(1.1)';
                    }
                });
            });
        });
        
        // 为卡片添加悬停效果
        const cards = document.querySelectorAll('.modern-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 15px 30px -5px rgba(0, 0, 0, 0.07)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02)';
            });
        });
        
        // 添加窗口大小变化处理
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
            
            if (window.innerWidth > 992) {
                // 检查本地存储中的侧边栏状态
                const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                
                if (isSidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    sidebar.classList.remove('show');
                }
                
                sidebarBackdrop.classList.remove('show');
            } else {
                sidebar.classList.remove('collapsed');
                
                // 如果侧边栏是可见的，显示遮罩
                if (sidebar.classList.contains('show')) {
                    sidebarBackdrop.classList.add('show');
                }
            }
        });
    </script>

    <!-- Global Modal Handling Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Get Modal Elements (Global Scope) ---
        const successModal = document.getElementById('successModal');
        const closeSuccessModalBtn = document.getElementById('closeSuccessModal');
        const modalSuccessTitle = document.getElementById('modalSuccessTitle');
        const modalSuccessMessage = document.getElementById('modalSuccessMessage');
        
        const errorModal = document.getElementById('errorModal');
        const closeErrorModalBtn = document.getElementById('closeErrorModal');
        const modalErrorTitle = document.getElementById('modalErrorTitle');
        const modalErrorMessage = document.getElementById('modalErrorMessage');
        
        const successModalOverlay = document.getElementById('successModal'); 
        const errorModalOverlay = document.getElementById('errorModal'); 

        // --- Modal Functions (Global) ---
        // Make functions globally available
        window.showSuccessModal = function(message = "操作成功！", title = "操作成功！", receiptContent = null) {
            if (successModal) {
                const modalReceiptPre = document.getElementById('modalReceiptContent');
                const modalReceiptButtons = document.getElementById('modalReceiptButtons');

                if (modalSuccessTitle) modalSuccessTitle.textContent = title;
                if (modalSuccessMessage) modalSuccessMessage.textContent = message;

                // Handle receipt display
                if (receiptContent && modalReceiptPre && modalReceiptButtons) {
                    modalReceiptPre.textContent = receiptContent;
                    modalReceiptPre.style.display = 'block';
                    modalReceiptButtons.style.display = 'flex'; // Show buttons
                } else if (modalReceiptPre && modalReceiptButtons) {
                    modalReceiptPre.textContent = '';
                    modalReceiptPre.style.display = 'none';
                    modalReceiptButtons.style.display = 'none'; // Hide buttons
                }

                successModal.style.display = 'flex';
                setTimeout(() => successModal.classList.add('show'), 10);
                console.log('Global Success modal shown');
            }
        }
        window.hideSuccessModal = function() {
            if (successModal) {
                successModal.classList.remove('show');
                setTimeout(() => { successModal.style.display = 'none'; }, 300);
                console.log('Global Success modal hidden');
            }
        }
        window.showErrorModal = function(message = "发生错误", title = "操作失败") {
            if (errorModal) {
                if (modalErrorTitle) modalErrorTitle.textContent = title;
                if (modalErrorMessage) modalErrorMessage.textContent = message;
                errorModal.style.display = 'flex';
                setTimeout(() => errorModal.classList.add('show'), 10);
                console.log('Global Error modal shown');
            }
        }
        window.hideErrorModal = function() {
            if (errorModal) {
                errorModal.classList.remove('show');
                setTimeout(() => { errorModal.style.display = 'none'; }, 300);
                console.log('Global Error modal hidden');
            }
        }

        // --- Trigger Modals from PHP Data --- 
        // This PHP block needs to exist in the specific view 
        // file where $popup_message is passed from the controller.
        // It cannot run directly in this global footer script.
        // <?php if (isset($popup_message) && $popup_message): ?>
        //     const messageType = "<?php echo $popup_message['type']; ?>";
        //     const messageText = "<?php echo addslashes($popup_message['text']); ?>";
        //     const messageTitle = "<?php echo isset($popup_message['title']) ? addslashes($popup_message['title']) : null; ?>";
        //     if (messageType === 'success') {
        //         window.showSuccessModal(messageText, messageTitle || "操作成功！"); 
        //     } else { 
        //         window.showErrorModal(messageText, messageTitle || "操作失败");
        //     }
        // <?php endif; ?>

        // --- Global Modal Close Events ---
        // Add listeners for buttons INSIDE the modal
        const modalCopyBtn = document.getElementById('modalCopyReceipt');
        const modalWhatsappBtn = document.getElementById('modalWhatsappShare');

        if (modalCopyBtn) {
            modalCopyBtn.addEventListener('click', function() {
                const receiptText = document.getElementById('modalReceiptContent')?.textContent || '';
                 if (receiptText) {
                    try {
                        navigator.clipboard.writeText(receiptText).then(() => {
                            alert('收据已复制到剪贴板!');
                        }).catch(err => {
                            fallbackCopyTextToClipboard(receiptText);
                        });
                    } catch (e) { fallbackCopyTextToClipboard(receiptText); }
                 } else { alert('没有可复制的收据内容'); }
            });
        }

        if (modalWhatsappBtn) {
             modalWhatsappBtn.addEventListener('click', function() {
                 const receiptText = document.getElementById('modalReceiptContent')?.textContent || '';
                 if (receiptText) {
                    let encodedText = encodeURIComponent(receiptText);
                    window.open(`https://api.whatsapp.com/send?text=${encodedText}`, '_blank');
                 }
            });
        }

        if (closeSuccessModalBtn) {
            closeSuccessModalBtn.addEventListener('click', window.hideSuccessModal);
        }
        if (closeErrorModalBtn) {
            closeErrorModalBtn.addEventListener('click', window.hideErrorModal);
        }
        if (successModalOverlay) {
            successModalOverlay.addEventListener('click', function(event) {
                // Close only if click is directly on the overlay
                if (event.target === successModalOverlay) { 
                    window.hideSuccessModal();
                }
            });
        }
        if (errorModalOverlay) {
            errorModalOverlay.addEventListener('click', function(event) {
                // Close only if click is directly on the overlay
                if (event.target === errorModalOverlay) { 
                    window.hideErrorModal();
                }
            });
        }
        // Optional: Close modals on Escape key press
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (successModal && successModal.classList.contains('show')) {
                    window.hideSuccessModal();
                }
                if (errorModal && errorModal.classList.contains('show')) {
                    window.hideErrorModal();
                }
            }
        });
    });
    </script>

</body>
</html> 