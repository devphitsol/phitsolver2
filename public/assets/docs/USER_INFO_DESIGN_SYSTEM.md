# Partners Portal - User Info Design System

## 👤 개요

Partners Portal의 현대적이고 세련된 사용자 정보 인터페이스입니다. 아바타, 드롭다운 메뉴, 상태 표시 등 다양한 요소를 통해 직관적이고 매력적인 사용자 경험을 제공합니다.

## 🎨 디자인 구성 요소

### 1. User Avatar (사용자 아바타)
```css
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-weight: 600;
  font-size: var(--font-size-base);
  box-shadow: var(--shadow-sm);
  transition: all var(--unified-transition);
  position: relative;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.3);
}
```

#### 특징
- **컴팩트 디자인**: 36px 크기로 더 자연스러운 비율
- **그라데이션 배경**: 브랜드 색상 그라데이션
- **이니셜 표시**: 사용자 이름의 첫 글자
- **테두리 효과**: 반투명 테두리로 깊이감 추가
- **부드러운 애니메이션**: 호버 시 미묘한 확대 효과

### 2. User Status (사용자 상태)
```css
.user-status {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  background: var(--success-500);
  border: 2px solid var(--white);
  border-radius: 50%;
  box-shadow: var(--shadow-sm);
  animation: pulse 2s infinite;
}
```

#### 특징
- **온라인 상태**: 녹색 점으로 온라인 상태 표시
- **맥박 애니메이션**: 2초마다 반복되는 맥박 효과
- **위치**: 아바타 우상단에 배치
- **테두리**: 흰색 테두리로 구분

### 3. User Details (사용자 정보)
```css
.user-details {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--spacing-1);
}

.user-name {
  font-weight: 600;
  color: var(--gray-800);
  font-size: var(--font-size-sm);
  margin: 0;
  transition: all var(--unified-transition);
  line-height: 1.2;
}

.user-role {
  font-size: var(--font-size-xs);
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
  background: var(--gray-50);
  padding: var(--spacing-1) var(--spacing-2);
  border-radius: 9999px;
  transition: all var(--unified-transition);
  border: 1px solid var(--gray-100);
}
```

#### 특징
- **자연스러운 타이포그래피**: 더 읽기 쉬운 폰트 크기와 색상
- **역할 배지**: 테두리가 있는 배지 형태의 역할 표시
- **상태별 색상**: 호버 및 활성 상태에 따른 색상 변화
- **부드러운 전환**: 모든 상태 변화에 부드러운 애니메이션

## 🎭 상호작용 패턴

### 1. Click & Hover Effects (클릭 및 호버 효과)
```css
.user-info:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-200);
  background: rgba(255, 255, 255, 0.95);
}

.user-info.active {
  background: rgba(255, 255, 255, 0.98);
  border-color: var(--primary-300);
  box-shadow: var(--shadow-lg);
}

.user-info:hover .user-avatar {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
  border-color: rgba(255, 255, 255, 0.5);
}

.user-info.active .user-avatar {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-300);
}

.user-info:hover .user-name {
  color: var(--gray-900);
  font-weight: 700;
}

.user-info.active .user-name {
  color: var(--primary-700);
  font-weight: 700;
}

.user-info:hover .user-role {
  background: var(--primary-50);
  color: var(--primary-600);
  border-color: var(--primary-200);
}

.user-info.active .user-role {
  background: var(--primary-100);
  color: var(--primary-700);
  border-color: var(--primary-300);
}
```

#### 효과
- **호버 상태**: 미묘한 위로 이동 및 배경 투명도 증가
- **활성 상태**: 클릭 시 더 강한 시각적 피드백
- **아바타**: 부드러운 확대 및 테두리 색상 변화
- **텍스트**: 색상 및 폰트 굵기 변화로 상태 표시
- **역할 배지**: 배경색과 테두리 색상 변화

