# Partners Portal - Home Link Feature

## 🏠 개요

Partners Portal에 메인 웹사이트(Home page)로 이동할 수 있는 기능을 추가했습니다. 사용자가 Partners Portal 내에서 언제든지 메인 웹사이트로 쉽게 이동할 수 있도록 헤더와 사용자 드롭다운 메뉴에 Home 링크를 제공합니다.

## 🎯 주요 기능

### 1. 헤더 Home 링크
- **위치**: 헤더 좌측 (제목 옆)
- **아이콘**: FontAwesome `fa-home` 아이콘
- **동작**: 현재 탭에서 메인 웹사이트로 이동
- **디자인**: 그라데이션 배경의 버튼 형태
- **애니메이션**: 호버 시 빛나는 효과와 확대

### 2. 사용자 드롭다운 Home 링크
- **위치**: 사용자 정보 드롭다운 메뉴 최상단
- **아이콘**: FontAwesome `fa-home` 아이콘
- **동작**: 현재 탭에서 메인 웹사이트로 이동
- **스타일**: 일반 드롭다운 메뉴 항목

### 3. 상호작용 피드백
- **알림**: 링크 클릭 시 알림 메시지 표시
- **햅틱 피드백**: 모바일에서 진동 피드백
- **시각적 피드백**: 클릭 시 스케일 애니메이션
- **툴팁**: 마우스 호버 시 설명 표시

## 🎨 디자인 특징

### 1. 헤더 Home 링크 스타일
```css
.header-home-link {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-3) var(--spacing-4);
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  color: var(--white);
  text-decoration: none;
  border-radius: var(--unified-border-radius);
  font-weight: 600;
  font-size: var(--font-size-sm);
  transition: all var(--unified-transition);
  box-shadow: var(--shadow-md);
  position: relative;
  overflow: hidden;
}

.header-home-link::before {
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

.header-home-link:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
  background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
}

.header-home-link:hover::before {
  transform: translateX(100%);
}
```

#### 특징
- **그라데이션 배경**: 브랜드 색상 그라데이션
- **빛나는 효과**: 호버 시 빛나는 애니메이션
- **부드러운 전환**: 위로 이동 및 그림자 강화
- **아이콘 확대**: 호버 시 아이콘 확대 효과

### 2. 드롭다운 메뉴 스타일
```css
.dropdown-item[href*="index.php"]::after {
  content: '\f08e';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  right: var(--spacing-4);
  top: 50%;
  transform: translateY(-50%);
  font-size: var(--font-size-xs);
  color: var(--gray-400);
  transition: all var(--unified-transition);
}
```

#### 특징
- **외부 링크 표시**: 외부 링크 아이콘으로 구분
- **반응형**: 모든 화면 크기에서 적절히 표시
- **접근성**: 스크린 리더 지원

## 🔧 기술적 구현

### 1. HTML 구조
```html
<!-- 헤더 Home 링크 -->
<div class="partners-header">
  <div class="header-left">
    <div>
      <h1 class="header-title">Dashboard</h1>
      <p class="text-muted mb-0">Welcome back, User</p>
    </div>
    <a href="index.php" class="header-home-link" target="_blank" title="Open Home page in new tab">
      <i class="fas fa-home"></i>
      <span>Home</span>
    </a>
  </div>
  <!-- 사용자 정보... -->
</div>

<!-- 사용자 드롭다운 Home 링크 -->
<div class="user-dropdown">
  <a href="index.php" class="dropdown-item" target="_blank">
    <i class="fas fa-home"></i>
    Home
  </a>
  <!-- 기타 메뉴 항목들... -->
</div>
```

#### 속성 설명
- **`href="index.php"`**: 메인 웹사이트 링크
- **`target="_blank"`**: 새 탭에서 열기
- **`class="sidebar-link"`**: 사이드바 링크 스타일
- **`class="dropdown-item"`**: 드롭다운 메뉴 스타일

### 2. JavaScript 기능
```javascript
setupHomeLinkInteractions() {
  const homeLinks = document.querySelectorAll('a[href*="index.php"]');
  
  homeLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      // 알림 표시
      this.showNotification('🏠 Opening Home page in new tab...', 'info', 2000);
      
      // 햅틱 피드백
      if (navigator.vibrate) {
        navigator.vibrate(50);
      }
      
      // 시각적 피드백
      link.style.transform = 'scale(0.95)';
      setTimeout(() => {
        link.style.transform = '';
      }, 150);
    });
    
    // 툴팁 추가
    link.setAttribute('title', 'Open Home page in new tab');
    link.setAttribute('aria-label', 'Open Home page in new tab');
  });
}
```

#### 기능 설명
- **이벤트 리스너**: 모든 Home 링크에 클릭 이벤트 추가
- **알림 시스템**: 사용자에게 새 탭 열림 알림
- **햅틱 피드백**: 모바일에서 진동 피드백
- **시각적 피드백**: 클릭 시 스케일 애니메이션
- **접근성**: 툴팁과 ARIA 라벨 추가

## 📱 반응형 디자인

### 1. 데스크톱
- **헤더**: 항상 표시되는 Home 링크 (그라데이션 버튼)
- **드롭다운**: 호버 시 나타나는 Home 링크
- **애니메이션**: 호버 시 빛나는 효과와 확대

### 2. 모바일
- **헤더**: 모바일에서는 Home 링크 숨김 (공간 절약)
- **드롭다운**: 터치로 열리는 드롭다운에서 Home 링크
- **햅틱 피드백**: 터치 시 진동 피드백

