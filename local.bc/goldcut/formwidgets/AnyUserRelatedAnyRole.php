<?php 
class AnyUserRelatedAnyRole extends FormWidget
{
	protected function build()
	{	
		try
		{
			$ff = $this->subject->name;	
			$this->setData($this->eo->$ff);
			$this->html .= "<script> var rt = [] </script>";
			$countRelated = count($this->data);
			if ($countRelated) 
			{
				$urns = $this->data->asURNs();
				$mm = json_encode($this->data->toArray());
				$this->html .= "<script> rt['$ff'] = $mm </script>";
				//$this->html .= join(', ', $urns);
				//$this->html .= $mm;
			}
			
			// число
			$m = new Message();
			$m->action = "load";
			$m->urn = $this->subject->urn;
			//$m->order = array('title'=>'ASC'); // TODO! ADD IF HAS FIELD
			if ($this->subject->defaultorder)
				$m->order = $ENTITY->defaultorder;						
			$ds = $m->deliver();
			
			$countPotential = count($ds);
			//if (false)
			
			if (!defined('RELATED_WIDGET_MORPH_ON')) echo $this->html .= "<p>RELATED_WIDGET_MORPH_ON не определен в конфигурации</p>";
			
			if ($countPotential < RELATED_WIDGET_MORPH_ON)
			{
				
				$this->html .= Form::category_selectbox($this->subject, $ds, $urns, false, true);
			}
			else
			{
				
				if (!$this->eo)
				{
					$this->html = '<p>Привязка возможно только после сохранения объекта</p>'; // когда у объекта уже есть UUID
					return false;
				}
				
				// какие загружать до поиска? последние использованные, часто используемые
				$listid = '';
				$this->html .= '
				<div class="fc" id="rt_'.$ff.'" data-listid="'.$listid.'">
					<div class="fleft">
						<div>
							<p>Поиск (Всего '.$countPotential.')</p>
							<input type="text" class="myInput" data-entity="'.$ff.'" maxlength="255" /> 
						</div>
						<div class="m2" data-host='.$this->eo->urn.'>
							
						</div>
					</div>
					<div class="fleft m3" data-host='.$this->eo->urn.'>
						
					</div>
				</div>
				';
				
				$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
				$jsfile = BASE_DIR.$jsuri;
				if ($jsfile) 
				{
					$this->html .= "<script src='{$jsuri}'></script>";
					$this->html .= "<script> rtInit('rt_{$ff}', rt['$ff']) </script>";
				}
				
			}	
		}
		catch (Exception $e)
		{
			$this->html = $e;
		}
		
	}
}
	
?>