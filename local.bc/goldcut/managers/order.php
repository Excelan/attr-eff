<?php

class Order extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
	
	function place($o)
	{
		$m = new Message();
		$m->action = 'update';
		$m->urn = $o->cart;
		$m->converted = true;
		$m->deliver();
		
		$mt = new Message();
		$mt->urn = $m->urn;
		$mt->action = 'calculate';
		$mt->nosave = true;
		$carttotal = $mt->deliver();

        $cartURN = $m->urn;

		$m = new Message();
		$m->action = 'create';
		$m->urn = 'urn:clientorder';
        $m->payed = false;
		$m->user = $o->user;
		$m->mixeddata = (string) $carttotal->contents;
        $m->cart = $cartURN;
		$m->price = $carttotal->total;
		$m->discount = $carttotal->discounted;

        if ($o->user)
        {
            $userURN = new URN($o->user);
            $user = $userURN->resolve();
            $oo = $user;

            // TODO check for bonus ballance
            if ($o->paywithbonus)
            {
                $u = new Message();
                $u->action = 'update';
                $u->urn = $o->user;
                $u->bonus = array('decrement' => (int) $carttotal->total );
                $u->deliver();
                // make order payed
                $m->payed = true;
            }

        }
        else
        {
            $oo = $o;
        }
        foreach (array('name','phone','street','house','room') as $fname)
            $m->$fname = $oo->$fname;

		// $m->invoice = $invoice->urn;
		$order = $m->deliver();
		return $order;
	}
}

?>