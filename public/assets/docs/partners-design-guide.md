# 🎨 Partners Portal - Modern Design System Guide

## 📋 목적
Partners Portal의 헤더와 사이드바를 통합된 현대적인 디자인 시스템으로 관리하여 일관된 사용자 경험을 제공하고, 세련된 UI/UX를 구현합니다.

## 🏗️ 아키텍처 개요

### 핵심 디자인 원칙
- **Glassmorphism**: 반투명 효과와 블러를 활용한 현대적인 디자인
- **Typography-First**: Inter 폰트를 기반으로 한 타이포그래피 중심 설계
- **Micro-interactions**: 부드러운 애니메이션과 호버 효과
- **Responsive Priority**: 모바일 우선 반응형 디자인
- **Accessibility**: 접근성을 고려한 색상 대비와 포커스 관리

### 기술 스택
- **CSS Grid**: 레이아웃 구조
- **CSS Variables**: 디자인 토큰 관리
- **Backdrop Filter**: 글래스모피즘 효과
- **CSS Animations**: 부드러운 전환 효과

## 🎨 디자인 토큰 (CSS Variables)

### 색상 팔레트
```css
/* Primary Colors */
--primary-50: #eff6ff;
--primary-100: #dbeafe;
--primary-200: #bfdbfe;
--primary-300: #93c5fd;
--primary-400: #60a5fa;
--primary-500: #3b82f6;
--primary-600: #2563eb;
--primary-700: #1d4ed8;
--primary-800: #1e40af;
--primary-900: #1e3a8a;
--primary-950: #172554;

/* Neutral Colors */
--gray-50: #f8fafc;
--gray-100: #f1f5f9;
--gray-200: #e2e8f0;
--gray-300: #cbd5e1;
--gray-400: #94a3b8;
--gray-500: #64748b;
--gray-600: #475569;
--gray-700: #334155;
--gray-800: #1e293b;
--gray-900: #0f172a;
--gray-950: #020617;

/* Accent Colors */
--success-50: #f0fdf4;
--success-500: #22c55e;
--success-600: #16a34a;
--warning-50: #fffbeb;
--warning-500: #f59e0b;
--warning-600: #d97706;
--error-50: #fef2f2;
--error-500: #ef4444;
--error-600: #dc2626;
```

### 레이아웃 치수
```css
--header-height: 70px;
--sidebar-width: 280px;
--border-radius-sm: 8px;
--border-radius-md: 12px;
--border-radius-lg: 16px;
--border-radius-xl: 20px;
```

### 타이포그래피
```css
--font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
--font-size-xs: 0.75rem;
--font-size-sm: 0.875rem;
--font-size-base: 0.95rem;
--font-size-lg: 1.125rem;
--font-size-xl: 1.25rem;
--font-size-2xl: 1.5rem;
--font-size-3xl: 1.875rem;
```

### 간격 시스템
```css
--spacing-1: 0.25rem;
--spacing-2: 0.5rem;
--spacing-3: 0.75rem;
--spacing-4: 1rem;
--spacing-5: 1.25rem;
--spacing-6: 1.5rem;
--spacing-8: 2rem;
--spacing-10: 2.5rem;
--spacing-12: 3rem;
```

### 그림자 시스템
```css
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
--shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
```

### 글래스모피즘 효과
```css
--glass-bg: rgba(255, 255, 255, 0.1);
--glass-border: rgba(255, 255, 255, 0.2);
--glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
--backdrop-blur: blur(16px);
```

## 📱 반응형 브레이크포인트

### Desktop (1024px+)
- 전체 레이아웃 표시
- 사이드바 너비: 280px
- 헤더 높이: 70px

### Tablet (768px-1023px)
- 사이드바 너비 축소: 240px
- 콘텐츠 패딩 조정

### Mobile (768px-)
- 헤더만 표시
- 사이드바는 오버레이 메뉴로 전환
- 모바일 메뉴 토글 버튼 활성화

### Small Mobile (480px-)
- 헤더 높이 축소: 60px
- 로고 크기 축소
- 패딩 최소화

## 🧩 컴포넌트 구조

### 헤더 컴포넌트
```html
<header class="partners-header">
    <div class="header-brand">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <img src="assets/img/logo_white.png" alt="PHITSOL Logo" class="header-logo">
        <span class="header-title">Partners Portal</span>
    </div>
    <div class="header-user">
        <div class="user-info">
            <div class="user-name">사용자명</div>
            <div class="user-role">Business Partner</div>
        </div>
    </div>
</header>
```

### 사이드바 컴포넌트
```html
<nav class="partners-sidebar">
    <div class="sidebar-nav">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="partners-dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="contact-support.php" class="sidebar-link">
                    <i class="fas fa-envelope"></i>
                    <span>Support</span>
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="sidebar-item">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
```

### 메인 콘텐츠 영역
```html
<main class="partners-main">
    <div class="main-content">
        <!-- 페이지별 콘텐츠 -->
    </div>
</main>
```

## ✨ 주요 기능

