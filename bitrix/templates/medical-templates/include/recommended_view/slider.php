<?php
$arResult = $arParams['DATA'] ?? [];
if (empty($arResult['ITEMS'])) {
	return;
}
$counterPrefix = 'goods__counter_input_' . preg_replace('/\W+/', '', (string)($arParams['BLOCK_UID'] ?? 'rec')) . '_';
?>
<div class="goods">
    <ul class="goods__slider">
        <?php foreach ($arResult["ITEMS"] as $arItem):
			$price = priceDiscount($arItem['ID']);
			$counterId = $counterPrefix . (int)$arItem['ID'];
			$articls = $arItem['ARTICLS']['VALUE'] ?? $arItem['PROPERTIES']['ARTICLS']['VALUE'] ?? [];
			if (!is_array($articls)) {
				$articls = $articls !== '' && $articls !== null ? [$articls] : [];
			}
			?>
            <li class="goods__item">
                <div class="goods__item_wrapper">

                    <div class="goods__img">
                        <a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><img src="<?=$arItem['PREVIEW_PICTURE']?>" alt="goods"></a>
                    </div>
                    <a href="<?echo $arItem["DETAIL_PAGE_URL"]?>" class="goods__name">
                        <?=trim($arItem['NAME'])?>

                        <? if($arItem['DESCRIPTION']): ?>
                            <br/>
                            <span class="goods__name__desc"><?=$arItem['DESCRIPTION']?></span>
                        <? endif; ?>
                    </a>
                    <div class="goods__info">
                        <div class="goods__prices">

                            <div class="goods__price"><?=$price['DISCOUNT_PRICE']?></div>

                            <div class="goods__counter">
                                <div class="goods__counter_subtract">-</div>
                                <input type="text" class="goods__counter_input" id="<?=htmlspecialcharsbx($counterId)?>" value="1" readonly>
                                <div class="goods__counter_add">+</div>
                            </div>
                            <span data-text="за штуку"><?=($arItem["JS_HIDE"] == "N") ? "за штуку" : "" ?></span>
                        </div>
                        <? if(count($articls) > 1): ?>
                            <a href="javascript:void(0)" class="goods__basket icon-basket" onclick="$('#more_option_<?=(int)$arItem['ID']?>').bPopup({zIndex:1000});"></a>
                        <?else:?>
                            <input type="hidden" name="article" value="<?=htmlspecialcharsbx($articls[0] ?? '')?>">
                            <a href="javascript:void(0)" class="goods__basket icon-basket" onclick="addToBasket2(<?=(int)$arItem['ID']?>, $('#<?=htmlspecialcharsbx($counterId)?>').val(),this);"></a>
                        <?endif;?>
                    </div>
                </div>
            </li>
        <?endforeach;?>
    </ul>
</div>
