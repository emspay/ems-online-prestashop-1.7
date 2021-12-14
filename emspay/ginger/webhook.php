<?php

use Lib\components\GingerBankConfig;

include ('vendor/autoload.php');

include dirname(__FILE__).'/../../config/config.inc.php';

global $kernel;

if(!$kernel)
{
    require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
    $kernel = new \AppKernel('prod', false);
    $kernel->boot();
}

$input = json_decode(file_get_contents("php://input"), true);
$ginger_order_id = $input['order_id'];
echo("WEBHOOK: Starting for ginger_order_id: ".htmlentities($ginger_order_id) . "\n");

if (!in_array($input['event'], array("status_changed")))
{
    die("Only work to do if the status changed");
}

$row = Db::getInstance()->getRow(
    sprintf(
        'SELECT * FROM `%s` WHERE `%s` = \'%s\'',
        _DB_PREFIX_. GingerBankConfig::BANK_PREFIX.'',
        'ginger_order_id',
        pSQL($ginger_order_id)
    )
);

if (!$row)
{
    die("WEBHOOK: Error - No row found for ginger_order_id: ".htmlentities($ginger_order_id));
}

echo "WEBHOOK: Payment method: " . $row['payment_method'] . "\n";

include dirname(__FILE__).'/../'.$row['payment_method'].'/'.$row['payment_method'].'.php';

$gingerPaymentMethod = new $row['payment_method']();

$order_details = $gingerPaymentMethod->gingerClient()->getOrder($ginger_order_id);

if ($order_details)
{

    echo "WEBHOOK: Found status: " . $order_details['status'] . "\n";

    if ($row['id_order'])
    {
        echo "WEBHOOK: id_order was not empty but: " . $row['id_order'] . "\n";

        if (!Context::getContext()->link)
        {
            Context::getContext()->link = new link();// work around a prestashop bug so email is sent
        }

        $order = new Order((int) $row['id_order']);

        switch ($order_details['status']) {
            case 'new':
            case 'processing':
                $order_status = (int) Configuration::get('PS_OS_PREPARATION');
                break;

            case 'completed':
                $order_status = (int) Configuration::get('PS_OS_PAYMENT');
                break;

            case 'error':
                $order_status = (int) Configuration::get('PS_OS_ERROR');
                break;

            case 'cancelled':
            case 'expired':
                $order_status = (int) Configuration::get('PS_OS_CANCELED');
                break;
        }

        echo "WEBHOOK: updating status, old status was: " . $order->current_state . "\n";

        $new_history = new OrderHistory();
        $new_history->id_order = (int) $order->id;
        $new_history->changeIdOrderState($order_status, $order, true);
        $new_history->addWithemail(true);

    }  else {
        echo "WEBHOOK: id_order is empty\n";

        // check if the cart id already is an order
        if ($id_order = intval(Order::getOrderByCartId((int) ($row['id_cart'])))) {
            echo "WEBHOOK: cart was already promoted to order\n";
        } else {
            echo "WEBHOOK: promote cart to order\n";

            $gingerPaymentMethod->validateOrder(
                $row['id_cart'], Configuration::get('PS_OS_PAYMENT'),
                $order_details['amount'] / 100,
                GingerBankConfig::GINGER_BANK_LABELS[current($order_details['transactions'])['payment_method']],
                null,
                array("transaction_id" => current($order_details['transactions'])['id']),
                null,
                false,
                $row['key']
            );
            $id_order = $gingerPaymentMethod->currentOrder;
        }
        echo "WEBHOOK: update database; set id_order to: ".$id_order."\n";

        Db::getInstance()->update(GingerBankConfig::BANK_PREFIX, array("id_order" => $id_order),
            '`ginger_order_id` = "'.Db::getInstance()->escape($ginger_order_id).'"');

        $order_details['merchant_order_id'] = (string)$id_order;
        $gingerPaymentMethod->ginger()->updateOrder($ginger_order_id, $order_details);
    }
}
