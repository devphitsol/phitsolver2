# Admin Dashboard - Company Management Feature

## 🏢 개요

Admin Dashboard에 Company Management 기능을 추가했습니다. 이 기능을 통해 관리자는 등록된 Business User 계정들의 목록을 확인하고, 각 회사의 프로필을 관리할 수 있습니다.

## 🎯 주요 기능

### 1. Business Companies Overview (비즈니스 회사 개요)
- **위치**: 상단 헤더 섹션
- **기능**: 등록된 Business User 계정 수 표시
- **표시 정보**: 총 회사 수, 관리 설명

### 2. Business Companies List (비즈니스 회사 목록)
- **위치**: 메인 테이블 섹션
- **기능**: 등록된 모든 Business User 계정 목록 표시
- **표시 정보**: 
  - Company Name (회사명)
  - Contact Person (담당자)
  - Email (이메일)
  - Status (상태)
  - Registration Date (등록일)
  - Actions (작업)

### 3. Company Profile Actions (회사 프로필 작업)
- **Update Company Profile**: 회사 정보 수정 페이지로 이동

### 4. 회사 프로필 편집 페이지
- **Company Profile 섹션**: 회사 기본 정보 편집
- **Authorized Contact Persons 섹션**: 담당자 정보 편집
- **Required Documents List 섹션**: 문서 제출 현황 관리
- 각 섹션별 독립적인 편집 모드 (UPDATE 버튼 클릭 시 활성화)
- 저장 시 토스트 알림 표시

### 5. Status Management (상태 관리)
- **Active**: 활성 상태
- **Pending**: 승인 대기 상태
- **Inactive**: 비활성 상태

## 🎨 UI/UX 설계

### 레이아웃 구성
- **헤더 섹션**: 상단에 Business Companies Management 제목과 총 회사 수 표시
- **테이블 레이아웃**: 메인 섹션에 Business User 목록을 테이블 형태로 표시
- **카드형 디자인**: 각 섹션을 카드 형태로 구분하여 시각적 명확성 제공

### 인터랙션 설계
- **회사 아바타**: 각 회사의 첫 글자를 원형 아바타로 표시
- **상태 배지**: Active, Pending, Inactive 상태를 색상별 배지로 구분
- **액션 버튼**: Update Company Profile 버튼으로 각 회사 관리
- **반응형 테이블**: 모바일에서도 적절히 표시되도록 반응형 디자인

### 반응형 디자인
- **모바일 최적화**: 768px 이하에서 버튼들이 세로로 배치
- **유연한 그리드**: 화면 크기에 따라 카드 레이아웃 자동 조정
- **터치 친화적**: 모바일에서 터치하기 쉬운 버튼 크기와 간격

## 🔧 기술적 구현

### 파일 구조
```
admin/
├── views/
│   ├── company.php          # 회사 프로필 편집 페이지
│   ├── company-detail.php   # 회사 목록 페이지
│   └── layout.php           # 사이드바에 Company Management 메뉴 추가
├── assets/
│   ├── css/
│   │   └── admin.css        # Company Management 스타일 추가
│   └── docs/
│       └── COMPANY_MANAGEMENT_FEATURE.md  # 이 문서
└── index.php                # Company Management 라우팅 추가
```

### 라우팅
```php
case 'company':
    $pageTitle = 'Company Management';
    
    // Check if method parameter exists
    $method = $_GET['method'] ?? 'list';
    
    if ($method === 'edit' && isset($_GET['id'])) {
        // Load company edit page
        ob_start();
        include 'views/company.php';
        $pageContent = ob_get_clean();
        include 'views/layout.php';
    } else {
        // Load company list page
        ob_start();
        include 'views/company-detail.php';
        $pageContent = ob_get_clean();
        include 'views/layout.php';
    }
    break;
```

### JavaScript 기능
- **폼 상태 관리**: 읽기 전용 ↔ 수정 가능 상태 전환
- **이벤트 핸들링**: 버튼 클릭, 폼 제출, 취소 처리
- **토스트 알림**: Bootstrap Toast를 활용한 성공 메시지 표시
- **반응형 처리**: 모바일 환경에서의 버튼 레이아웃 조정

### CSS 스타일
- **카드 디자인**: 그림자, 둥근 모서리, 테두리로 구분
- **폼 컨트롤**: 읽기 전용/활성 상태에 따른 시각적 차별화
- **버튼 스타일**: 일관된 색상 체계와 호버 효과
- **반응형 미디어 쿼리**: 모바일 최적화

## 📋 데이터 구조

### Company Profile Data
```php
$companyData = [
    'company_name' => 'PHITSOL Technologies Inc.',
    'company_address' => '123 Business Street, Makati City, Philippines',
    'date_of_incorporation' => '2020-01-15',
    'tin_number' => '123-456-789-000',
    'business_permit' => 'BP-2024-001234',
    'email_address' => 'info@phitsol.com',
    'contact_number' => '+63 2 1234 5678',
    'website_url' => 'https://www.phitsol.com'
];
```

