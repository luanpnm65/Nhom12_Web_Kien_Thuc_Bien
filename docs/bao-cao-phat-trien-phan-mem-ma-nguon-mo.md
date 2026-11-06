# BÁO CÁO PHÂN TÍCH DỰ ÁN MÃ NGUỒN MỞ
## OceanQuiz — Web Quiz Kiến Thức Biển (Nhóm 12)

| Thông tin | Giá trị |
|---|---|
| Môn học | Phát triển phần mềm mã nguồn mở |
| Dự án | `luanpnm65/Nhom12_Web_Kien_Thuc_Bien` (thương hiệu hiển thị: **OceanQuiz**) |
| Nền tảng phân tích | Nhánh `main`, commit `8d275e8` (sau khi hợp nhất PR #9), ngày 06/11/2026 |
| Phương pháp | Đọc trực tiếp mã nguồn, cấu hình, tài liệu trong repo; đối chiếu số liệu lịch sử qua GitHub REST API (do bản sao làm việc là shallow clone) |

---

## 1. Tổng quan dự án (Project Overview)

### 1.1 Tên dự án và mục đích

**OceanQuiz** (tên cũ trên giao diện: *AbyssalMind*) là một ứng dụng web trắc nghiệm kiến thức đại dương, được xây dựng trên nền tảng Laravel. Dự án phục vụ hai mục đích song song:

1. **Mục đích học tập (phía người dùng):** tổ chức việc học kiến thức biển thành một quy trình hai bước có kiểm soát thời gian: (i) đọc hiểu một tư liệu khoa học trong 3 phút, sau đó (ii) trả lời 5 câu hỏi trắc nghiệm theo phong cách Kahoot, trong đó điểm số phụ thuộc vào độ chính xác **và** tốc độ phản hồi.
2. **Mục đích vận hành nội dung (phía quản trị):** cung cấp một quy trình sản xuất bộ đề có kiểm chứng, gồm lấy bài viết từ Wikipedia tiếng Việt, sinh câu hỏi nháp bằng Google Gemini AI, biên tập/duyệt trước khi xuất bản.

### 1.2 Vấn đề thực tế mà dự án giải quyết

- **Thiếu công cụ học chủ động bằng tiếng Việt về kiến thức biển.** Tài liệu tiếng Việt về hải dương học phần lớn ở dạng bài viết dài, không có cơ chế kiểm tra mức độ hiểu sau khi đọc.
- **Nội dung phân tán và khó kiểm chứng.** Dự án giải quyết bằng cách gắn mỗi bộ đề với một bài viết nguồn (URL, giấy phép, mã trang Wikipedia), đảm bảo khả năng truy vết nội dung.
- **Chi phí soạn nội dung cao.** Việc dùng mô hình ngôn ngữ lớn để sinh câu hỏi nháp, sau đó bắt buộc con người duyệt (draft → review → publish), là cách cân bằng giữa tốc độ và độ tin cậy.
- **Vấn đề toàn vẹn của bài thi tính giờ.** Đây là điểm kỹ thuật khó nhất: đồng hồ, thứ tự câu hỏi và việc ghi nhận đáp án phải nằm ở phía máy chủ để người học không thể làm lại, tua lại hoặc sửa điểm bằng cách tải lại trang. Dự án giải quyết bằng thực thể `QuizRun`, API trạng thái JSON và giao dịch có khoá (`lockForUpdate`).

### 1.3 Đối tượng sử dụng mục tiêu

| Nhóm | Đặc điểm | Nhu cầu chính |
|---|---|---|
| Học sinh, sinh viên | Quen thi trắc nghiệm trực tuyến, dùng điện thoại | Học nhanh, có điểm, có xếp hạng, có ôn lại câu sai |
| Giáo viên / người hướng dẫn | Cần nguồn học liệu có dẫn chứng | Bộ đề gắn bài đọc nguồn, có giải thích đáp án |
| Người quan tâm môi trường biển | Động lực tự thân | Nội dung hấp dẫn, tiến trình theo độ sâu |
| Quản trị viên nội dung | Kiêm nhiệm, không chuyên sâu kỹ thuật | Quy trình soạn đề nhanh, có nhật ký, có bước duyệt |

### 1.4 Giấy phép mã nguồn mở (Open Source License)

**Hiện trạng thực tế của repository:**

| Hạng mục | Kết quả kiểm tra |
|---|---|
| Tệp `LICENSE` ở thư mục gốc | **Không có** |
| Trường `license` trong `composer.json` | `"MIT"` (kế thừa từ skeleton `laravel/laravel`) |
| Metadata giấy phép trên GitHub | `licenseInfo = null` (GitHub không nhận diện được giấy phép) |
| Tệp `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, `SECURITY.md` | Không có |
| Giấy phép của mã nền (framework) | Laravel: MIT |

**Kết luận:** dự án **tuyên bố** giấy phép MIT trong `composer.json` nhưng **chưa thiết lập giấy phép ở cấp repository** (thiếu tệp văn bản pháp lý). Đây là điểm thiếu sót cần khắc phục, vì trường `license` trong `composer.json` chỉ có ý nghĩa khai báo cho trình quản lý gói, không thay thế được tệp giấy phép. Trong thực tế pháp lý, khi không có văn bản cấp phép đi kèm, người dùng bên ngoài **không có cơ sở rõ ràng** để sao chép, sửa hay phân phối lại mã nguồn, dù repository ở chế độ công khai.

**Nếu áp dụng MIT (theo khai báo hiện tại), các điều khoản chính là:**

- **Quyền được cấp:** sử dụng, sao chép, sửa đổi, hợp nhất, xuất bản, phân phối, cấp phép lại (sublicense) và bán các bản sao phần mềm; cho phép dùng trong sản phẩm thương mại và mã nguồn đóng.
- **Điều kiện bắt buộc:** phải giữ nguyên thông báo bản quyền và văn bản giấy phép trong **mọi bản sao hoặc phần quan trọng** của phần mềm.
- **Giới hạn trách nhiệm:** phần mềm được cung cấp "nguyên trạng" (as-is), không bảo hành, tác giả không chịu trách nhiệm cho bất kỳ thiệt hại nào.
- **Không có hiệu ứng copyleft:** không buộc dự án phái sinh phải mở mã nguồn (khác GPL); **không** có điều khoản cấp phép bằng phát minh (khác Apache 2.0); **không** cấp quyền sử dụng nhãn hiệu.

**Khuyến nghị:** bổ sung tệp `LICENSE` (nội dung MIT, giữ năm và chủ sở hữu quyền), khai báo SPDX `MIT` trong README, và ghi rõ giấy phép của tài nguyên đi kèm: ảnh minh hoạ trong `public/images/` do nhóm tự sinh bằng mô hình ảnh AI (không phụ thuộc ảnh bên thứ ba), font chữ dùng qua Google Fonts (Inter, JetBrains Mono, Merriweather — giấy phép SIL Open Font License) cần được nêu để người dùng lại biết điều kiện.

---

## 2. Kiến trúc hệ thống và Công nghệ sử dụng

### 2.1 Ngôn ngữ, framework và thư viện

| Thành phần | Công nghệ / phiên bản | Vai trò |
|---|---|---|
| Ngôn ngữ chính | PHP `^8.2` | Toàn bộ nghiệp vụ phía máy chủ |
| Framework | Laravel `^12.0` | Routing, ORM Eloquent, validation, session, scheduler, HTTP client |
| Template engine | Blade (22 tệp, ~1.060 dòng) | Kết xuất giao diện phía máy chủ |
| CSS | CSS thuần, biến thiết kế (2 tệp, ~2.075 dòng) | Hệ thống giao diện "đại dương sáng", responsive |
| JavaScript | Vanilla JS (4 tệp, ~196 dòng) | Polling trạng thái lượt thi, đếm ngược, tương tác Kahoot |
| Cơ sở dữ liệu | MySQL (XAMPP) cho chạy thật; SQLite in-memory cho test | Lưu người dùng, bộ đề, câu hỏi, lượt làm bài, lượt thi |
| Kiểm thử | PHPUnit `^11.5` | 31 phương thức test |
| Chất lượng mã | Laravel Pint `^1.24` | Chuẩn hoá code style PHP |
| Công cụ phụ | Laravel Tinker, Pail (log), Collision, Faker, Mockery, Sail | Debug, log, sinh dữ liệu giả |
| Frontend build | Vite `^7`, `laravel-vite-plugin` `^2`, Tailwind CSS `^4`, axios, concurrently | Có khai báo nhưng **chưa được dùng** trong view (không có `@vite`) |
| Tự động hoá môi trường | PowerShell (`scripts/XamppQuizScheduler.ps1`) | Đăng ký Windows Scheduled Task chạy scheduler Laravel trên XAMPP |

Số liệu định lượng của mã nguồn (không tính `vendor/`):

| Loại | Số tệp | Số dòng |
|---|---|---|
| PHP (không tính Blade) | 58 | 4.285 |
| Blade | 22 | 1.060 |
| CSS | 2 | 2.075 |
| JavaScript | 4 | 196 |
| Tổng tệp được Git theo dõi | 120 | — |
| Gói Composer (production / dev) | 77 / 35 | — |

Thống kê ngôn ngữ theo GitHub: PHP ~172 KB, Blade ~72 KB, CSS ~42 KB, JavaScript ~8 KB, PowerShell ~2,5 KB.

### 2.2 Mô hình kiến trúc

Dự án là **monolith theo mô hình MVC của Laravel, có bổ sung một lớp Service** (không phải Clean Architecture đầy đủ, không microservices).

```
HTTP Request
   │
   ├─ routes/web.php + routes/admin-*.php        (khai báo endpoint, middleware auth/admin)
   │
   ├─ Middleware: auth, AdminMiddleware          (phân quyền theo cột users.role)
   │
   ├─ Controller (6 controller + 4 controller Admin)
   │     ├─ validate() dữ liệu đầu vào
   │     └─ điều phối nghiệp vụ
   │
   ├─ Service layer (3 lớp)
   │     ├─ TimedQuizRuns   : máy trạng thái của lượt thi tính giờ, tính điểm, khoá giao dịch
   │     ├─ GeminiService   : gọi Google Gemini, kiểm tra và chuẩn hoá JSON 5 câu hỏi
   │     └─ WikipediaService: gọi MediaWiki API (tìm kiếm, lấy nội dung bài viết)
   │
   ├─ Eloquent Model (8 model) ⇄ MySQL/SQLite
   │
   └─ Blade View (+ public/css, public/js)
```

Các quyết định kiến trúc đáng chú ý:

1. **Máy chủ là nguồn sự thật duy nhất về thời gian.** `QuizRun` lưu `phase`, `deadline_ms`, `answers`; mọi thao tác mở lượt, trả lời, kết thúc đều chạy trong `DB::transaction` và `lockForUpdate()` trên bản ghi người dùng/lượt thi. Nhờ đó, việc tải lại trang hay gửi hai đáp án đồng thời không làm thay đổi kết quả.
2. **Tách lớp Service cho nghiệp vụ có tính trạng thái.** `TimedQuizRuns` là nơi duy nhất biết cách "trôi" thời gian (đọc 180 giây, phản hồi 2,2 giây/câu), nhờ vậy controller mỏng và test hành vi dễ hơn.
3. **Scheduler thay cho hàng đợi nặng.** `routes/console.php` khai báo lệnh `quiz:advance-runs` chạy mỗi 5 giây với `withoutOverlapping(1)` để chốt các lượt thi bị bỏ dở — phù hợp với môi trường XAMPP không có worker thường trực.
4. **Gọi dịch vụ ngoài có timeout và xử lý lỗi tường minh.** `WikipediaService` dùng `connectTimeout(8)`, `timeout(20)`; `GeminiService` dùng `connectTimeout(10)`, `timeout(90)`, ánh xạ mã lỗi HTTP sang thông báo tiếng Việt và che khoá API (`[REDACTED]`) trước khi ghi log.
5. **Điểm hạn chế kiến trúc:** tổ chức route theo "thành viên phụ trách" (`routes/web.php` chia khối bằng chú thích Thành viên 1/2/3) thay vì theo miền nghiệp vụ; chưa có lớp Repository/DTO; phần lớn truy vấn viết trực tiếp trong controller.

### 2.3 Cấu trúc thư mục

```
Nhom12_Web_Kien_Thuc_Bien/
├── app/
│   ├── Http/Controllers/           # 6 controller người dùng: Home, Auth, Dashboard, Quiz, Admin, Controller gốc
│   │   └── Admin/                  # 4 controller quản trị: Article, Wikipedia, AiGeneration, QuestionSet
│   ├── Http/Middleware/            # AdminMiddleware: chặn người dùng thường khỏi khu vực quản trị
│   ├── Models/                     # 8 model: User, Article, QuizSet, Question, QuizRun, QuizAttempt, QuizAttemptAnswer, AiGenerationLog
│   ├── Services/                   # 3 lớp nghiệp vụ: TimedQuizRuns, GeminiService, WikipediaService
│   └── Providers/                  # Đăng ký service provider của Laravel
├── bootstrap/                      # Khởi tạo ứng dụng (Application::configure), cache khung
├── config/                         # 11 tệp cấu hình, gồm config/http.php (CA bundle tuỳ biến)
├── database/
│   ├── migrations/                 # 7 migration: users/cache/jobs + bảng quiz + sinh câu hỏi + điểm xếp hạng + quiz_runs
│   ├── seeders/                    # DatabaseSeeder (tài khoản demo + 2 bộ đề) và PaginationDemoSeeder
│   └── factories/                  # UserFactory
├── public/
│   ├── css/ocean-app.css           # Toàn bộ hệ thống giao diện
│   ├── js/quiz-play.js             # Logic phía trình duyệt cho lượt thi
│   └── images/                     # Ảnh hero + 5 ảnh minh hoạ 5 tầng đại dương (tự sinh)
├── resources/
│   ├── views/                      # 22 tệp Blade: layouts, auth, dashboard, quiz, leaderboard, admin, pagination
│   ├── css/, js/                   # Pipeline Vite/Tailwind mặc định (hiện chưa dùng trong view)
│   └── certs/cacert.pem            # CA bundle kèm dự án cho các máy thiếu chứng chỉ HTTPS
├── routes/                         # web.php, admin-articles.php, admin-question-sets.php, console.php
├── scripts/XamppQuizScheduler.ps1  # Tự động hoá chạy scheduler trên Windows/XAMPP
├── tests/                          # 4 bộ Feature thực chất + 2 Example test, TestCase gốc
├── composer.json / composer.lock   # Khai báo phụ thuộc PHP
├── package.json / vite.config.js   # Khai báo phụ thuộc frontend
├── phpunit.xml                     # Cấu hình test (SQLite in-memory, array cache/session)
├── .editorconfig / .gitattributes / .gitignore
└── README.md
```

**Nhận xét:** cấu trúc tuân theo chuẩn của Laravel nên dễ tiếp cận với người đã biết framework; phần mở rộng của nhóm (Services, Admin namespace, scripts, docs) nằm đúng chỗ. Điểm chưa hợp lý: `public/css` và `public/js` chứa mã nguồn chính nhưng lại không đi qua pipeline build, trong khi `resources/css|js` + Vite/Tailwind vẫn tồn tại dưới dạng cấu hình "chết".

---

## 3. Quy trình phát triển và Quản lý mã nguồn

### 3.1 Mô hình nhánh

Dự án vận hành theo **GitHub Flow** ở dạng rút gọn: chỉ một nhánh dài hạn `main` tồn tại trên remote; mọi thay đổi đi qua nhánh tính năng ngắn hạn rồi hợp nhất bằng Pull Request, sau đó nhánh bị xoá. Không có `develop`, không có `release/*` như Git Flow.

```
feature/quiz ─┐
Nam/tich-hop-trung-tam-ai ─┼─► Pull Request ─► main ─► (xoá nhánh)
timkiem-lichsu-phantrang ─┘
```

Số liệu (từ GitHub API):

| Chỉ số | Giá trị |
|---|---|
| Tổng số commit trên `main` | 21 |
| Khoảng thời gian | 30/10/2026 → 06/11/2026 |
| Người đóng góp | 3 (`luanpnm65`: 17, `VOVANNAM2812`: 3, `LeTuanKiet2307`: 1) |
| Pull Request | 9 (4 đã hợp nhất, 4 đóng không hợp nhất, 1 đang mở) |
| Issue | 0 |
| Release / tag | 0 |
| Nhánh trên remote | 1 (`main`) |
| Mức độ cộng đồng | Công khai · 3 sao · 2 lượt fork |

| PR | Tác giả | Trạng thái | Quy mô |
|---|---|---|---|
| #1 Thêm phần Quiz: controller, giao diện, ôn tập câu sai | LeTuanKiet2307 | Đã hợp nhất | +504 / −0, 3 tệp |
| #3 Tích hợp Trung tâm AI, Wikipedia và Gemini | VOVANNAM2812 | Đã hợp nhất | +4.002 / −2, 29 tệp |
| #7 Thêm phân trang, tính điểm và khôi phục lượt làm quiz | VOVANNAM2812 | Đã hợp nhất | +2.363 / −386, 38 tệp |
| #9 Thiết kế lại giao diện theo phong cách SeaGuard | luanpnm65 | Đã hợp nhất | +1.732 / −861, 24 tệp |
| #2, #4, #5, #8 | nhiều tác giả | Đóng (thay bằng PR khác) | — |
| #6 Sửa logic Quiz và hướng dẫn tích hợp database | LeTuanKiet2307 | Đang mở | +2.214 / −387, 14 tệp |

**Đánh giá:** việc dùng PR cho cả 3 thành viên là điểm tích cực, cho thấy thói quen review chéo thay vì đẩy trực tiếp lên `main`. Tuy nhiên quy ước đặt tên nhánh **không thống nhất**: `feature/quiz` (đúng chuẩn), `FixQuiz`, `FixQuizLogic`, `Nam/tich-hop-trung-tam-ai`, `timkiem-lichsu-phantrang` (không dấu, không tiền tố), và `arena/*` (nhánh do công cụ hỗ trợ sinh ra). Điều này làm lịch sử khó tra cứu khi số nhánh tăng.

### 3.2 Quản lý tác vụ, Issue Tracking và Pull Request

- **Issue Tracking:** không được sử dụng (0 issue). Việc phân công thay vào đó dựa trên chú thích ngay trong mã nguồn, ví dụ:
  - `routes/web.php`: *"[Thành viên 1 - Owner] Landing page, leaderboard & auth"*, *"[Thành viên 2 - Contributor A] Quiz workflow, Kahoot UI & review mistakes"*, *"[Thành viên 3 - Contributor B] Admin dashboard, Wikipedia API & Gemini AI"*.
  - `app/Http/Controllers/QuizController.php`: dòng đầu ghi `[Thành viên 2 - Contributor A]`.
  - Tiêu đề PR #2 có mã công việc `C01`.
- **Mẫu tài liệu cộng đồng:** không có `CONTRIBUTING.md`, `ISSUE_TEMPLATE/`, `PULL_REQUEST_TEMPLATE.md`, `CODE_OF_CONDUCT.md`. Nội dung PR do tác giả tự trình bày (PR #7 và #9 có mô tả chi tiết: danh sách thay đổi, ghi chú kiểm tra), nhưng không có chuẩn bắt buộc.
- **Commit message:** tiếng Việt, ngắn, mô tả mục đích ("Thêm phân trang và quản lý lượt làm quiz", "Bỏ bộ lọc Tầng đại dương ở trang chọn bài và lịch sử"). Không theo Conventional Commits; có commit trùng lặp nội dung ("Bổ sung" ×2, "Giao diện và controller" ×2) và commit không dấu ("Khoi tao du an") — dấu hiệu thiếu quy ước.
- **Mô hình tích hợp:** "merge commit" của GitHub (không squash), nên lịch sử vẫn giữ được ngữ cảnh nhánh.

### 3.3 CI/CD và tự động hoá

| Hạng mục | Trạng thái trong repo |
|---|---|
| GitHub Actions / Travis / CircleCI | **Không có** (không tồn tại thư mục `.github/`) |
| Kiểm tra tự động khi mở PR | Không có (không có status check) |
| Script Composer | `composer setup`, `composer dev`, `composer test` (test chạy `config:clear` rồi `artisan test`) |
| Script NPM | `npm run dev` (Vite), `npm run build` |
| Scheduler nội bộ | `Schedule::command('quiz:advance-runs')->everyFiveSeconds()->withoutOverlapping(1)` |
| Tự động hoá môi trường Windows | `scripts/XamppQuizScheduler.ps1 -Install`: tạo Scheduled Task tên **AbyssalMind Quiz Scheduler**, chờ MySQL sẵn sàng rồi chạy scheduler Laravel; có xử lý log, thoát khi thiếu PHP |
| Hạ tầng dev | `composer dev` chạy song song server, queue listener, scheduler, log (Pail) và Vite bằng `concurrently` |

Một chi tiết đáng chú ý: `.gitattributes` vẫn còn các dòng `/.github export-ignore`, `CHANGELOG.md export-ignore`, `.styleci.yml export-ignore` — dấu vết của skeleton Laravel, cho thấy các tệp này từng tồn tại trong mẫu nhưng **chưa được dự án tạo**. Tương tự, tên Scheduled Task vẫn là "AbyssalMind" sau khi giao diện đã đổi thành "OceanQuiz" — dấu hiệu đổi thương hiệu chưa được rà soát toàn bộ.

**Đánh giá:** dự án có tự động hoá *vận hành* (scheduler, script XAMPP, script Composer) nhưng **không có tự động hoá chất lượng** (CI). Với một dự án mã nguồn mở có 3 người đóng góp và 9 PR trong một tuần, việc thiếu CI là rủi ro trực tiếp: không ai kiểm tra tự động rằng PR không làm hỏng 31 bài test hiện có.

---

## 4. Chất lượng mã nguồn và Kiểm thử

### 4.1 Hiện trạng kiểm thử

- **Framework:** PHPUnit 11.5 (tích hợp sẵn qua `php artisan test`), cấu hình tại `phpunit.xml`.
- **Môi trường test:** `APP_ENV=testing`, DB SQLite `:memory:`, cache/session `array`, mail `array`, queue `sync` → test chạy độc lập, không cần MySQL.
- **Kỹ thuật sử dụng:** `RefreshDatabase` (làm mới schema mỗi test), `Http::fake()` / `Http::preventStrayRequests()` để giả lập Gemini và Wikipedia, `actingAs()` cho phân quyền, `assertViewHas()` để kiểm tra dữ liệu đưa ra view.

| Bộ test | Số test | Phạm vi |
|---|---|---|
| `QuestionGenerationTest` | 9 | Phân quyền admin; tìm/duyệt/lưu bài Wikipedia không trùng lặp; sinh 5 câu bằng Gemini; xử lý thiếu khoá, lỗi nhà cung cấp, JSON sai; soạn bộ thủ công; xuất bản/gỡ xuất bản; migration backfill; seed |
| `QuizBrowsingHistoryTest` | 12 | Yêu cầu đăng nhập; danh mục chỉ hiện bộ đề chơi được; tìm kiếm + trạng thái; phân trang 10 dòng và giữ bộ lọc; lịch sử riêng tư theo người dùng; thẻ lượt làm thu gọn mặc định; lịch sử 10 dòng/trang; xử lý rỗng |
| `TimedQuizRunTest` | 5 | Gia hạn deadline khi refresh; tự chuyển pha khi vắng mặt; chốt lượt bỏ dở đúng một lần qua scheduler; endpoint riêng tư; gỡ xuất bản không phá lượt đang chạy |
| `QuizRankingTest` | 3 | Điểm xếp hạng chỉ tính ở lượt đầu; chống gian lận nhờ làm lại; migration chỉ xoá điểm lặp |
| `ExampleTest` (Feature, Unit) | 2 | Kiểm tra khói do Laravel sinh sẵn |
| **Tổng** | **31** | ~845 dòng test |

**Nhận xét:** tỉ lệ 31 test cho ~4.300 dòng PHP là mức khá với một đồ án môn học, và điểm mạnh là test **hành vi nghiệp vụ** chứ không chỉ test "trang trả về 200": có test chống gian lận điểm, test tính riêng tư của lịch sử, test tính nhất quán của đồng hồ khi refresh. Tuy nhiên **không có unit test trực tiếp** cho `GeminiService`/`WikipediaService` (chỉ test gián tiếp qua feature), không có E2E/browser test (không dùng Laravel Dusk hoặc Playwright), và **không cấu hình đo coverage** (không `--coverage`, không Xdebug/PCOV trong CI) nên chưa có con số phần trăm bao phủ.

### 4.2 Tiêu chuẩn viết mã

| Hạng mục | Hiện trạng |
|---|---|
| Code style PHP | **Laravel Pint** (`^1.24`) có trong `require-dev`; chưa có `pint.json` → dùng preset `laravel` mặc định. Không có bước kiểm tra style tự động trong CI |
| Chuẩn autoload | PSR-4: `App\` → `app/`, `Tests\` → `tests/` (khai báo trong `composer.json`) |
| Chuẩn soạn thảo | `.editorconfig`: UTF-8, LF, indent 4 dấu cách, xoá khoảng trắng cuối dòng, newline cuối tệp |
| Quy ước Git | `.gitattributes` chuẩn hoá `text=auto eol=lf`, gán diff driver cho `.blade.php`, `.css`, `.md`, `.php` |
| Phân tích tĩnh | **Không có** PHPStan/Larastan/Psalm |
| Lint frontend | **Không có** ESLint/Prettier/Stylelint |
| Kiểm tra bảo mật tự động | Không có Dependabot/CodeQL/`composer audit` trong pipeline |
| Kiểm soát bí mật | `.gitignore` chặn `.env` và mọi `*.sql`, `*.sqlite`; `GeminiService` che khoá API trước khi ghi log; CA bundle riêng trong `config/http.php` |

**Điểm mạnh về chất lượng mã:** controller dùng `$request->validate()` thay vì đọc `input()` thô; nghiệp vụ phức tạp nằm trong Service có kiểu dữ liệu tường minh (`private const READING_MS = 180_000`); tên biến/hàm tiếng Anh rõ nghĩa; có comment giải thích quyết định (ví dụ: *"Use the same account lock for opening, answering and ranking a run"*).

**Điểm chưa đạt:** khoảng 1.900 dòng CSS dồn trong một tệp `ocean-app.css`; nhiều `style=""` nội tuyến trong Blade (khó bảo trì, khó theming); thông báo flash trong `AuthController` còn tiếng Anh ("Welcome aboard submarine explorer...") trong khi giao diện tiếng Việt — dấu hiệu thiếu quy trình i18n; dữ liệu seed chứa mật khẩu yếu `password123` cho 3 tài khoản demo (dù form đăng nhập không còn hiển thị gợi ý).

---

## 5. Đánh giá cá nhân và Bài học kinh nghiệm

### 5.1 Điểm mạnh

1. **Ý tưởng sản phẩm có chiều sâu sư phạm.** Mô hình "đọc 3 phút → phản xạ 5 câu" buộc người học xử lý văn bản trước khi trả lời, khác biệt với các quiz hỏi đáp thuần tuý.
2. **Thiết kế máy chủ làm trọng tài thời gian.** `QuizRun` + giao dịch có khoá là lời giải đúng cho bài toán thi tính giờ trên web, đồng thời tạo nền cho các test chống gian lận.
3. **Tách lớp Service hợp lý.** Ba lớp `TimedQuizRuns`, `GeminiService`, `WikipediaService` cô lập phần dễ thay đổi (mô hình AI, nguồn tri thức) khỏi controller.
4. **Quy trình nội dung có kiểm chứng.** AI chỉ sinh **nháp**; bắt buộc duyệt, có nhật ký `AiGenerationLog` lưu model, trạng thái, phản hồi, lỗi — thể hiện nhận thức về rủi ro của nội dung do AI sinh.
5. **Độ phủ test hành vi đáng khích lệ** với các ca biên: refresh không reset đồng hồ, hai đáp án đồng thời, gỡ xuất bản khi đang làm bài.
6. **Giao diện được đầu tư.** Hệ thống thiết kế riêng (bảng màu, thẻ kính, ảnh minh hoạ 5 tầng), responsive, có cải thiện khả năng truy cập (aria-label, focus-visible, `role="status"`).
7. **Văn hoá PR được duy trì** dù nhóm nhỏ, và có tài liệu vận hành thực dụng (hướng dẫn scheduler XAMPP bằng tiếng Việt).

### 5.2 Điểm yếu và hạn chế

| Nhóm | Hạn chế | Hệ quả |
|---|---|---|
| Pháp lý | Không có tệp `LICENSE`, GitHub không nhận diện giấy phép | Người ngoài không có cơ sở tái sử dụng; không đạt tiêu chí "open source" theo định nghĩa OSI ở cấp repository |
| Tài liệu | README gần như nguyên bản Laravel; không có hướng dẫn cài đặt/biến môi trường/kiến trúc | Người mới mất nhiều thời gian dựng môi trường; tri thức dự án nằm trong đầu thành viên |
| CI/CD | Không có pipeline | Không phát hiện hồi quy tự động; chất lượng phụ thuộc review thủ công |
| Cộng đồng | Không có CONTRIBUTING, issue/PR template, CODE_OF_CONDUCT, SECURITY | Rào cản đóng góp; không có kênh báo lỗi bảo mật |
| Bảo mật | Không giới hạn tần suất đăng nhập; seeder tạo tài khoản mật khẩu yếu; không có 2FA | Rủi ro brute-force, rủi ro lộ tài khoản demo nếu deploy nguyên trạng |
| Nhất quán sản phẩm | Flash message tiếng Anh; tên Scheduled Task còn "AbyssalMind"; tên nhánh/commit không quy ước | Trải nghiệm và lịch sử thiếu chuyên nghiệp |
| Kỹ thuật | CSS đơn khối ~1.900 dòng + nhiều inline style; Vite/Tailwind cấu hình nhưng không dùng; chưa có static analysis | Khó bảo trì, tăng thời gian build không cần thiết, dễ phát sinh lỗi kiểu dữ liệu |
| Kiểm thử | Thiếu unit test cho Services, thiếu E2E, không đo coverage | Vùng then chốt (gọi AI/Wikipedia) chỉ được kiểm tra gián tiếp |

### 5.3 Đề xuất cải tiến (theo mức ưu tiên)

| Ưu tiên | Đề xuất | Giá trị |
|---|---|---|
| **P0** | Thêm `LICENSE` (MIT) + SPDX trong README; ghi rõ giấy phép tài nguyên (font, ảnh AI) | Hợp thức hoá dự án mã nguồn mở |
| **P0** | `CONTRIBUTING.md` + `ISSUE_TEMPLATE` + `PULL_REQUEST_TEMPLATE` + `CODE_OF_CONDUCT` + `SECURITY.md` | Chuẩn hoá đóng góp, tạo kênh báo lỗi |
| **P0** | GitHub Actions: `composer test` + `pint --test` trên ma trận PHP 8.2/8.3, cache Composer | Chặn hồi quy tự động |
| **P1** | Giới hạn tần suất đăng nhập (`RateLimiter`, throttle 5 lần/phút), khoá tài khoản tạm thời | Chống brute-force |
| **P1** | Đưa dữ liệu demo ra sau cờ môi trường (`local` only), đổi sang mật khẩu ngẫu nhiên khi seed | An toàn khi triển khai thật |
| **P1** | i18n: chuyển toàn bộ chuỗi trong controller/view sang `lang/vi` và `lang/en` | Nhất quán ngôn ngữ, mở đường đa ngôn ngữ |
| **P1** | Bổ sung unit test cho `GeminiService::validateQuestions` và `WikipediaService` (fixture JSON, mã lỗi HTTP) | Tăng độ tin cậy ở vùng tích hợp ngoài |
| **P2** | Tách `ocean-app.css` thành các module (tokens, layout, components) hoặc chuyển sang Tailwind vốn đã khai báo | Bảo trì giao diện |
| **P2** | Thêm PHPStan/Larastan mức 5 + ESLint/Prettier cho JS/CSS | Phát hiện lỗi sớm |
| **P2** | Thêm E2E (Dusk/Playwright) cho luồng đọc → thi → xem lịch sử | Bao phủ luồng người dùng thật |
| **P2** | Docker Compose (MySQL + PHP) thay phụ thuộc XAMPP; release theo SemVer + `CHANGELOG.md` | Tái lập môi trường, phát hành có phiên bản |
| **P2** | Chuẩn hoá Conventional Commits + mẫu nhánh `feature/*`, `fix/*`; bật Dependabot | Lịch sử dễ đọc, cập nhật phụ thuộc an toàn |
| **P3** | Chỉ mục DB cho truy vấn xếp hạng, cache bảng xếp hạng, thêm trang quản trị nhật ký bảo mật | Hiệu năng và khả năng kiểm toán |

### 5.4 Bài học thực tế về tổ chức và vận hành dự án mã nguồn mở

1. **Giấy phép phải được khai báo bằng văn bản, không chỉ bằng metadata.** `composer.json` ghi MIT nhưng thiếu `LICENSE` khiến GitHub không nhận diện. Với dự án mở, tệp giấy phép là "giao diện pháp lý" đầu tiên mà người dùng thấy.
2. **CI là khoản đầu tư rẻ nhất trong một tuần làm việc nhóm.** Chỉ cần một workflow chạy `composer test` là đủ để phát hiện phần lớn hồi quy giữa 9 PR; thiếu nó, mọi PR phải review bằng mắt.
3. **Test hành vi có giá trị hơn test hình thức.** Các bài test về đồng hồ, gian lận điểm và quyền riêng tư lịch sử bảo vệ đúng phần khó nhất của sản phẩm — đây là cách viết test nên nhân rộng.
4. **Tài liệu môi trường là một phần của sản phẩm.** Script PowerShell cho XAMPP là ví dụ tốt về việc ghi lại "tri thức vận hành" vốn dễ mất khi nhóm tan; nhưng tài liệu đó nên nằm trong `docs/` với README liên kết tới.
5. **Quy ước nhỏ tạo khác biệt lớn.** Tên nhánh, thông điệp commit, ngôn ngữ thông báo, tên Scheduled Task — mỗi thứ nhỏ, nhưng cộng lại quyết định cảm nhận về mức độ chuyên nghiệp của dự án.
6. **Không để cấu hình "chết".** Vite/Tailwind được khai báo nhưng không dùng; `.gitattributes` nhắc tới `.github` không tồn tại. Trong dự án mở, cấu hình thừa gây hiểu nhầm cho người đóng góp mới.
7. **Thay đổi lớn cần được chia nhỏ và mô tả.** PR #7 (38 tệp) và #9 (24 tệp) chứng minh giá trị của mô tả PR chi tiết; ngược lại, 4 PR bị đóng cho thấy chi phí của việc mở PR quá lớn hoặc không đồng bộ với `main`.
8. **Mọi phụ thuộc bên ngoài đều là bề mặt rủi ro.** Việc dùng Wikipedia và Gemini đem lại giá trị lớn nhưng đòi hỏi timeout, che bí mật và nhật ký — dự án đã làm đúng ở mức cơ bản, nên tiếp tục với retry có backoff và giới hạn chi phí gọi API.

---

## Phụ lục: Bảng số liệu tổng hợp

| Chỉ số | Giá trị |
|---|---|
| Tổng tệp được Git theo dõi | 120 |
| PHP (không Blade) | 58 tệp / 4.285 dòng |
| Blade | 22 tệp / 1.060 dòng |
| CSS | 2 tệp / 2.075 dòng |
| JavaScript | 4 tệp / 196 dòng |
| Tệp Controller / Model / Service / Middleware | 10 / 8 / 3 / 1 |
| Controller nghiệp vụ (không kể controller gốc và Admin) | 5 |
| Migration / Seeder | 7 / 2 |
| Khai báo route | 32 dòng khai báo trong 3 tệp route (15 GET, 9 POST, 2 resource, 6 nhóm middleware) |
| Bài test (phương thức) | 31 |
| Gói Composer (prod / dev) | 77 / 35 |
| Commit / Người đóng góp / PR | 21 / 3 / 9 |
| Issue / Release | 0 / 0 |
| CI pipeline | 0 |
| Tệp LICENSE | 0 (khai báo MIT trong `composer.json`) |
| Giấy phép GitHub nhận diện | Không có (`licenseInfo = null`) |