### 3. 태블릿
- **중간 크기**: 데스크톱과 모바일의 중간 동작
- **적응형 레이아웃**: 화면 크기에 따른 자동 조정

## ♿ 접근성 (Accessibility)

### 1. 키보드 네비게이션
- **Tab 키**: 키보드로 Home 링크에 포커스 가능
- **Enter 키**: 링크 활성화
- **Space 키**: 링크 활성화

### 2. 스크린 리더 지원
```html
<a href="index.php" 
   class="sidebar-link" 
   target="_blank"
   title="Open Home page in new tab"
   aria-label="Open Home page in new tab">
  <i class="fas fa-home" aria-hidden="true"></i>
  Home
</a>
```

#### ARIA 속성
- **`title`**: 마우스 호버 시 툴팁
- **`aria-label`**: 스크린 리더용 라벨
- **`aria-hidden="true"`**: 아이콘을 스크린 리더에서 숨김

### 3. 시각적 접근성
- **외부 링크 표시**: 명확한 외부 링크 아이콘
- **색상 대비**: 충분한 색상 대비
- **포커스 표시**: 명확한 포커스 아웃라인

## 🎯 사용자 경험 (UX)

### 1. 직관적 네비게이션
- **일관된 위치**: 모든 페이지에서 동일한 위치
- **명확한 아이콘**: Home 아이콘으로 직관적 이해
- **외부 링크 표시**: 새 탭에서 열림을 명확히 표시

### 2. 피드백 시스템
- **알림 메시지**: 사용자에게 동작 확인
- **시각적 피드백**: 클릭 시 즉시 반응
- **햅틱 피드백**: 모바일에서 물리적 피드백

### 3. 성능 최적화
- **새 탭 열기**: 현재 작업 중단 없음
- **빠른 로딩**: 메인 웹사이트 빠른 접근
- **메모리 효율**: 효율적인 이벤트 처리

## 🔄 적용된 페이지

### 1. Partners Dashboard
- **파일**: `public/partners-dashboard.php`
- **위치**: 헤더 좌측, 사용자 드롭다운 최상단

### 2. Profile Page
- **파일**: `public/profile.php`
- **위치**: 헤더 좌측, 사용자 드롭다운 최상단

### 3. Contact Support
- **파일**: `public/contact-support.php`
- **위치**: 헤더 좌측, 사용자 드롭다운 최상단

## 🎨 커스터마이징

### 1. 아이콘 변경
```css
.sidebar-link[href*="index.php"]::after {
  content: '\f015'; /* 다른 FontAwesome 아이콘 */
}
```

### 2. 색상 변경
```css
.sidebar-link[href*="index.php"]:hover::after {
  color: var(--success-500); /* 다른 색상 */
}
```

### 3. 애니메이션 조정
```css
.sidebar-link[href*="index.php"]::after {
  transition: all 500ms ease-in-out; /* 다른 전환 효과 */
}
```

## 🔮 향후 개선 계획

### 1. 단기 목표
- **방문 기록**: Home 페이지 방문 횟수 추적
- **즐겨찾기**: 자주 사용하는 페이지 즐겨찾기 기능
- **검색 기능**: 메인 웹사이트 내 검색 기능 연동

### 2. 장기 목표
- **개인화**: 사용자별 맞춤 Home 페이지
- **알림 시스템**: 메인 웹사이트 업데이트 알림
- **통합 대시보드**: Partners Portal과 메인 웹사이트 통합 뷰

## 📊 사용 통계

### 1. 추적 가능한 메트릭
- **클릭 횟수**: Home 링크 클릭 빈도
- **사용자 패턴**: 언제 Home 링크를 사용하는지
- **세션 길이**: Home 페이지 방문 후 Partners Portal 복귀 시간

### 2. 분석 도구
- **Google Analytics**: 페이지 방문 추적
- **사용자 행동 분석**: 클릭 패턴 분석
- **A/B 테스트**: 다양한 Home 링크 위치 테스트

## 🛠️ 개발자 가이드

### 1. 새로운 페이지에 Home 링크 추가
```html
<!-- 헤더에 추가 -->
<div class="partners-header">
  <div class="header-left">
    <div>
      <h1 class="header-title">Page Title</h1>
      <p class="text-muted mb-0">Page description</p>
    </div>
    <a href="index.php" class="header-home-link" target="_blank" title="Open Home page in new tab">
      <i class="fas fa-home"></i>
      <span>Home</span>
    </a>
  </div>
  <!-- 사용자 정보... -->
</div>

<!-- 사용자 드롭다운에 추가 -->
<div class="user-dropdown">
  <a href="index.php" class="dropdown-item" target="_blank">
    <i class="fas fa-home"></i>
    Home
  </a>
  <!-- 기타 메뉴 항목들... -->
</div>
```

### 2. CSS 스타일 확인
```css
/* 헤더 Home 링크 스타일이 자동으로 적용됩니다 */
.header-home-link {
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  /* 그라데이션 배경과 애니메이션 자동 적용 */
}
```

### 3. JavaScript 기능 확인
```javascript
// Home 링크 기능이 자동으로 초기화됩니다
window.modernUnifiedLayout.setupHomeLinkInteractions();
```

---

**버전**: 1.0.0  
**최종 업데이트**: 2024년 12월  
**기능 상태**: 활성화  
**담당자**: 개발팀 & UX팀 