# UNIFIED ADMIN DESIGN SYSTEM

## 개요
관리자 영역의 UI/UX 일관성을 유지하기 위해 통일된 디자인 시스템을 구축하고 적용했습니다.

## 목표
- slider, blog, users 페이지와 동일한 구조와 디자인 적용
- company, products 페이지의 디자인 통일성 확보
- 전체 admin 영역의 일관된 레이아웃과 스타일 제공

## 적용된 페이지

### 1. Company Management
- **파일**: `admin/views/company/company.php`
- **변경사항**:
  - `content-header`, `content-body` 컴포넌트 적용
  - `admin-card`, `admin-card-header`, `admin-card-body` 구조 사용
  - 통일된 버튼 스타일과 아이콘 적용

### 2. Company Detail
- **파일**: `admin/views/company/company-detail.php`
- **변경사항**:
  - 통일된 테이블 구조 (`admin-table`) 적용
  - 회사 아바타와 상태 배지 스타일 통일
  - 버튼 그룹과 액션 스타일 일관성 확보

### 3. Products Management
- **파일**: `admin/views/products/content.php`
- **변경사항**:
  - 통일된 헤더와 통계 카드 구조 적용
  - 검색 및 필터 섹션을 `admin-card`로 재구성
  - 제품 테이블을 `admin-table`로 통일

## 디자인 시스템 컴포넌트

### 1. 레이아웃 컴포넌트
- `content-header`: 페이지 헤더 영역
- `content-body`: 메인 콘텐츠 영역
- `admin-card`: 카드 형태의 컨테이너

### 2. 통계 컴포넌트
- `stats-cards`: 통계 카드 그리드
- `stat-card`: 개별 통계 카드
- `stat-icon`: 통계 아이콘
- `stat-content`: 통계 내용

### 3. 테이블 컴포넌트
- `admin-table`: 통일된 테이블 스타일
- `table-header`: 테이블 헤더
- `table-actions`: 테이블 액션 버튼

### 4. 버튼 컴포넌트
- `btn-primary`: 주요 액션 버튼
- `btn-secondary`: 보조 액션 버튼
- `btn-outline-*`: 아웃라인 스타일 버튼
- `btn-success`, `btn-warning`, `btn-danger`: 상태별 버튼

### 5. 배지 컴포넌트
- `badge-success`: 성공 상태
- `badge-warning`: 경고 상태
- `badge-danger`: 위험 상태
- `badge-secondary`: 보조 상태
- `badge-info`: 정보 상태

### 6. 폼 컴포넌트
- `form-group`: 폼 그룹
- `form-label`: 폼 라벨
- `form-control`: 폼 입력 필드
- `form-select`: 폼 선택 필드

### 7. 아바타 및 썸네일
- `company-avatar`: 회사 아바타
- `product-thumb-placeholder`: 제품 썸네일 플레이스홀더

### 8. 상태 표시
- `status-badge`: 상태 배지
- `status-active`: 활성 상태
- `status-pending`: 대기 상태
- `status-inactive`: 비활성 상태

## CSS 변수 시스템

### 색상 팔레트
```css
--primary-color: #667eea
--secondary-color: #28a745
--warning-color: #ffc107
--info-color: #17a2b8
--danger-color: #dc3545
```

### 간격 시스템
```css
--spacing-xs: 0.25rem
--spacing-sm: 0.5rem
--spacing-md: 1rem
--spacing-lg: 1.5rem
--spacing-xl: 2rem
--spacing-xxl: 3rem
```

### 테두리 반경
```css
--border-radius-sm: 4px
--border-radius-md: 8px
--border-radius-lg: 12px
--border-radius-xl: 16px
```

### 그림자 시스템
```css
--shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1)
--shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1)
--shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1)
--shadow-xl: 0 12px 24px rgba(0, 0, 0, 0.15)
```

## 반응형 디자인

### 브레이크포인트
- **768px 이하**: 모바일 최적화
- **480px 이하**: 소형 모바일 최적화

### 모바일 최적화
- 통계 카드 그리드 단일 컬럼으로 변경
- 테이블 스택 모드 적용
- 버튼 크기 및 간격 조정

## 접근성 개선

### 키보드 네비게이션
- 포커스 표시기 추가
- 탭 순서 최적화
- 스킵 링크 제공

### 스크린 리더 지원
- 적절한 ARIA 라벨
- 의미있는 HTML 구조
- 상태 정보 제공

## 성능 최적화

### CSS 최적화
- 중복 스타일 제거
- CSS 변수 활용
- 미디어 쿼리 최적화

### 로딩 최적화
- CSS 파일 통합
- 캐시 버스팅 적용
- 폰트 프리로드

## 사용법

### 새로운 페이지 추가 시
1. `content-header` 컴포넌트 사용
2. `content-body` 내에서 `admin-card` 구조 활용
3. 통일된 버튼 클래스 적용
4. CSS 변수 활용하여 일관된 스타일 적용

### 예시 코드
```html
<!-- Content Header -->
<div class="content-header">
    <div class="header-info">
        <h1 class="page-title">
            <i class="fas fa-icon"></i>
            페이지 제목
        </h1>
        <div class="stats-info">
            <span class="stat-item">통계 정보</span>
        </div>
    </div>
    <div class="header-actions">
        <a href="#" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            새로 만들기
        </a>
    </div>
</div>

<!-- Content Body -->
<div class="content-body">
    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="admin-card-title">
                <i class="fas fa-list"></i>
                카드 제목
            </h5>
        </div>
        <div class="admin-card-body">
            <!-- 콘텐츠 -->
        </div>
    </div>
</div>
```

## 완료된 작업

### ✅ 적용된 페이지
- [x] Company Management (company.php)
- [x] Company Detail (company-detail.php)
- [x] Products Management (content.php)

### ✅ 통일된 컴포넌트
- [x] Content Header
- [x] Content Body
- [x] Admin Cards
- [x] Statistics Grid
- [x] Tables
- [x] Buttons
- [x] Badges
- [x] Forms
- [x] Avatars
- [x] Status Indicators

### ✅ 디자인 시스템
- [x] CSS 변수 시스템
- [x] 반응형 디자인
- [x] 접근성 개선
- [x] 성능 최적화
- [x] 일관된 스타일링

## 결과

이제 전체 관리자 영역이 통일된 디자인 시스템을 사용하여 일관된 UI/UX를 제공합니다. 모든 페이지가 동일한 레이아웃 구조와 스타일을 사용하며, 향후 새로운 페이지 추가 시에도 이 디자인 시스템을 활용하여 일관성을 유지할 수 있습니다. 