### 1. 글래스모피즘 디자인
- 반투명 배경과 블러 효과
- 현대적이고 세련된 시각적 효과
- 깊이감 있는 레이어링

### 2. 부드러운 애니메이션
- 호버 시 카드 상승 효과
- 사이드바 링크 슬라이드 효과
- 페이드인 애니메이션

### 3. 반응형 네비게이션
- 모바일 햄버거 메뉴
- 오버레이 방식의 모바일 사이드바
- 터치 친화적 인터페이스

### 4. 접근성 고려
- 높은 색상 대비
- 포커스 표시 개선
- 키보드 네비게이션 지원

## 🛠️ 구현 가이드

### 1. CSS 파일 연결
```html
<link href="assets/css/partners-layout.css?v=<?php echo time(); ?>" rel="stylesheet">
```

### 2. 모바일 메뉴 JavaScript
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.partners-sidebar');
    const overlay = document.createElement('div');
    
    overlay.className = 'mobile-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 997;
        display: none;
    `;
    
    document.body.appendChild(overlay);
    
    function toggleMobileMenu() {
        const isOpen = sidebar.classList.contains('mobile-open');
        
        if (isOpen) {
            sidebar.classList.remove('mobile-open');
            overlay.style.display = 'none';
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.add('mobile-open');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }
    
    mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    overlay.addEventListener('click', toggleMobileMenu);
});
```

### 3. 페이지별 스타일 적용
```css
/* 페이지별 특정 스타일 */
.page-specific-card {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    padding: var(--spacing-8);
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.page-specific-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
}

.page-specific-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}
```

## 🔄 상태 관리

### 활성 메뉴 표시
```css
.sidebar-link.active {
    color: white;
    background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
    box-shadow: var(--shadow-md);
}
```

### 호버 상태
```css
.sidebar-link:hover {
    color: var(--primary-700);
    background: var(--primary-50);
    transform: translateX(4px);
}
```

### 모바일 메뉴 상태
```css
.partners-sidebar.mobile-open {
    display: block;
}
```

## 🎭 애니메이션 시스템

### 페이드인 애니메이션
```css
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.main-content {
    animation: fadeInUp 0.6s ease-out;
}
```

### 슬라이드인 애니메이션
```css
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.sidebar-item {
    animation: slideInLeft 0.4s ease-out;
}
```

## 📱 모바일 최적화

### 터치 타겟 크기
- 최소 44px × 44px 터치 영역
- 충분한 간격으로 터치 오류 방지

### 성능 최적화
- CSS 하드웨어 가속 활용
- 불필요한 리페인트 최소화
- 효율적인 애니메이션 사용

### 로딩 최적화
- CSS 파일 캐싱
- 이미지 최적화
- 폰트 로딩 최적화

## 🔧 유지보수 가이드

### 디자인 토큰 업데이트
1. CSS 변수 수정
2. 모든 페이지에서 일관성 확인
3. 브라우저 호환성 테스트

### 새로운 페이지 추가
1. 레이아웃 구조 복사
2. 페이지별 스타일 추가
3. 모바일 메뉴 JavaScript 포함

### 브라우저 지원
- Chrome 88+
- Firefox 87+
- Safari 14+
- Edge 88+

## ✅ 검증 체크리스트

### 디자인 일관성
- [ ] 모든 페이지에서 동일한 헤더/사이드바
- [ ] 일관된 색상 팔레트 사용
- [ ] 통일된 타이포그래피 적용
- [ ] 일관된 간격 시스템

### 반응형 디자인
- [ ] 모바일에서 올바른 레이아웃
- [ ] 태블릿에서 적절한 크기 조정
- [ ] 데스크톱에서 최적화된 표시
- [ ] 터치 인터페이스 동작 확인

### 접근성
- [ ] 색상 대비 충분성
- [ ] 키보드 네비게이션 지원
- [ ] 스크린 리더 호환성
- [ ] 포커스 표시 명확성

### 성능
- [ ] 페이지 로딩 속도
- [ ] 애니메이션 부드러움
- [ ] 메모리 사용량
- [ ] 네트워크 요청 최적화

## 🚀 향후 개선사항

### 계획된 기능
1. **다크 모드 지원**
   - 사용자 설정 기반 테마 전환
   - 시스템 설정 자동 감지

2. **고급 애니메이션**
   - 페이지 전환 애니메이션
   - 스크롤 기반 애니메이션
   - 인터랙티브 요소 강화

3. **개인화 옵션**
   - 사이드바 너비 조정
   - 색상 테마 선택
   - 레이아웃 커스터마이징

4. **성능 최적화**
   - 코드 스플리팅
   - 지연 로딩
   - 캐싱 전략 개선

### 기술적 개선
1. **CSS-in-JS 도입 검토**
2. **CSS Grid 고급 활용**
3. **Web Components 도입**
4. **PWA 기능 추가**

---

## 📞 지원 및 문의

디자인 시스템 관련 문의사항이나 개선 제안이 있으시면 개발팀에 연락해주세요.

**마지막 업데이트**: 2024년 12월
**버전**: 2.0 (Modern Design System) 