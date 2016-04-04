<?php

function after_order_created_system($order)
{
    // get cart from order
    $orderCartID = $order->cart;
    $cartURN = new URN("urn-cart-{$orderCartID}");
    $cart = $cartURN->resolve()->current();
    /*
     * fix json_decode of mixed data
    printlnd($cart,1,TERM_VIOLET);
    printlnd($order->mixeddata,1,TERM_VIOLET);
    printlnd(json_decode($order->mixeddata, true),1,TERM_VIOLET);
    var_dump(json_last_error())
    */

    // compile order product items list
    $m = new Message();
    $m->action = 'edgesfrom';
    $m->urn = $cart->urn;
    $m->to = 'urn:product';
    $edges = $m->deliver();
    $orderdetails = '';
    foreach ($edges as $edge)
    {
        $amount = $edge->metadata['amount'];
        $urn = new URN($edge->nodeTo);
        $product = $urn->resolve()->current();
        //$amountonpricetotal = (float) $product->price * $amount;
        //{$amountonpricetotal}<sub>грн</sub> &mdash;
        $orderdetails .= "<p style='font-size: 150%; line-height: 150%; border-bottom: 1px solid #ccc; margin: 10px 0;'>{$product->title} <font color=green>{$product->price}</font><sub>грн</sub> x <span style='color: red'>{$amount}</span></p>";
    }
    $ordertotal = "<p style='font-size: 200%; border-top: 1px dashed #eee; padding-top: 3px; margin-top: 7px'><b>В сумме: {$cart->price}<sub>грн</sub></b></p>";
    $orderidtxt = "<p style='border-top: 1px dashed #eee; font-size: 125%; color: #777; padding-top: 3px; margin-top: 7px'><b>Ваш номер заказа: №{$order->id}</b></p>";

    // context for mail template with order list & total
    $orderdata = new stdClass();
    $orderdata->listing = $orderdetails;
    $orderdata->total = $ordertotal;
    $orderdata->id = $order->id;
    $orderdata->name = $order->name;
    $context = array('orderdata' => $orderdata);

    // non template html for admin, user
    $html = $orderdetails;
    $html .= $ordertotal;
    $html .= $orderidtxt;

    // registered user order
    if ($userID = $order->user)
    {
        $userURN = new URN("urn-user-{$userID}");
        $user = $userURN->resolve()->current();
        println($user,1,TERM_GREEN);

        // exists mail template?
        $m = new Message();
        $m->action = 'load';
        $m->urn = 'urn:mailtemplate';
        $m->uri = 'onneworder';
        $hasmailt = $m->deliver();
        if (!count($hasmailt)) // send plain email
        {
            $to = $user->email;
            $nameto = $user->name ? $user->name : $user->email;
            $from = 'webmaster@'.HOST;
            $namefrom = SITE_NAME;
            $subject = "Покупка на сайте ".SITE_NAME;
            try
            {
                dprintln("$from, $namefrom, $to, $nameto, $subject, $html");
                Mail::send($from, $namefrom, $to, $nameto, $subject, $html);
            }
            catch (Exception $e) {
                dprintln($e,1,TERM_RED);
                Log::error($e,'ordermail');
            }
        }
        else // send mail with template
        {
            Mail::sendUserTemplatesContext($user, 'mailview', 'onneworder', $context);
            dprintln("onneworder sent",1,TERM_VIOLET);
        }
    }

    /**
     * SEND EMAILORDERSTO
     */
    $aemails = explode(' ',EMAILORDERSTO);
    foreach ($aemails as $aemail)
    {
        $ci = '';
        foreach (array('name'=>'Имя','phone'=>'Телефон','street'=>'Улица','house'=>'Дом','room'=>'Квартира','text'=>'Инфо') as $k => $v)
        {
            $ci[] = "<tr><td>$v</td><td>{$order->$k}</td></tr>";
        }
        $clientinfo = "<table>".join("\n",$ci)."</table>";
        $html .= $clientinfo;
        $admin = new stdClass();
        $admin->email = $aemail;
        $admin->name = 'ADMIN';
        $from = 'webmaster@'.HOST;
        $namefrom = SITE_NAME;
        $subject = 'Новый заказ';
        Mail::send($from, $namefrom, $admin->email, $admin->name, $subject, $html);
    }


}

if (defined('USELAGACYEMAILORDERSEND') && USELAGACYEMAILORDERSEND === true)
{
    $broker = Broker::instance();
    $broker->queue_declare ("ENITYCONSUMER", DURABLE, NO_ACK);
    $broker->bind("ENTITY", "ENITYCONSUMER", "after.create.clientorder");
    $broker->bind_rpc ("ENITYCONSUMER", "after_order_created_system");
}
?>