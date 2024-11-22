<?php
global $APPLICATION;

if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowMessage([
    "MESSAGE" => "Вы действительно хотите удалить модуль?",
    "TYPE" => "WARNING",
]);
?>
<form action="<?php echo $APPLICATION->GetCurPage(); ?>" method="post">
    <?php echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?php echo LANGUAGE_ID; ?>">
    <input type="hidden" name="id" value="vendor.calendar">
    <input type="hidden" name="uninstall" value="Y">

    <p>
        <input type="checkbox" name="delete_data" value="Y" id="delete_data">
        <label for="delete_data">Удалить все данные, созданные модулем (таблицы, настройки)?</label>
    </p>

    <input type="submit" name="inst" value="Удалить">
</form>
