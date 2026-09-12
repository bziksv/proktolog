<?php
$arResult = $arParams['DATA'] ?? [];
if (empty($arResult['ITEMS'])) {
	return;
}
$counterPrefix = 'goods__counter_input_' . preg_replace('/\W+/', '', (string)($arParams['BLOCK_UID'] ?? 'rec')) . '_';
?>
<div class="goods__list goods__list-2">
    <?php foreach ($arResult['ITEMS'] as $item):
		$price = priceDiscount($item['ID']);
		$counterId = $counterPrefix . (int)$item['ID'];
		$articls = $item['PROPERTIES']['ARTICLS']['VALUE'] ?? $item['ARTICLS']['VALUE'] ?? [];
		if (!is_array($articls)) {
			$articls = $articls !== '' && $articls !== null ? [$articls] : [];
		}
		?>
        <div class="goods__item goods__item_list">
            <?if($item["PRICES"]["BASE"]["DISCOUNT_DIFF_PERCENT"]):?>
                <div class="goods__alert">-<?=$item["PRICES"]["BASE"]["DISCOUNT_DIFF_PERCENT"]?>%</div>
            <?endif;?>
            <div class="goods__img">
                <a href="<?=$item['DETAIL_PAGE_URL'];?>"><img src="<?=$item['PREVIEW_PICTURE']?>" alt="<?=$item['NAME']?>"></a>
            </div>
            <div class="goods__content">
                <a href="<?=$item['DETAIL_PAGE_URL'];?>" class="goods__name"><?=$item['NAME']?></a>

                <? if($item['DESCRIPTION']): ?>
                <div class="goods__text">
                    <?=$item['DESCRIPTION']?>
                </div>
                <? endif; ?>
            </div>

            <div class="goods__main">
                <div class="goods__price"><?=$price['DISCOUNT_PRICE']?></div>
				<span data-text="за штуку"><?=($item["JS_HIDE"] == "N") ? "за штуку" : "" ?></span>
                <div class="goods__counter">
                    <div class="goods__counter_subtract">-</div>
                    <input type="text" class="goods__counter_input" id="<?=htmlspecialcharsbx($counterId)?>" value="1" readonly>
                    <div class="goods__counter_add">+</div>
                </div>
                <? if(count($articls) > 1): ?>
                    <a href="javascript:void(0)" class="goods__buy" onclick="$('#more_option_<?=(int)$item['ID']?>').bPopup({zIndex:1000});" data-text="Купить"><?=($item["JS_HIDE"] == "N") ? "Купить" : "" ?></a>
                <?else:?>
                    <input type="hidden" name="article" value="<?=htmlspecialcharsbx($articls[0] ?? '')?>">
                    <a href="javascript:void(0)" class="goods__buy" onclick="addToBasket2(<?=(int)$item['ID']?>, $('#<?=htmlspecialcharsbx($counterId)?>').val(),this);" data-text="Купить"><?=($item["JS_HIDE"] == "N") ? "Купить" : "" ?></a>
                <?endif;?>

            </div>
        </div>
    <? endforeach; ?>
</div>
