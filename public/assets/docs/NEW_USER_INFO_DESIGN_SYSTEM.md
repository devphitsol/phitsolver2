# 🎨 New User Info Design System

## 📋 개요

Partners Portal의 새로운 User Info 영역은 헤더 우측에 배치된 간결하고 직관적인 사용자 메뉴입니다. 기존의 복잡한 user-info 컴포넌트를 완전히 대체하여 더 깔끔하고 사용하기 쉬운 인터페이스를 제공합니다.

## 🧩 구성요소

### 1. User Trigger (사용자 트리거)
```html
<div class="user-trigger">
    <div class="user-icon">
        <span>J</span>
    </div>
    <div class="user-name">John Doe</div>
    <i class="fas fa-chevron-down dropdown-arrow"></i>
</div>
```

#### 특징
- **사용자 아이콘**: 사용자 이름의 첫 글자를 표시하는 원형 아이콘
- **사용자 이름**: 실제 사용자 이름 표시
- **드롭다운 화살표**: 메뉴가 열릴 수 있음을 나타내는 시각적 표시

### 2. User Icon (사용자 아이콘)
```css
.user-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-weight: 600;
  font-size: var(--font-size-sm);
  box-shadow: var(--shadow-sm);
  transition: all var(--unified-transition);
  position: relative;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.3);
  flex-shrink: 0;
}
```

#### 특징
- **컴팩트 크기**: 32px로 적절한 크기
- **그라데이션 배경**: 브랜드 색상 그라데이션
- **테두리 효과**: 반투명 테두리로 깊이감
- **빛나는 효과**: 호버 시 빛나는 애니메이션

### 3. User Name (사용자 이름)
```css
.user-name {
  font-weight: 600;
  color: var(--gray-800);
  font-size: var(--font-size-sm);
  margin: 0;
  transition: all var(--unified-transition);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 120px;
}
```

#### 특징
- **텍스트 오버플로우**: 긴 이름은 말줄임표로 처리
- **최대 너비**: 120px로 제한하여 레이아웃 유지
- **상태별 색상**: 호버 및 활성 상태에 따른 색상 변화

### 4. Dropdown Arrow (드롭다운 화살표)
```css
.dropdown-arrow {
  font-size: var(--font-size-xs);
  color: var(--gray-500);
  transition: transform var(--unified-transition);
  flex-shrink: 0;
}

.user-trigger.active .dropdown-arrow {
  transform: rotate(180deg);
}
```

#### 특징
- **회전 애니메이션**: 드롭다운 열림/닫힘에 따른 회전
- **색상 변화**: 상태에 따른 색상 변화
- **고정 크기**: flex-shrink: 0으로 크기 유지

## 🎭 상호작용 패턴

### 1. Hover Effects (호버 효과)
```css
.user-trigger:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-200);
  background: rgba(255, 255, 255, 0.95);
}

.user-trigger:hover .user-icon {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
  border-color: rgba(255, 255, 255, 0.5);
}

.user-trigger:hover .user-name {
  color: var(--gray-900);
  font-weight: 700;
}

.user-trigger:hover .dropdown-arrow {
  color: var(--gray-700);
}
```

#### 효과
- **전체 컨테이너**: 위로 이동 및 그림자 강화
- **아이콘**: 확대 및 테두리 색상 변화
- **이름**: 색상 및 폰트 굵기 변화
- **화살표**: 색상 변화

### 2. Active State (활성 상태)
```css
.user-trigger.active {
  background: rgba(255, 255, 255, 0.98);
  border-color: var(--primary-300);
  box-shadow: var(--shadow-lg);
}

.user-trigger.active .user-icon {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-300);
}

.user-trigger.active .user-name {
  color: var(--primary-700);
  font-weight: 700;
}

.user-trigger.active .dropdown-arrow {
  color: var(--primary-600);
  transform: rotate(180deg);
}
```

#### 효과
- **강화된 시각적 피드백**: 드롭다운이 열린 상태 표시
- **화살표 회전**: 180도 회전으로 상태 변화 명확화
- **브랜드 색상**: 활성 상태에서 브랜드 색상 강조

## 📱 드롭다운 메뉴

### 1. Dropdown Container
```css
.new-user-dropdown {
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
  min-width: 160px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-8px) scale(0.95);
  transition: all var(--unified-transition);
  z-index: 1000;
  overflow: hidden;
}

.new-user-info.active .new-user-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
}
```

#### 특징
- **우측 정렬**: 헤더 우측 끝에 고정
- **스케일 애니메이션**: 나타날 때 스케일 효과
- **반투명 배경**: 가독성과 시각적 매력성
- **상단 그라데이션**: 미묘한 장식 효과

### 2. Menu Items (메뉴 항목)
```html
<div class="new-user-dropdown">
    <a href="index.php" class="dropdown-item">
        <i class="fas fa-home"></i>
        Home
    </a>
    <div class="dropdown-divider"></div>
    <a href="logout.php" class="dropdown-item">
        <i class="fas fa-sign-out-alt"></i>
        Logout
    </a>
</div>
```

#### 메뉴 구성
- **Home**: 메인 웹사이트로 이동 (현재 탭)
- **구분선**: 메뉴 항목 간 시각적 분리
- **Logout**: 로그아웃 기능

## ♿ 접근성 (Accessibility)

### 1. ARIA 속성
```html
<div class="user-trigger" 
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
userTrigger.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        this.toggleNewUserDropdown();
    } else if (e.key === 'Escape') {
        this.closeNewUserDropdown();
    }
});
```

#### 지원 키
- **Enter**: 드롭다운 토글
- **Space**: 드롭다운 토글
- **Escape**: 드롭다운 닫기

### 3. 포커스 관리
```css
.user-trigger:focus {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}

.user-trigger:focus-visible {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}
```