### 2. Enhanced Interactions (향상된 상호작용)
```css
.user-avatar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
  transform: translateX(-100%);
  transition: transform var(--unified-transition);
}

.user-info:hover .user-avatar::before {
  transform: translateX(100%);
}

.dropdown-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
  opacity: 0;
  transition: opacity var(--unified-transition);
  z-index: -1;
}

.dropdown-item:hover::before {
  opacity: 0.05;
}
```

#### 효과
- **아바타 빛나는 효과**: 호버 시 아바타에 빛나는 애니메이션
- **드롭다운 배경 효과**: 메뉴 항목 호버 시 미묘한 배경 변화
- **부드러운 전환**: 모든 애니메이션에 300ms cubic-bezier 전환
- **시각적 피드백**: 클릭 시 스케일 애니메이션과 햅틱 피드백
- **로그아웃 확인**: 로그아웃 시 확인 다이얼로그 표시

## 📱 드롭다운 메뉴

### 1. Dropdown Container
```css
.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: var(--spacing-2);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: var(--backdrop-blur);
  -webkit-backdrop-filter: var(--backdrop-blur);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--unified-border-radius);
  box-shadow: var(--shadow-lg);
  min-width: 180px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-8px) scale(0.95);
  transition: all var(--unified-transition);
  z-index: 1000;
  overflow: hidden;
}

.user-info.active .user-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
}

.user-dropdown::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--primary-300), transparent);
}
```

#### 특징
- **자연스러운 배경**: 반투명 흰색 배경으로 가독성 향상
- **클릭 기반 동작**: 호버가 아닌 클릭으로 드롭다운 제어
- **스케일 애니메이션**: 나타날 때 스케일 효과로 부드러운 전환
- **상단 그라데이션**: 드롭다운 상단에 미묘한 그라데이션 선
- **자동 닫기**: 외부 클릭 시 자동으로 닫힘

### 2. Dropdown Items
```css
.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3) var(--spacing-4);
  color: var(--gray-700);
  text-decoration: none;
  font-size: var(--font-size-sm);
  font-weight: 500;
  transition: all var(--unified-transition);
  border-bottom: 1px solid var(--gray-100);
}

.dropdown-item:hover {
  background: var(--primary-50);
  color: var(--primary-700);
  transform: translateX(4px);
}
```

#### 특징
- **아이콘**: 각 메뉴 항목에 아이콘 포함
- **호버 효과**: 호버 시 배경색 변경 및 이동
- **구분선**: 항목 간 구분선

### 3. Dropdown Divider
```css
.dropdown-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gray-300), transparent);
  margin: var(--spacing-2) 0;
}
```

#### 특징
- **그라데이션**: 양쪽이 투명한 그라데이션 선
- **시각적 구분**: 메뉴 항목 그룹화

## 🌐 반응형 디자인

### 1. Desktop (데스크톱)
```css
/* Desktop에서는 호버로 드롭다운 표시 */
.user-info:hover .user-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
```

### 2. Mobile (모바일)
```css
@media (max-width: 768px) {
  .user-info {
    display: none; /* 모바일에서는 숨김 */
  }
  
  .user-dropdown {
    display: none;
  }
}
```

#### 모바일 대안
- **클릭 이벤트**: 터치 디바이스에서 클릭으로 드롭다운 제어
- **키보드 지원**: Enter/Space 키로 드롭다운 제어

## ♿ 접근성 (Accessibility)

### 1. ARIA 속성
```html
<div class="user-info" 
     tabindex="0" 
     role="button" 
     aria-label="User menu" 
     aria-expanded="false">
```

#### 속성 설명
- **tabindex="0"**: 키보드 포커스 가능
- **role="button"**: 버튼 역할 명시
- **aria-label**: 스크린 리더용 라벨
- **aria-expanded**: 드롭다운 상태 표시

### 2. 키보드 네비게이션
```javascript
userInfo.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    this.toggleUserDropdown();
  }
});
```

#### 지원 키
- **Enter**: 드롭다운 토글
- **Space**: 드롭다운 토글
- **Escape**: 드롭다운 닫기

### 3. 포커스 관리
```css
.user-info:focus {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}

.user-info:focus-visible {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}
```

