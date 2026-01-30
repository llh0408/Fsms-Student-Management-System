## FHC Management System

Truy cập trực tiếp tại: http://fsms.great-site.net/

FHC Management System là một hệ thống quản lí lớp học dành cho một lớp học từ nhỏ, vừa đến lớn. Hệ thống gồm có 2 role là giáo viên, sinh viên.
### Tech stack
*    PHP 8.5.1 (MVC)
*    Nginx
*    mySQL 8.0
### Các thông tin của user
*    Tên đăng nhập
*    Mật khẩu
*    Họ tên
*    Email
*    Số điện thoại
### Chức năng chính
1. **Quản lý thông tin:**
+ Giáo viên có thể thêm, sửa, xóa các thông tin của các sinh viên và của chính mình.
+ Sinh viên có thể thêm, sửa thông tin của chính mình trừ tên đăng nhập và họ tên.
+ 1 người dùng bất kỳ được phép xem danh sách các người dùng trên website và xem thông tin chi tiết của 1 người dùng khác. (ngoại từ tên đăng nhập, mật khẩu)

2. **Chức năng giao bài, trả bài:**
+ Giáo viên có thể upload file bài tập lên. Các sinh viên có thể xem danh sách bài tập và tải file bài tập về.
+ Sinh viên có thể upload bài làm tương ứng với bài tập được giao. Chỉ giáo viên mới nhìn thấy danh sách bài làm này.

3. **Chức năng cho phép giáo viên tổ chức 1 trò chơi giải đố:**
+ Giáo viên tạo challenge, trong đó cần thực hiện: upload lên 1 file txt có nội dung là 1 bài thơ, văn,.. Tên file được viết dưới định dạng không dấu và các từ cách nhau bởi 1 khoảng trắng. Sau đó nhập gợi ý về quyển sách và submit. (Đáp ấn chính là tên file mà giáo viên upload lên. Đáp án không lưu ra file, DB,...).
+ Sinh viên xem gợi ý và nhập đáp án. Khi sinh viên nhập đúng thì trả về nội dụng bài thơ, văn,.. lưu trong file đáp án.

