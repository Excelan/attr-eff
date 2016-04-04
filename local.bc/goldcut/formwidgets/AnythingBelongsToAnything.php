<?php

//namespace formwidgets;

class AnythingBelongsToAnything extends FormWidget
{
	protected function build()
	{
		$ff = $this->subject->name;
		$alias = $this->subject->getAlias();
		//println($ff,1,TERM_VIOLET);
		//println($alias,2,TERM_VIOLET);
		if ($this->usedas) {
			$k = $this->usedas;
			$sel = $this->eo->$k;
		}
		else
			$sel = $this->eo->$alias;
		//println($sel,3,TERM_VIOLET);
		if (count($sel))
		{
			$selected_urns = $sel->asURNs();
		}

		$ENTITY = Entity::ref($ff);
		$m = new Message();
		$m->urn = 'urn:'.$ENTITY->name;
		$m->action = 'load';
		$m->last = 300; // !!!
		$m->page = 1;

		if ($ENTITY->defaultorder)
		{
			$m->order = $ENTITY->defaultorder;
		}
		$data = $m->deliver();
		if ($data->total > count($data)) $data = $sel;

		$this->setData($data);

		// usedas
		if ($this->usedas != $ff)
		{
			$ff = $this->usedas;
			$usedTitle = $this->object->astitles[$ff];
			if (!$usedTitle) $usedTitle = $ENTITY->title['ru'];
		}


		// usedas
		if ($this->usedas)
			$MO = array($this->subject, $this->usedas, $usedTitle);
		else
			$MO = $this->subject;

		if (count($data))
			$this->html .= Form::category_selectbox($MO, $this->data, $selected_urns, true, false);
		else
		{
			$this->html .= "SKIP $ff";
		}
	}

	protected function buildx()
	{
		$ff = $this->subject->name;
		$alias = $this->subject->getAlias();
		$ObjEntityMeta = $this->object;

		$ENTITY = Entity::ref($ff);

		$sel = $this->eo->$alias;
		if (count($sel))
		{
			$selected_urns = $sel->asURNs();
			if (count($selected_urns) == 1) $selTitle = $sel->title;
		}

		$m = new Message();
		$m->urn = 'urn:'.$ENTITY->name;
		$m->action = 'load';
		$m->last = 300; // !!!
		$m->page = 1;
		if ($ENTITY->defaultorder) $m->order = $ENTITY->defaultorder;
		// if ($ENTITY->has_field('_parent')) $m->_parent = 'NULL';
		$data = $m->deliver();
		if ($data->total > count($data)) $data = $sel;
		// $data->treesort();

		//$this->setData($this->eo->$ff);
		$this->setData($data);

		if (in_array($ff, $ObjEntityMeta->extendstructure) && count($selected_urns)) {
			$this->html = "<br><p>Смена рубрики <b>{$selTitle}</b> не допускается для расширенной модели данных</p>";
			return;
		}

		if (count($data))
			$this->html .= Form::category_selectbox($this->subject, $this->data, $selected_urns, true, false);
		else
		{
			$this->html .= "SKIP $ff";
		}

		//$this->html .= $sel->current();

	}
	
}

?>