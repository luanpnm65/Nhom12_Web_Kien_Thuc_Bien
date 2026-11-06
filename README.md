# OceanQuiz

Website học kiến thức sinh vật biển bằng Laravel 12, PHP 8.2, Breeze, Blade và MySQL/MariaDB. Phát triển theo vai A/B/C; mỗi chức năng có commit riêng trên `main`.

## Cài đặt local

Yêu cầu PHP 8.2+, Composer, Node.js/npm và MySQL/MariaDB (XAMPP). PHP cần PDO MySQL, mbstring, openssl, curl, fileinfo; SQLite không bắt buộc.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

Tạo database `oceanquiz1` dùng `utf8mb4`, sửa `DB_*` trong `.env` cho máy của bạn. Khởi động MySQL trong XAMPP trước khi migrate.

```powershell
php artisan migrate --seed
php artisan serve
```

Mở http://127.0.0.1:8001.


## Cấu hình tích hợp

Đặt `GEMINI_API_KEY`, `GEMINI_MODEL` và `WIKI_USER_AGENT` trong `.env`. Không commit `.env` hoặc API key. Khi chưa có key Gemini, chức năng nhập thủ công vẫn hoạt động.

```dotenv
GEMINI_API_KEY=your-own-key
GEMINI_MODEL=gemini-3.5-flash-lite
WIKI_USER_AGENT="OceanQuiz/1.0"
```

Model là cấu hình thay đổi được: chọn model Gemini có quyền truy cập trong tài khoản của bạn và hỗ trợ structured output. Sau khi sửa `.env`, chạy `php artisan config:clear`; nếu cần khởi động lại server local. Khóa được gửi trong header, không đưa vào URL hoặc frontend.

Nếu đã dán key nhưng web vẫn báo chưa cấu hình trong khi CLI nhận được key, hãy kiểm tra cách chạy server. `php artisan serve --no-reload` truyền giá trị môi trường lúc khởi động cho tiến trình web và không nạp lại khi `.env` đổi. Dừng server bằng Ctrl+C rồi chạy lại `php artisan serve --host=127.0.0.1 --port=8001` (không thêm `--no-reload`). Sau đó tải lại trang admin Gemini. Chỉ chạy `config:clear` trong CLI có thể chưa đủ với tiến trình web cũ.

- Wikipedia: vào **Quản trị → Wikipedia**, tìm từ khóa → xem trước → lưu bài. Server gọi API cố định của Wikipedia tiếng Việt, lưu văn bản và liên kết nguồn/giấy phép; bài trùng page ID được cập nhật nếu chưa có lượt làm.
- Gemini: vào **Gemini & nhật ký**, chọn bài và tên bộ → sinh đúng 5 câu nháp. Chỉ dùng tối đa 12.000 ký tự đầu bài. Server kiểm tra toàn bộ JSON trước khi lưu trong transaction; một câu sai thì không lưu bộ/câu nào.
- Nhật ký ghi thời gian, model, người thao tác, trạng thái, lỗi và phản hồi văn bản đã giới hạn độ dài. Chỉ admin xem được; không ghi API key.
- Khi API lỗi hoặc không có khóa, vẫn có thể nhập bài và tạo câu thủ công. Không tự xuất bản nội dung AI.
- Mặc định dùng `gemini-3.5-flash-lite` cho tác vụ quiz. Đã bấm trực tiếp trên web với bài Minecraft và tạo đủ 5 câu trong khoảng 30 giây. `gemini-3.8-flash` vẫn có thể cấu hình, nhưng đã gặp timeout 90 giây trong đợt thử này.
- Lỗi API hiển thị mã HTTP và mã trạng thái của Google để phân biệt sai dữ liệu, thiếu quyền, model không tồn tại, hạn mức và dịch vụ tạm bận. Chi tiết được giới hạn độ dài và che key trước khi hiển thị hoặc ghi nhật ký.
- Với Gemini 3, quiz sử dụng mức suy luận `low`; thời gian chờ tối đa 90 giây. Cấu hình này giúp tránh chờ quá lâu cho tác vụ 5 câu, nhưng tốc độ thực tế vẫn phụ thuộc API.
- Thao tác sinh câu hỏi nâng giới hạn thực thi PHP tối thiểu lên 120 giây khi cần, để yêu cầu API 90 giây còn đủ thời gian ghi kết quả hoặc nhật ký lỗi. Trên server phát triển PHP một tiến trình, yêu cầu trang khác có thể phải chờ trong lúc Gemini đang chạy.