### Contact Persons Data
```php
$contactData = [
    'authorized_representative' => 'John Doe',
    'position_title' => 'Chief Executive Officer',
    'representative_contact' => '+63 917 123 4567',
    'representative_email' => 'john.doe@phitsol.com',
    'secondary_contact_name' => 'Jane Smith',
    'secondary_contact_position' => 'Chief Operating Officer',
    'secondary_contact_number' => '+63 917 987 6543',
    'secondary_contact_email' => 'jane.smith@phitsol.com'
];
```

### Documents Data
```php
$documents = [
    'company_profile' => true,
    'business_permit' => true,
    'bir_2303' => true,
    'gis' => true,
    'audited_financial' => true,
    'proof_of_payment' => true,
    'valid_id' => true,
    'corporate_secretary' => true,
    'credit_investigation' => false,
    'peza_certification' => false
];
```

## 🚀 향후 개선 계획

### 1. 데이터베이스 연동
- 회사 정보를 데이터베이스에 저장하고 관리
- 실시간 데이터 동기화 및 백업

### 2. 파일 업로드 기능
- 필수 문서의 실제 파일 업로드 및 관리
- 파일 검증 및 보안 처리

### 3. 버전 관리
- 회사 정보 변경 이력 추적
- 변경 사항에 대한 승인 워크플로우

### 4. 알림 시스템
- 문서 만료일 알림
- 필수 문서 누락 시 경고 메시지

### 5. 내보내기 기능
- 회사 정보 PDF 보고서 생성
- Excel 형식으로 데이터 내보내기

## 📝 변경 이력

### v1.2.3 (2024-01-XX)
- ✅ Partners Portal Support 페이지 레이아웃 변경 - Send New Message 폼을 상단에 배치

### v1.2.2 (2024-01-XX)
- ✅ Partners Portal Support 페이지 게시판 형식 구현 - 메시지 목록 및 admin 답변 표시 기능 추가
- ✅ 게시판 스타일 UI 구현 - 메시지 헤더, 상태 배지, 답변 구분 표시
- ✅ 반응형 디자인 적용 - 모바일에서도 적절히 표시

### v1.2.1 (2024-01-XX)
- ✅ MongoDB BSONDocument 처리 오류 수정 - BSONDocument를 배열로 변환하는 로직 추가

### v1.2.0 (2024-01-XX)
- ✅ Partners Portal에 Company Profile 메뉴 추가
- ✅ Company Profile 페이지 생성 (public/company-profile.php)
- ✅ 모든 Partners Portal 페이지에 Company Profile 메뉴 통합
- ✅ Partners Dashboard에 Company Profile 요약 카드 추가
- ✅ 회사 정보 및 문서 상태를 읽기 전용으로 표시

### v1.1.5 (2024-01-XX)
- ✅ Required Documents List 체크박스 저장 기능 구현 - AJAX를 통한 서버 저장 기능 추가
- ✅ UserController에 updateUserDocuments 메서드 추가
- ✅ User 모델에 updateUserDocuments 및 getBusinessCount 메서드 추가

### v1.1.4 (2024-01-XX)
- ✅ Required Documents List 체크박스 기본값 변경 - 기본적으로 체크되지 않도록 설정

### v1.1.3 (2024-01-XX)
- ✅ View Profile 버튼 제거 - Actions 열에서 View Profile 버튼 삭제

### v1.1.2 (2024-01-XX)
- ✅ Update Company Profile 버튼 클릭 시 새로운 회사 편집 페이지로 이동 기능 구현
- ✅ 회사 프로필 편집 페이지 생성 (company.php)
- ✅ 회사 목록 페이지를 company-detail.php로 분리
- ✅ 라우팅 시스템 업데이트 (method=edit 파라미터 지원)

### v1.1.1 (2024-01-XX)
- ✅ Actions 버튼 텍스트 변경: "Update Profile" → "Update Company Profile"

### v1.1.0 (2024-01-XX)
- ✅ Business User 목록 표시 기능 구현
- ✅ 회사별 View Profile 및 Update Company Profile 버튼 추가
- ✅ 회사 아바타 및 상태 배지 디자인 구현
- ✅ 반응형 테이블 레이아웃 구현
- ✅ User Model에 getBusinessUsers() 메서드 추가

### v1.0.0 (2024-01-XX)
- ✅ Company Management 기능 초기 구현
- ✅ Company Profile 섹션 추가
- ✅ Authorized Contact Persons 섹션 추가
- ✅ Required Documents List 섹션 추가
- ✅ 반응형 디자인 구현
- ✅ 토스트 알림 시스템 구현 