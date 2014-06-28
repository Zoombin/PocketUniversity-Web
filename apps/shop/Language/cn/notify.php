<?php

return array(
    'shop_win' => array(
        'title' => '恭喜您一元梦想获奖，请尽快填写收货地址，以便我们为您配送！超过7天，此商品将被默认为您已经放弃',
        'body'  => '订单号:'.$order_id.' 商品:'.$product,
        'other' => '<a href="' . U('shop/Myshop/ygOrderDetail', array('id' => $order_id)) . '" target="_blank">提交收货地址</a>',
    ),
    'shop_tgend' => array(
        'title' => '恭喜您众志成城开团，请尽快付清尾款，填写收货地址，以便我们为您配送！超过7天，此商品将被默认为您已经放弃',
        'body'  => '订单号:'.$order_id.' 商品:'.$product,
        'other' => '<a href="' . U('shop/Myshop/tgOrderDetail', array('id' => $order_id)) . '" target="_blank">付清尾款</a>',
    )
);
?>