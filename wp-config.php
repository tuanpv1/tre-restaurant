<?php
/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung 
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt, 
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define('DB_NAME', 'tre');

/** Username của database */
define('DB_USER', 'root');

/** Mật khẩu của database */
define('DB_PASSWORD', '');

/** Hostname của database */
define('DB_HOST', 'localhost');

/** Database charset sử dụng để tạo bảng database. */
define('DB_CHARSET', 'utf8mb4');

/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '$7)o>,]V!lLE%:[9/a7l-isP::nsQ[y+N=x_lgGjDU&[+u(%u3!jY/_( @rV2XGU');
define('SECURE_AUTH_KEY',  'S}%gxXMcI3Iq:J.?[#p#D,rPUKaQaV,/lhK!kNe7H=vlBq[$|*`$-b4S LWV.Q?j');
define('LOGGED_IN_KEY',    '5Tb&[=;DxB!F;q0_4= /Ow04xf*Sa9SrE5.0@`df4z4-,Z=jPfkcE|4Lu;wRS1J!');
define('NONCE_KEY',        '_Jk@P4X6.o0F`&1GyJ.%l!4!2v,w8VL#7+i1e sU~&C%b@&dm=P~|qSE5a$=NXd}');
define('AUTH_SALT',        '?ps;-1;4bM1ju6UlUl$H(]%H5<GP75}9kF`+)_%Q#:pm7L EKNx@]IsKG423q`vE');
define('SECURE_AUTH_SALT', 'frmP.J L/pdUFg(Kh26p5VYj4ec`q]FF`% M0B8DQDcIYk&t>t}<t;|X%^0}L;t9');
define('LOGGED_IN_SALT',   'CNtWp5m$b:vH}0&XhF;n~qk|=d J%[y~}cU.BO(9?1N|[r#{d(Ym0bOE-YEM~<Zk');
define('NONCE_SALT',       'O9Q-Ps }c/dyiO }x={t/S4tz.wZNiI(BH>]PxSl*8j&8Wz=J. Vk82FeHSycey$');

/**#@-*/

/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix  = 'wp_';

/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');