## 🎯 사용자 경험 (UX)

### 1. 시각적 피드백
- **호버 상태**: 명확한 시각적 피드백
- **포커스 상태**: 키보드 사용자를 위한 포커스 표시
- **활성 상태**: 현재 선택된 항목 강조

### 2. 애니메이션
- **부드러운 전환**: 300ms cubic-bezier 전환
- **자연스러운 움직임**: 물리학 기반 애니메이션
- **성능 최적화**: GPU 가속 애니메이션

### 3. 일관성
- **브랜드 색상**: 일관된 색상 사용
- **간격 시스템**: 통일된 간격 사용
- **타이포그래피**: 일관된 폰트 스타일

## 🔧 개발자 가이드

### 1. JavaScript API
```javascript
// User Info 클릭 핸들러
setupUserInfoInteractions() {
    const userInfo = document.querySelector('.user-info');
    
    if (userInfo) {
        // 모든 디바이스에서 클릭으로 드롭다운 제어
        userInfo.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleUserDropdown();
            
            // 햅틱 피드백 (모바일)
            if (navigator.vibrate) {
                navigator.vibrate(30);
            }
            
            // 시각적 피드백
            userInfo.style.transform = 'scale(0.98)';
            setTimeout(() => {
                userInfo.style.transform = '';
            }, 150);
        });
        
        // 키보드 지원
        userInfo.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggleUserDropdown();
            } else if (e.key === 'Escape') {
                this.closeUserDropdown();
            }
        });
        
        // 외부 클릭 시 자동 닫기
        document.addEventListener('click', (e) => {
            if (!userInfo.contains(e.target)) {
                this.closeUserDropdown();
            }
        });
    }
}

// 드롭다운 토글
toggleUserDropdown() {
    const userInfo = document.querySelector('.user-info');
    const dropdown = document.querySelector('.user-dropdown');
    
    if (userInfo && dropdown) {
        const isExpanded = userInfo.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            this.closeUserDropdown();
        } else {
            this.openUserDropdown();
        }
    }
}

// 드롭다운 열기
openUserDropdown() {
    const userInfo = document.querySelector('.user-info');
    const dropdown = document.querySelector('.user-dropdown');
    
    if (userInfo && dropdown) {
        userInfo.classList.add('active');
        dropdown.style.opacity = '1';
        dropdown.style.visibility = 'visible';
        dropdown.style.transform = 'translateY(0) scale(1)';
        userInfo.setAttribute('aria-expanded', 'true');
        
        // 알림 표시
        this.showNotification('👤 User menu opened', 'info', 1500);
    }
}

// 드롭다운 닫기
closeUserDropdown() {
    const userInfo = document.querySelector('.user-info');
    const dropdown = document.querySelector('.user-dropdown');
    
    if (userInfo && dropdown) {
        userInfo.classList.remove('active');
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.transform = 'translateY(-8px) scale(0.95)';
        userInfo.setAttribute('aria-expanded', 'false');
    }
}

// 드롭다운 아이템 인터랙션
setupDropdownItemInteractions() {
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    dropdownItems.forEach(item => {
        item.addEventListener('click', (e) => {
            // 클릭 피드백
            item.style.transform = 'translateX(3px) scale(0.98)';
            setTimeout(() => {
                item.style.transform = '';
            }, 150);
            
            // 햅틱 피드백
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
            
            // 로그아웃 특별 처리
            if (item.href && item.href.includes('logout.php')) {
                e.preventDefault();
                this.handleLogout(item.href);
            }
            
            // Home 특별 처리
            if (item.href && item.href.includes('index.php')) {
                this.showNotification('🏠 Opening Home page...', 'info', 2000);
            }
            
            // 드롭다운 닫기
            setTimeout(() => {
                this.closeUserDropdown();
            }, 200);
        });
    });
}

// 로그아웃 처리
handleLogout(logoutUrl) {
    if (confirm('Are you sure you want to logout?')) {
        this.showNotification('👋 Logging out...', 'info', 2000);
        
        const userInfo = document.querySelector('.user-info');
        if (userInfo) {
            userInfo.style.animation = 'fadeOut 0.5s ease-out';
        }
        
        setTimeout(() => {
            window.location.href = logoutUrl;
        }, 1000);
    }
}
```