Tài liệu kỹ thuật: [MediaWiki Search](https://www.mediawiki.org/wiki/API:Search), [TextExtracts](https://www.mediawiki.org/wiki/Extension:TextExtracts#API), [User-Agent](https://www.mediawiki.org/wiki/Wikimedia_APIs/Access_policy), [Gemini structured output](https://ai.google.dev/gemini-api/docs/generate-content/structured-output?hl=en).

## Tài khoản và dữ liệu demo

Seeder tài khoản chỉ chạy ở môi trường `local` hoặc `testing`.

| Vai trò | Email | Mật khẩu local |
|---|---|---|
| Admin | `admin@oceanquiz.local` | `OceanQuiz@123` |
| User | `user@oceanquiz.local` | `OceanQuiz@123` |
| User 2 | `user2@oceanquiz.local` | `OceanQuiz@123` |

Có 3 bài và 3 bộ đã xuất bản về cá voi xanh, rùa biển, san hô; mỗi bộ 5 câu. Có thêm 1 bộ nháp dùng để kiểm tra quyền truy cập. Seed lại không ghi đè bài/bộ đã tồn tại hoặc kết quả. Dữ liệu từ thao tác demo trên từng máy không được đưa lên GitHub; máy mới tái tạo dữ liệu bằng seeder.

Nội dung được diễn giải từ [NOAA Blue Whale](https://www.fisheries.noaa.gov/species/blue-whale), [NOAA Sea Turtles](https://www.fisheries.noaa.gov/feature-story/what-can-you-do-help-sea-turtles), [NOAA Coral](https://oceanexplorer.noaa.gov/ocean-fact/coral-animal/). Minh họa SVG do dự án tạo, không cần dịch vụ ảnh bên ngoài.

## Quy tắc nghiệp vụ

- Mỗi bộ có 5 câu, mỗi câu 4 lựa chọn và 1 đáp án đúng. Đúng 10 điểm, sai/bỏ trống 0; tối đa 50.
- Xếp hạng theo bộ: điểm cao nhất mỗi người; cùng mức điểm chọn lượt hoàn thành sớm nhất; sắp theo điểm giảm dần, thời điểm tăng dần, user ID tăng dần. Hạng liên tiếp, không đồng hạng.
- Thời gian lưu UTC, hiển thị theo giờ Việt Nam.

## Luồng sử dụng

**Người dùng:** đăng nhập → thư viện → đọc bài → bắt đầu → chọn đáp án → nộp → xem kết quả. Lịch sử chỉ gồm lượt completed của chính mình, phân trang 10 dòng. Làm lại tạo lượt mới, không ghi đè kết quả cũ. Lựa chọn chưa nộp chưa được lưu khi reload.

**Admin:** thêm bài → tạo bộ nháp gắn với một bài → thêm đủ 5 câu (4 lựa chọn/câu, 1 đáp án đúng) → kiểm tra → xuất bản. Có thể dùng Wikipedia và Gemini để hỗ trợ hai bước đầu. Bài gắn với bộ có lượt làm và bộ đã có lượt làm được khóa sửa/xóa. Muốn đổi nội dung hãy tạo bài/bộ mới.

Gỡ xuất bản chỉ ngừng nhận lượt mới. Chủ tài khoản vẫn tiếp tục lượt đã bắt đầu và xem kết quả cũ; bảng xếp hạng công khai chỉ áp dụng cho bộ published. Bài gắn với bộ nào cũng không được xóa, tránh xóa dây chuyền. Xóa tài khoản bị vô hiệu hóa để bảo toàn lịch sử.

**Xếp hạng:** truy vấn trực tiếp `attempts`, không lưu bảng xếp hạng riêng. Với từng user/bộ, loại lượt có một lượt khác tốt hơn (điểm cao hơn, hoặc cùng điểm nhưng sớm hơn, hoặc cùng điểm/giờ nhưng ID nhỏ hơn). Sắp các lượt đại diện theo điểm giảm dần, thời điểm tăng dần, user ID tăng dần; phân trang 10 người và tiếp nối hạng.

Ví dụ An đạt 30 điểm lúc 08:00, 50 điểm lúc 09:00, 50 điểm lúc 10:00 thì chọn 50 điểm lúc 09:00. Bình đạt 50 điểm lúc 09:10 đứng sau An. Không ghép `MAX(total_score)` với `MIN(finished_at)` của các lượt khác nhau.

## Cấu trúc để học và phát triển

| Phần | Vị trí |
|---|---|
| Đăng nhập/phân quyền | `app/Http/Controllers/Auth`, `app/Http/Middleware/EnsureUserIsAdmin.php` |
| Thư viện/đọc | `LibraryController`, `resources/views/library.blade.php`, `read.blade.php` |
| Quiz/kết quả/lịch sử | `QuizController`, `HistoryController`, `routes/quiz.php` |
| Biên soạn | `app/Http/Controllers/Admin/Question*`, `routes/admin-question-sets.php` |
| API ngoài | `app/Services/WikipediaService.php`, `GeminiService.php` |
| Xếp hạng | `LeaderboardController`, `routes/leaderboard.php` |
| Database | `database/migrations`, `app/Models`, `database/seeders` |
| Layout/style | `resources/views/layouts/ocean.blade.php`, `resources/css/ocean.css` |
| Kiểm thử nghiệp vụ | `tests/Feature`, `tests/CreatesQuiz.php` |

7 bảng nghiệp vụ cơ bản: users, articles, question_sets, questions, question_options, attempts, attempt_answers. Bổ sung ai_generation_logs cho Gemini; còn các bảng hạ tầng mặc định Laravel. Khóa dòng trong transaction bảo vệ thao tác bắt đầu/nộp và sửa nội dung đồng thời.

## Lệnh kiểm tra và khắc phục local

```powershell
php artisan migrate:status
php artisan test --compact
npm run build
php artisan view:cache
php artisan view:clear
composer validate --strict
```

- Lỗi connection refused: khởi động MySQL XAMPP và kiểm tra `DB_HOST`, `DB_PORT`.
- Chưa có bảng: tạo database rồi chạy `php artisan migrate --seed`.
- Không có giao diện/Vite manifest: chạy `npm install` và `npm run build`.
- Mail quên mật khẩu ở local dùng `MAIL_MAILER=log`: xem `storage/logs/laravel.log`, chưa cấu hình SMTP thật.
- Wikimedia có thể tạm bận hoặc giới hạn truy cập: thử lại sau, kiểm tra User-Agent hoặc dùng nhập thủ công. Không tắt kiểm tra TLS để vượt lỗi chứng chỉ.
- Gemini báo 429: kiểm tra hạn mức tài khoản; lỗi quyền/model: kiểm tra cấu hình và quyền API.
- Không chạy `migrate:fresh` trên database có dữ liệu cần giữ.

Chi tiết nghiệm thu, giới hạn kiểm chứng và kịch bản demo nằm trong [docs/kiem-thu-va-ban-giao.md](docs/kiem-thu-va-ban-giao.md).

## Kết quả kiểm tra bản bàn giao

- `php artisan test --compact`: **52 tests passed, 227 assertions** trên MariaDB.
- `npm run build`, `php artisan view:cache` và `composer validate --strict`: thành công.
- Đã kiểm tra trình duyệt luồng user (50/50, lịch sử, xếp hạng), admin, Wikipedia thật và responsive 1280/375 px.
- Đã thử start/submit đồng thời bằng hai tiến trình trên database riêng: 1 lượt, 5 câu trả lời, 50 điểm.
- Ngày 03/11/2026 đã gọi thật Gemini thành công với `gemini-3.8-flash`: HTTP 200, finishReason STOP, khoảng 55 giây; bộ nháp có 5 câu, 20 lựa chọn, mỗi câu 1 đáp án đúng và có giải thích. Bản thử local tên “Gemini kiểm tra key mới — Cá voi xanh”, không tự xuất bản. Các kiểm thử lỗi vẫn dùng HTTP giả lập.

Xem [phân chia công việc.md](phân%20chia%20công%20việc.md) để biết kế hoạch chi tiết.
