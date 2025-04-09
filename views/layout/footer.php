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
</body>
</html> 