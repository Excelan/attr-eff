<?php

/**
Cart cant be empty
Cart created on first put
total - 
1 cart inited > total, contents urns[]
2 no cart > 
*/
class Cart extends EManager
{

	protected function config()
	{
		$this->behaviors[] = 'general_crud';
		$this->behaviors[] = 'general_graph';
		$this->behaviors[] = 'general_list';
	}

	private function init_session()
	{
		$cart_session = Session::get("cart_session");
		if (!$cart_session)
		{
			$cart_session = rand(20000000, 30000000);
			Session::put("cart_session", $cart_session);
		}
		return $cart_session;
	}

	function put($m)
	{
		$amount = 0;
		$action = "link";
		if ($m->urn->is_general())
		{
			// create cart
			$c = new Message('{"action": "create", "urn": "urn-cart"}');
			$c->session = ($m->session) ? $m->session : mt_rand(1000000,32000000);
			$cart = $c->deliver();
			$m->urn = $cart->urn; // replace general cart urn with concrete in query message
		}
		if ($m->urn->is_concrete())
		{
			if (!$cart)
			{
				// load cart
				$t = new Message('{"action": "load"}');
				$t->urn = $m->urn;
				$t->session = $m->session;
				$cart = $t->deliver();
				if (!count($cart)) return new Message('{"urn": "urn-cart", "state": "lost cart"}');
                if ($cart->converted)
                {
                    Session::pop("cart");
                    throw new Exception("Cart converted");
                }
                if ($cart->closed) {
                    Session::pop("cart");
                    throw new Exception("Cart closed");
                }
			}
			
			// println('concrete '.$m->urn,1,TERM_GREEN);
			// println($m,2,TERM_GREEN);
			$amountInc = ($m->amount) ? (int) $m->amount : 1;
			$u = new Message();
			$u->action = 'hasedge';
			$u->urn = $m->urn;
			$u->with = $m->product;
			$et = $u->deliver();
			if (!$et->exists)
			{
				$u = new Message();
				$u->action = 'newedge';
				$u->urn = $m->urn;
				$u->with = $m->product;
				$u->metadata = array("amount"=>$amountInc);
				// println($u,2,TERM_VIOLET);
				$e = $u->deliver();
			}
			else
			{
				$u = new Message();
				$u->action = 'edgedata';
				$u->urn = $m->urn;
				$u->with = $m->product;
				$u->type = 0;
				$u->metadata = array('amount'=>array('increment'=>$amountInc));
				// println($u,2,TERM_GRAY);
				$e = $u->deliver();
			}
		}
		else
			throw new Exception("{$m->urn} is not cart urn");
		
		$mt = new Message();
		$mt->urn = $m->urn;
		$mt->action = 'calculate';
		//$mt->session = $carthash;
		$carttotal = $mt->deliver();
		
		/**
		order mininum
		delivery free from
		*/
		
		return $carttotal;
	}
	
	function increment($m)
	{
		$m->__direction = 'increment';
		return $this->changeamount($m);
	}
	
	function decrement($m)
	{
		$m->__direction = 'decrement';
		return $this->changeamount($m);
	}
	
	function changeamount($m)
	{
		if ($m->urn->is_concrete())
		{
			$amountInc = ($m->amount) ? (int) $m->amount : 1;
			$u = new Message();
			$u->action = 'hasedge';
			$u->urn = $m->urn;
			$u->with = $m->product;
			$et = $u->deliver();
			if (!$et->exists)
			{
				throw new Exception("No edge to {$m->product} from {$m->urn}");
			}
			else
			{
                $u = new Message();
                $u->action = 'loadedge';
                $u->urn = $m->urn;
                $u->with = $m->product;
                $u->type = 0;
                $edge = $u->deliver();
                Log::debug($edge,'cart');
                /*
                 * TODO if SET amount = 0?
                 */
                if ($m->__direction == 'decrement' && $edge->metadata['amount'] == 1)
                {
                    $u = new Message();
                    $u->action = 'unedge';
                    $u->urn = $m->urn;
                    $u->with = $m->product;
                    $u->type = 0;
                    $u->deliver();
                }
                else
                {
                    $u = new Message();
                    $u->action = 'edgedata';
                    $u->urn = $m->urn;
                    $u->with = $m->product;
                    $u->type = 0;
                    if ($m->__direction)
                    {
                        $u->metadata = array('amount' => array($m->__direction => $amountInc));
                    }
                    else
                        $u->metadata = array('amount' => $amountInc);
                    $u->deliver();
                    Log::debug($u,'cart');
                }
			}
		}
		else
			throw new Exception("{$m->urn} is not concrete cart urn");
		
		$mt = new Message();
		$mt->urn = $m->urn;
		$mt->action = 'calculate';
		$carttotal = $mt->deliver();
		
		return $carttotal;
	}

	/**
	discounts
	shipping
	tax
	bonuses gen
	*/
	function calculate($m)
	{
		if ($m->session || $m->urn->is_concrete()) // LOAD CART BY UUID OR SESSION ID
		{
			// load cart
			$t = new Message('{"action": "load"}');
			$t->urn = $m->urn;
			$t->session = $m->session;
			$cart = $t->deliver();
			if (!count($cart)) return new Message('{"urn": "urn-cart", "state": "lost cart"}');

			// calc total on contents
			$total = 0;
			$discounted = 0;
			$urns = array();
			
			$m = new Message();
			$m->action = 'edgesfrom';
			$m->urn = $cart->urn;
			$m->to = 'urn:product';
			$edges = $m->deliver();
			$uuids = array();
			$uuid_amount = array();
			foreach ($edges as $edge)
			{
				$urn = new URN($edge->nodeTo);
				$uuid = $urn->uuid;
				array_push($uuids, $uuid);
				$amount = $edge->metadata['amount'];
				$uuid_amount[$uuid] = $amount;
			}
            if (count($uuids))
            {
                $m = new Message();
                $m->urn = 'urn:product';
                $m->action = 'load';
                $m->id = $uuids;
                $products = $m->deliver();
                foreach ($products as $p)
                {
                    $amount = $uuid_amount[$p->id];
                    $urns[] = array("urn"=>(string) $p->urn, "amount" => $amount, 'title' => $p->title, 'price' => $p->price);
                    $total += $p->price * $amount;
                    if ($p->discount > 0)
                    {
                        $discounted += ceil($p->price - ($p->price / 100 * $p->discount)) * $amount;
                    }
                    else
                    {
                        $discounted += $p->price * $pamount;
                    }
                    $discounted = $discounted;
                }
            }
			
			// update cart total in db
			if (!$m->nosave)
			{
				$mt = new Message();
				$mt->action = 'update';
				$mt->urn = $cart->urn;
				$mt->price = $total;
				$mt->deliver();
			}
			
			$t = new Message(array("urn" => (string) $cart->urn, "total" => $total, "contents" => $urns));
			$t->discounted = $discounted;
			return $t;
		}	
	}

}

?>