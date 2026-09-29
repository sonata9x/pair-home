/**
 * RA0 Edition - 모바일 메뉴 스크립트
 * 모바일 환경에서 좌측 슬라이드 메뉴 제어
 */

document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile_menu_btn');
    const mobileMenuClose = document.getElementById('mobile_menu_close');
    const mobileSidebar = document.getElementById('mobile_sidebar');
    const mobileOverlay = document.getElementById('mobile_overlay');

    // 메뉴 열기
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileSidebar.classList.add('active');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // 스크롤 방지
        });
    }

    // 메뉴 닫기
    function closeMenu() {
        mobileSidebar.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = ''; // 스크롤 복원
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMenu);
    }

    // 오버레이 클릭 시 메뉴 닫기
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMenu);
    }

    // 하위메뉴 토글
    document.querySelectorAll('.toggle_mobile_submenu').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const submenu = document.getElementById(targetId);
            const arrow = this.querySelector('.submenu_arrow');

            if (submenu) {
                const isActive = submenu.classList.contains('active');

                // 다른 하위메뉴 모두 닫기
                document.querySelectorAll('.mobile_submenu.active').forEach(function(menu) {
                    menu.classList.remove('active');
                });
                document.querySelectorAll('.submenu_arrow.active').forEach(function(arr) {
                    arr.classList.remove('active');
                });

                // 현재 하위메뉴 토글
                if (!isActive) {
                    submenu.classList.add('active');
                    if (arrow) arrow.classList.add('active');
                }
            }
        });
    });
});
