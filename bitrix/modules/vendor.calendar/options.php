<?php
use Bitrix\Main\Config\Option;

$module_id = 'vendor.calendar';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    Option::set($module_id, 'some_option', $_POST['some_option']);
}

$someOption = Option::get($module_id, 'some_option', '');
?>
<form method="post">
    <?= bitrix_sessid_post(); ?>
    <label>Пример настройки:</label>
    <input type="text" name="some_option" value="<?= htmlspecialchars($someOption); ?>">
    <input type="submit" value="Сохранить">
</form>