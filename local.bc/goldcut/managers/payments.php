<?php

class Payments extends EManager
{

	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
	
	function pay($i)
	{

		$invoice = $i->urn->resolve()->current();
		$user = $invoice->user;

		if ($i->from == 'bonus')
		{
			$fromaccount = 'bonus';
			$m = $this->trypaywith($user, $fromaccount, $invoice);
		}
		else if ($i->from == 'bonusorwallet')
		{
			$fromaccount = 'bonus';
			$m = $this->trypaywith($user, $fromaccount, $invoice);
			if ($m->error)
			{
				$fromaccount = 'wallet';
				$m = $this->trypaywith($user, $fromaccount, $invoice);
			}
			return $m;
		}
		else if ($i->from == 'walletorbonus')
		{
			$fromaccount = 'wallet';
			$m = $this->trypaywith($user, $fromaccount, $invoice);
			if ($m->error)
			{
				$fromaccount = 'bonus';
				$m = $this->trypaywith($user, $fromaccount, $invoice);
			}
			return $m;
		}
		else
		{
			$fromaccount = 'wallet';
			$m = $this->trypaywith($user, $fromaccount, $invoice);
		}

		// deliver goos or service
		// TODO MQ Payed callback
		$invoice = $invoice->urn->resolve();
		//dprintln("payed.{$invoice->mqname}",1,TERM_YELLOW);
		if ($invoice->mqname)
			Broker::instance()->send($invoice, "MANAGERS", "payed.{$invoice->mqname}");
		else 
			Log::debug("$invoice has no MQNAME for notify that invoice payed",'mqincost');
		
		return $m;
	}

	private function trypaywith($user, $fromaccount, $invoice)
	{
		$wallet = $user->$fromaccount;

		// check ballance
		if ($wallet < $invoice->total) {
			$mr = new Message();
			$mr->error = "not_enought_money_in_wallet";
			$mr->account = $fromaccount;
			$mr->deposit = $wallet;
			return $mr;
		}

		// withdraw amount
		$upw = new Message();
		$upw->urn = $user->urn;
		$upw->action = 'decrement';
		$upw->field = $fromaccount;
		$upw->value = $invoice->total;
		$upw->deliver();

		// set payed status to Yes
		$payit = new Message();
		$payit->urn = $invoice->urn;
		$payit->action = 'update';
		$payit->payed = true;
		$payit->payed_at = TimeOp::now();
		$payit->deliver();

		// return payed status
		$m = new Message();
		$m->info = 'payed';
		$m->from = $fromaccount;
		$m->total = $invoice->total;
		if ($invoice->uri) {
			$m->uri = $invoice->uri;
		}

		return $m;
	}

}

?>