### 2. HTML 구조
```html
<div class="header-user">
  <div class="user-info" tabindex="0" role="button" aria-label="User menu">
    <div class="user-avatar">
      <span>J</span>
      <div class="user-status"></div>
    </div>
    <div class="user-details">
      <div class="user-name">John Doe</div>
      <div class="user-role">Business Partner</div>
    </div>
    <div class="user-dropdown">
      <a href="profile.php" class="dropdown-item">
        <i class="fas fa-user"></i>
        Profile
      </a>
      <a href="support.php" class="dropdown-item">
        <i class="fas fa-headset"></i>
        Support
      </a>
      <div class="dropdown-divider"></div>
      <a href="logout.php" class="dropdown-item">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </div>
</div>
```

### 2. JavaScript API
```javascript
// 드롭다운 토글
window.modernUnifiedLayout.toggleUserDropdown();

// 사용자 정보 업데이트
window.modernUnifiedLayout.updateUserInfo({
  name: 'John Doe',
  role: 'Business Partner',
  avatar: 'J'
});
```

### 3. CSS 클래스
```css
/* 주요 클래스 */
.user-info          /* 사용자 정보 컨테이너 */
.user-avatar        /* 사용자 아바타 */
.user-status        /* 온라인 상태 표시 */
.user-details       /* 사용자 상세 정보 */
.user-name          /* 사용자 이름 */
.user-role          /* 사용자 역할 */
.user-dropdown      /* 드롭다운 메뉴 */
.dropdown-item      /* 드롭다운 항목 */
.dropdown-divider   /* 드롭다운 구분선 */
```

## 🎨 커스터마이징

### 1. 색상 변경
```css
:root {
  --user-avatar-bg: var(--gradient-primary);
  --user-status-color: var(--success-500);
  --user-name-color: var(--gray-900);
  --user-role-bg: var(--gray-100);
  --user-role-color: var(--gray-600);
}
```

### 2. 크기 조정
```css
.user-avatar {
  width: 48px;  /* 기본: 40px */
  height: 48px; /* 기본: 40px */
}

.user-status {
  width: 14px;  /* 기본: 12px */
  height: 14px; /* 기본: 12px */
}
```

### 3. 애니메이션 조정
```css
.user-info {
  transition: all 400ms ease-out; /* 기본: 300ms cubic-bezier */
}

.user-avatar {
  transition: all 500ms ease-in-out; /* 기본: 300ms cubic-bezier */
}
```

## 📊 성능 최적화

### 1. CSS 최적화
- **GPU 가속**: transform3d 사용
- **효율적인 선택자**: 최적화된 CSS 선택자
- **애니메이션 최적화**: will-change 속성 사용

### 2. JavaScript 최적화
- **이벤트 위임**: 효율적인 이벤트 처리
- **디바운싱**: 불필요한 이벤트 방지
- **메모리 관리**: 이벤트 리스너 정리

### 3. 이미지 최적화
- **SVG 아이콘**: 벡터 아이콘 사용
- **WebP 포맷**: 최신 이미지 포맷
- **지연 로딩**: 필요시에만 로드

## 🔮 향후 계획

### 1. 단기 목표
- **다크 모드**: 다크 테마 지원
- **애니메이션 개선**: 더 부드러운 애니메이션
- **접근성 강화**: WCAG 2.1 AA 준수

### 2. 장기 목표
- **AI 아바타**: AI 생성 아바타
- **실시간 상태**: 실시간 온라인 상태
- **개인화**: 사용자별 커스터마이징

---

**버전**: 1.0.0  
**최종 업데이트**: 2024년 12월  
**디자인 시스템**: Modern UI  
**담당자**: 디자인팀 & 개발팀 