<?php 
class AnyTaxonomyHasManyAnyContent extends FormWidget
{
	protected function build()
	{	
		$ff = $this->subject->name;
		$this->setData($this->eo->$ff);
		
		if (count($this->data)) 
		{
			foreach($this->data as $n)
			{
				$npath = $n->entity->getPath();
				$this->html .= "<p><a href='/goldcut/admin/?urn={$n->urn}&action=edit&lang=ru'>{$n->title}</a></p>"; 
			}
		}
		else
		{
			$this->html = '<p>Нет связанных материалов</p>';
		}
	}
}
	
?>