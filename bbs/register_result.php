<?php
include_once('./_common.php');

if (isset($_SESSION['ss_mb_reg']))
    $mb = get_member($_SESSION['ss_mb_reg']);

// 회원정보가 없다면 초기 페이지로 이동
if (!(isset($mb['mb_id']) && $mb['mb_id']))
    goto_url(G5_URL);

$g5['title'] = '회원가입 완료';
include_once('./_head.php');
?>

<style>
/* 회원가입 완료 */
#reg_result {padding:40px 30px;text-align:center;background:#edf3fc;border:1px solid #d6e2f4;border-radius:5px}
#reg_result h2 {font-size:2em;margin:0 0 20px}
#reg_result h2 strong {color:#ed6478}
#reg_result #result_email {margin:20px 0;padding:10px 50px;border-top:1px solid #e9e9e9;border-bottom:1px solid #dde4e9;background:#fff;line-height:2em}
#reg_result #result_email span {display:inline-block;width:150px}
#reg_result #result_email strong {color:#e8180c;font-size:1.2em}
#reg_result p {line-height:1.8em}
#reg_result .result_txt {text-align:left}
#reg_result .btn_confirm {margin:50px 0}
#reg_result i {font-size:3em}
#reg_result .reg_result_p {font-size:1.25em;margin:0 0 10px;color:#3684fa}

.btn_confirm_reg {margin:20px 0;text-align:center}
.reg_btn_submit {display:inline-block;background:#3a8afd;color:#fff;text-align:center;border-radius:3px;width:280px;height:57px;line-height:57px;font-size:1.2em;margin:0 auto;font-weight:bold}

.mb_log_cate h2 {width:50%;float:left;padding:20px 0;text-align:center}
.mb_log_cate .join {width:50%;float:left;padding:20px 0;text-align:center;background:#f7f7f7;color:#6e6e6e}
.mb_log_cate:after {display:block;visibility:hidden;clear:both;content:""}

.tooltip_icon {display:inline-block;vertical-align:baseline;color:#b3b5b8;border:0;font-size:1.4em;background:transparent;cursor:pointer}
.tooltip_icon:hover {color:#448bf5}
.tooltip {position:relative;width:auto;color:#fff;background:#000;padding:10px;font-size:small;line-height:18px;display:none;position:absolute;z-index:9;font-weight:normal;margin-left:15px;margin-top:10px}
.tooltip:before {content:"";position:absolute;top:0;left:-10px;width:0;height:0;border-style:solid;border-top:0px solid transparent;border-bottom:10px solid transparent;border-left:0;border-right:10px solid #000}
</style>

<!-- 회원가입결과 시작 { -->
    <div id="reg_result" class="register">
        <p class="reg_result_p">
            <i class="fa fa-gift" aria-hidden="true"></i><br>
            <strong><?php echo get_text($mb['mb_name']); ?></strong>님의 회원가입을 진심으로 축하합니다.
        </p>
    
        <?php if (is_use_email_certify()) {  ?>
        <p class="result_txt">
            회원 가입 시 입력하신 이메일 주소로 인증메일이 발송되었습니다.<br>
            발송된 인증메일을 확인하신 후 인증처리를 하시면 사이트를 원활하게 이용하실 수 있습니다.
        </p>
        <div id="result_email">
            <span>아이디</span>
            <strong><?php echo $mb['mb_id'] ?></strong><br>
            <span>이메일 주소</span>
            <strong><?php echo $mb['mb_email'] ?></strong>
        </div>
        <p>
            이메일 주소를 잘못 입력하셨다면, 사이트 관리자에게 문의해주시기 바랍니다.
        </p>
        <?php }  ?>
    
        <p class="result_txt">
            회원님의 비밀번호는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.<br>
            아이디, 비밀번호 분실시에는 회원가입시 입력하신 이메일 주소를 이용하여 찾을 수 있습니다.
        </p>
    
        <p class="result_txt">
            회원 탈퇴는 언제든지 가능하며 일정기간이 지난 후, 회원님의 정보는 삭제하고 있습니다.<br>
            감사합니다.
        </p>
    </div>
    <!-- } 회원가입결과 끝 -->
    <div class="btn_confirm_reg">
        <a href="<?php echo G5_URL ?>/" class="reg_btn_submit">메인으로</a>
</div>

<?php
include_once('./_tail.php');
?>