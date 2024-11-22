<?php
if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowNote("Модуль установлен успешно.");

?>
<form action="<?php echo $APPLICATION->GetCurPage(); ?>" method="post">
    <?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?php echo LANGUAGE_ID; ?>">
    <input type="hidden" name="id" value="vendor.calendar">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <input type="submit" name="inst" value="Продолжить">
</form>