## 📱 반응형 디자인

### 1. 데스크톱
- **완전 표시**: 사용자 아이콘, 이름, 화살표 모두 표시
- **호버 효과**: 마우스 호버 시 시각적 피드백
- **클릭 기반**: 클릭으로 드롭다운 제어

### 2. 모바일
```css
@media (max-width: 768px) {
  .new-user-info {
    display: none; /* 모바일에서는 숨김 */
  }
}
```

#### 모바일 대안
- **숨김 처리**: 모바일에서는 User Info 영역 숨김
- **공간 절약**: 모바일 헤더 공간 최적화
- **대안 네비게이션**: 사이드바나 다른 메뉴 활용

## 🎯 사용자 경험 (UX)

### 1. 직관적인 디자인
- **명확한 시각적 계층**: 아이콘, 이름, 화살표의 논리적 배치
- **상태 표시**: 호버, 활성, 포커스 상태의 명확한 구분
- **일관된 상호작용**: 예측 가능한 동작 패턴

### 2. 성능 최적화
- **GPU 가속**: transform과 opacity 사용으로 부드러운 애니메이션
- **최소한의 DOM 조작**: 효율적인 상태 관리
- **메모리 효율성**: 이벤트 리스너 최적화

### 3. 접근성 우선
- **키보드 지원**: 모든 기능을 키보드로 접근 가능
- **스크린 리더**: 적절한 ARIA 속성과 라벨
- **색상 대비**: 충분한 색상 대비로 가독성 보장

## 🔧 개발자 가이드

### 1. JavaScript API
```javascript
// User Info 클릭 핸들러
setupUserInfoInteractions() {
    const newUserInfo = document.querySelector('.new-user-info');
    const userTrigger = document.querySelector('.user-trigger');
    
    if (newUserInfo && userTrigger) {
        // 클릭 핸들러
        userTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleNewUserDropdown();
            
            // 햅틱 피드백
            if (navigator.vibrate) {
                navigator.vibrate(30);
            }
            
            // 시각적 피드백
            userTrigger.style.transform = 'scale(0.98)';
            setTimeout(() => {
                userTrigger.style.transform = '';
            }, 150);
        });
        
        // 키보드 지원
        userTrigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggleNewUserDropdown();
            } else if (e.key === 'Escape') {
                this.closeNewUserDropdown();
            }
        });
        
        // 외부 클릭 감지
        document.addEventListener('click', (e) => {
            if (!newUserInfo.contains(e.target)) {
                this.closeNewUserDropdown();
            }
        });
    }
}

// 드롭다운 토글
toggleNewUserDropdown() {
    const newUserInfo = document.querySelector('.new-user-info');
    const userTrigger = document.querySelector('.user-trigger');
    const dropdown = document.querySelector('.new-user-dropdown');
    
    if (newUserInfo && userTrigger && dropdown) {
        const isExpanded = userTrigger.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            this.closeNewUserDropdown();
        } else {
            this.openNewUserDropdown();
        }
    }
}
```

### 2. HTML 구조
```html
<div class="header-user">
    <div class="new-user-info">
        <div class="user-trigger" tabindex="0" role="button" aria-label="User menu">
            <div class="user-icon">
                <span>J</span>
            </div>
            <div class="user-name">John Doe</div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
        </div>
        <div class="new-user-dropdown">
            <a href="index.php" class="dropdown-item">
                <i class="fas fa-home"></i>
                Home
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

### 3. CSS 클래스
- **.new-user-info**: 메인 컨테이너
- **.user-trigger**: 클릭 가능한 트리거 영역
- **.user-icon**: 사용자 아이콘
- **.user-name**: 사용자 이름
- **.dropdown-arrow**: 드롭다운 화살표
- **.new-user-dropdown**: 드롭다운 메뉴 컨테이너
- **.dropdown-item**: 개별 메뉴 항목

### 4. 커스터마이징
```css
/* 색상 커스터마이징 */
.user-icon {
  background: linear-gradient(135deg, #your-color-1, #your-color-2);
}

/* 크기 커스터마이징 */
.user-icon {
  width: 40px;
  height: 40px;
}

/* 애니메이션 커스터마이징 */
.user-trigger {
  transition: all 0.2s ease-in-out;
}
```

## 🚀 향후 개선 계획

### 1. 기능 확장
- **사용자 프로필**: 프로필 이미지 업로드 지원
- **알림 시스템**: 사용자별 알림 표시
- **테마 전환**: 다크/라이트 모드 지원

### 2. 성능 최적화
- **지연 로딩**: 필요 시에만 드롭다운 렌더링
- **메모리 최적화**: 이벤트 리스너 정리
- **애니메이션 최적화**: CSS 변수 활용

### 3. 접근성 향상
- **음성 명령**: 음성 인터페이스 지원
- **고대비 모드**: 고대비 모드 최적화
- **다국어 지원**: 국제화(i18n) 지원

---

## 📝 변경 이력

### v1.0.2 (2024-01-XX)
- ✅ Home 링크 새 탭 열기 → 현재 탭 이동으로 변경
- ✅ 외부 링크 아이콘 제거
- ✅ Home 클릭 시 알림 제거

### v1.0.1 (2024-01-XX)
- ✅ User Menu Open 알림 제거
- ✅ 드롭다운 열림 시 불필요한 알림 제거

### v1.0.0 (2024-01-XX)
- ✅ 기존 user-info 컴포넌트 완전 삭제
- ✅ 새로운 User Info 영역 구현
- ✅ 헤더 우측 끝 고정 배치
- ✅ Home, Logout 메뉴 항목 구현
- ✅ 반응형 디자인 적용
- ✅ 접근성 가이드라인 준수
- ✅ JavaScript API 구현
- ✅ 문서화 완료 