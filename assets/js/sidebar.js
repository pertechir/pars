document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content');
    const submenuLinks = document.querySelectorAll('.has-submenu');
    const searchInput = document.querySelector('#menuSearch');

    // تاگل سایدبار
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // ذخیره وضعیت در localStorage
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed'));
        });
    }

    // بازیابی وضعیت قبلی سایدبار
    if (localStorage.getItem('sidebarState') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }

    // مدیریت زیرمنوها
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            const arrow = this.querySelector('.submenu-arrow');
            
            // بستن سایر زیرمنوها
            submenuLinks.forEach(otherLink => {
                if (otherLink !== this) {
                    const otherSubmenu = otherLink.nextElementSibling;
                    const otherArrow = otherLink.querySelector('.submenu-arrow');
                    if (otherSubmenu && otherArrow) {
                        otherSubmenu.style.maxHeight = null;
                        otherArrow.style.transform = '';
                    }
                }
            });
            
            // باز/بسته کردن زیرمنوی فعلی
            if (submenu && arrow) {
                if (submenu.style.maxHeight) {
                    submenu.style.maxHeight = null;
                    arrow.style.transform = '';
                } else {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    arrow.style.transform = 'rotate(-90deg)';
                }
            }
        });
    });

    // جستجو در منو
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const menuItems = document.querySelectorAll('.nav-item a');
            
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                const menuItem = item.closest('.nav-item');
                
                if (text.includes(searchTerm)) {
                    menuItem.style.display = '';
                    if (item.classList.contains('has-submenu')) {
                        const submenu = item.nextElementSibling;
                        if (submenu) {
                            submenu.style.maxHeight = submenu.scrollHeight + 'px';
                        }
                    }
                } else {
                    menuItem.style.display = 'none';
                }
            });
        });
    }
});