<?php

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\Extend;

return [
    (new Extend\Routes('api'))
        ->post('/money/webhook/alchemy', 'money.webhook.alchemy', 'ShebaoTing\\MoneyErc20Deposit\\Api\\Controller\\HandleAlchemyWebhookController'),

    (new Extend\Settings)
        ->serializeToApi('money_erc20_chain', 'money_erc20_chain')
        ->serializeToApi('money_erc20_wallet', 'money_erc20_wallet')
        ->serializeToApi('money_erc20_rate', 'money_erc20_rate', 'intval')
        ->serializeToApi('money_erc20_min', 'money_erc20_min', 'floatval'),

    (new Extend\Event)
        ->listen(Serializing::class, function (Serializing $event) {
            if ($event->isSerializer(CurrentUserSerializer::class)) {
                $event->attributes['money_deposit_id'] = $event->model->money_deposit_id ?? null;
            }
        })
];
