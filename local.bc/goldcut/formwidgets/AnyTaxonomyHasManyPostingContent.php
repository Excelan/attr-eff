<?php 
class AnyTaxonomyHasManyPostingContent extends FormWidget
{
	protected function build()
	{		
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);
		
		if (count($this->data)) 
		{
			$this->html .= "<p>".count($this->data)." объявлений в этой рубрике</p>"; 
		}
		else
		{
			$this->html = '<p>Нет связанных материалов</p>';
		}
	}
}
	
?>