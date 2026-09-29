<?php
$sub_menu = '100000';
require_once './_common.php';

@require_once './safe_check.php';

// 관리자 메인 페이지를 기본 환경설정으로 리다이렉트
goto_url('./config_form.php');
