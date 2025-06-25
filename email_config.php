<?php
require_once 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer; 


define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 465);
define('EMAIL_USERNAME', 'dariopp432@gmail.com');
define('EMAIL_PASSWORD', 'duyl wkzv bvnb gftj'); 
define('EMAIL_SMTPSecure', PHPMailer::ENCRYPTION_SMTPS); 
define('EMAIL_FROM', 'dariopp432@gmail.com');
define('EMAIL_FROM_NAME', 'HuinAgro');


define('EMAIL_DEBUG', true); 
define('EMAIL_LOG_DIR', 'logs/emails');


if (!is_dir(EMAIL_LOG_DIR)) {
    mkdir(EMAIL_LOG_DIR, 0777, true);
}
?>